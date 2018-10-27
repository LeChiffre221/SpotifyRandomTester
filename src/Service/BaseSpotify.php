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

class BaseSpotify
{

    const CLIENT_ID = '8a9a13e1558241d9bb8a3115b8ab89bd';
    const SECRET_ID = 'a629d310d41343368eb0304865043bc4';
    const REDIRECT_URL = 'http://localhost/spotifyRandomTester/public/spotify/callback';

    private $param;

    /**
     * BaseSpotify constructor.
     * @param ParameterBagInterface $param
     */
    public function __construct(ParameterBagInterface $param)
    {
        $this->param = $param;
    }


    public function getSpotifySession()
    {
        $session = new SpotifySession(
            $this->param->get('spotify.client.id'),
            $this->param->get('spotify.client.secret'),
            $this->param->get('spotify.redirect.url')
        );

        return $session;
    }

    public function getSpotifyAuthorizationOptions(){
        $options = [
            'scope' => [
                'playlist-read-private',
                'user-read-private',
                'user-read-currently-playing',
                'user-read-playback-state',
                'user-modify-playback-state',
            ],
        ];

        return $options;
    }

    /**
     * @param SpotifySession $spotifySession
     * @param User $user
     * @return SpotifyWebAPI
     */
    public function buildApiForUser(SpotifySession $spotifySession, User $user)
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

        }
        $this->userManager->persist($user);
        $this->userManager->flush();
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


}