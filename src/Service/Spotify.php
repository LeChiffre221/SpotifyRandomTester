<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 13/10/2018
 * Time: 23:08
 */

namespace App\Service;


use App\Entity\Artist;
use App\Entity\HistoTrack;
use App\Entity\Playlist;
use App\Entity\Track;
use App\Entity\User;
use App\Manager\ArtistManager;
use App\Manager\HistoTrackManager;
use App\Manager\PlaylistManager;
use App\Manager\TrackManager;
use App\Manager\UserManager;
use SpotifyWebAPI\Session as SpotifySession;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Spotify extends BaseSpotify
{

    const CLIENT_ID = '8a9a13e1558241d9bb8a3115b8ab89bd';
    const SECRET_ID = 'a629d310d41343368eb0304865043bc4';
    const REDIRECT_URL = 'http://localhost/spotifyRandomTester/public/spotify/callback';

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var HistoTrackManager
     */
    private $histoTrackManager;

    /**
     * @var PlaylistManager
     */
    private $playlistManager;

    /**
     * @var TrackManager
     */
    private $trackManager;

    /**
     * @var ArtistManager
     */
    private $artistManager;

    /**
     * Spotify constructor.
     * @param ParameterBagInterface $param
     * @param UserManager $userManager
     * @param HistoTrackManager $histoTrackManager
     * @param PlaylistManager $playlistManager
     * @param TrackManager $trackManager
     * @param ArtistManager $artistManager
     */
    public function __construct(
        ParameterBagInterface $param,
        UserManager $userManager,
        HistoTrackManager $histoTrackManager,
        PlaylistManager $playlistManager,
        TrackManager $trackManager,
        ArtistManager $artistManager
    )
    {

        parent::__construct($param);
        $this->userManager = $userManager;
        $this->histoTrackManager = $histoTrackManager;
        $this->playlistManager = $playlistManager;
        $this->trackManager = $trackManager;
        $this->artistManager = $artistManager;
    }

    public function connect()
    {


    }

    public function registerHistoTrack()
    {
        $users = $this->userManager->findAll();
        $spotifySession = $this->getSpotifySession();

        foreach ($users as $user) {
            $api = $this->buildApi($spotifySession, $user);
            $playBackInfo = $api->getMyCurrentPlaybackInfo();

            if (!$playBackInfo) {
                continue;
            }
            if (!$playBackInfo->context) {
                continue;
            }
            if ($playBackInfo->context->type != 'playlist') {
                continue;
            }
            if (!$playBackInfo->shuffle_state) {
                continue;
            }

            $playlist = $this->registerPlaylistByPlayback($api, $playBackInfo);

            $track = $this->registerTrackByPlayback($playBackInfo);

            if (!is_null($user->getLastTrackListened())) {
                if ($user->getLastTrackListened()->getUri() === $track->getUri()) {
                    continue;
                }
            }

            $histoTrack = new HistoTrack();

            $histoTrack->setUser($user);
            $histoTrack->setTrack($track);
            $histoTrack->setPlaylist($playlist);
            $histoTrack->setDate(new \DateTime());

            $user->setLastTrackListened($track);
            $this->userManager->persist($user);
            $this->histoTrackManager->persist($histoTrack);
            $this->histoTrackManager->flush();
        }

    }

    public function registerPlaylistByPlayback(SpotifyWebAPI $api, $playback)
    {
        $playlistUri = $playback->context->uri;
        $playlist = $this->playlistManager->findOneBy([
            'uri' => $playlistUri
        ]);

        if (is_null($playlist)) {
            $playlistId = explode(':', $playback->context->uri)[4];
            $playlistData = $api->getPlaylist($playlistId);

            $playlist = new Playlist();
            $playlist->setUri($playlistUri);
            $playlist->setName($playlistData->name);

            $this->playlistManager->persist($playlist);

        }

        return $playlist;
    }

    public function registerTrackByPlayback($playback)
    {
        $trackUri = $playback->item->uri;

        $track = $this->trackManager->findOneBy([
            'uri' => $trackUri
        ]);

        if (is_null($track)) {

            $track = new Track();
            $track->setUri($playback->item->uri);
            $track->setName($playback->item->name);

            $track->setImage($playback->item->album->images[1]->url);
            $track->setDurationMs($playback->item->duration_ms);

            foreach ($playback->item->artists as $artistPlayback) {
                $artist = $this->registerArtistByArtistPlayback($artistPlayback);
                $track->addArtist($artist);
            }

            $track->formatAndHydrateArtistStringField();

            $this->trackManager->persist($track);

        }

        return $track;
    }

    /**
     * @param $artistPlayback
     * @return Artist
     */
    public function registerArtistByArtistPlayback($artistPlayback)
    {

        $artistUri = $artistPlayback->uri;

        $artist = $this->artistManager->findOneBy([
            'uri' => $artistUri
        ]);

        if (is_null($artist)) {
            $artist = new Artist();
            $artist->setUri($artistUri);
            $artist->setName($artistPlayback->name);

            $this->artistManager->persist($artist);
        }

        return $artist;
    }

    /**
     * @param SpotifySession $spotifySession
     * @param User $user
     * @return SpotifyWebAPI
     */
    public function buildApi(SpotifySession $spotifySession, User $user)
    {
        $api = new SpotifyWebAPI();
        $api->setAccessToken($user->getAccessToken());

        try {
            $api->me();
        } catch (SpotifyWebAPIException $exception) {
            $spotifySession->refreshAccessToken($user->getRefreshToken());
            $accessToken = $spotifySession->getAccessToken();

            // Set our new access token on the API wrapper and continue to use the API as usual
            $api->setAccessToken($accessToken);
            $user = $this->mapUserCredential($user, $spotifySession);

            $this->createSpotifyUser($user, $api->me());
        }

        return $api;
    }


    /**
     * @param User $user
     * @param $userInfo
     */
    public function createSpotifyUser(User $user, $userInfo)
    {
        $userFound = $this->userManager->findBy([
            'username' => $userInfo->id
        ]);

        if (!$userFound || is_null($userInfo)) {
            $user->setName($userInfo->display_name);
            $user->setUsername($userInfo->id);
            $user->setPhoto(isset($userInfo->images[0]->url) ? $userInfo->images[0]->url : null);

            $this->userManager->persist($user);
            $this->userManager->flush();
        }
    }

    /**
     * @param User $user
     * @param $session
     * @return User
     */
    public function mapUserCredential(User $user, SpotifySession $session)
    {
        $user->setAccessToken($session->getAccessToken());
        $user->setRefreshToken($session->getRefreshToken());

        return $user;
    }

    /**
     * @param User $user
     * @param $mode
     * @return bool
     */
    public function repeatMode(User $user, $mode)
    {
        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApiForUser($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->repeat(array(
            'device_id' => $playBackInfo->device->id,
            'state' => $mode
        ));

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function play(User $user)
    {
        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApiForUser($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->play($playBackInfo->device->id);

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function next(User $user)
    {
        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApiForUser($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->next($playBackInfo->device->id);

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function previous(User $user)
    {
        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApiForUser($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->previous($playBackInfo->device->id);

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function pause(User $user)
    {
        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApiForUser($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->pause($playBackInfo->device->id);

        return true;
    }

    /**
     * @param User $user
     * @param $mode
     * @return bool
     */
    public function shuffleMode(User $user, $mode)
    {
        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApiForUser($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->shuffle(array(
            'device_id' => $playBackInfo->device->id,
            'state' => $mode
        ));

        return true;
    }


}