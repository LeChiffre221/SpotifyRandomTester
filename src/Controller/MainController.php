<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 22/09/2018
 * Time: 17:51
 */

namespace App\Controller;


use App\Entity\User;
use App\Model\Artist;
use App\Model\Context;
use App\Model\PlayBack;
use App\Model\Track;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIAuthException;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;


class MainController extends Controller
{

    private $twig;

    private $doctrine;


    /**
     * MainController constructor.
     */
    public function __construct(Environment $twig, RegistryInterface $doctrine)
    {
        $this->twig = $twig;
        $this->doctrine = $doctrine;

    }

    public function index()
    {
        return new Response(
            $this->twig->render('homepage.html.twig')
        );
    }

    public function connect()
    {
        $session = $this->getSpotifySession();

        $options = [
            'scope' => [
                'playlist-read-private',
                'user-read-private',
                'user-read-currently-playing',
                'user-read-playback-state',
                'user-modify-playback-state',
            ],
        ];


        return new RedirectResponse($session->getAuthorizeUrl($options));
    }

    public function callback()
    {

        $session = $this->getSpotifySession();

        // Request a access token using the code from Spotify
        $session->requestAccessToken($_GET['code']);

        $user = new User();
        $user = $this->mapUserCredential($user, $session);

        $api = $this->buildApi($session, $user);
        $this->createUser($user, $api->me());

        return new RedirectResponse('current-playback');
    }

    public function createUser(User $user, $userInfo)
    {

        $entityManager = $this->doctrine->getEntityManager();
        $userFound = $this->doctrine->getRepository(User::class)->findByUsername($userInfo->id);

        if (!$userFound || is_null($userInfo)) {
            $user->setName($userInfo->display_name);
            $user->setUsername($userInfo->id);
            $user->setPhoto(isset($userInfo->images[0]->url) ? $userInfo->images[0]->url : null);


        }

        $entityManager->persist($user);
        $entityManager->flush();

    }

    public function mapUserCredential(User $user, $session)
    {

        $entityManager = $this->doctrine->getEntityManager();

        $user->setAccessToken($session->getAccessToken());
        $user->setRefreshToken($session->getRefreshToken());

        return $user;
    }

    public function changeTrack($idUser)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();


        /*        highlight_string("<?php\n\$data =\n" . var_export($playBackInfo, true) . ";\n?>");*/

        //EL DIABLO BE CAREFUL !!!
//        $api->play($playBackInfo->device->id, array(
//            'uris' => ['spotify:track:3dlzyvxVSAoNFg4Wby3quj'],
//            'position_ms' => 75500
//        ));

        $api->play($playBackInfo->device->id, array(
            'uris' => ['spotify:track:4dTsjMFSmVbcRMaLoPNabP'],
            'position_ms' => 0
        ));

        return $this->redirectToRoute('current-playback');
    }

    public function repeat($idUser, $mode)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $state = $mode ? 'track' : 'off';
        $api->repeat(array(
            'device_id' => $playBackInfo->device->id,
            'state' => $state
        ));
        return $this->redirectToRoute('current-playback');
    }

    public function play($idUser)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->play($playBackInfo->device->id);

        return $this->redirectToRoute('current-playback');
    }

    public function next($idUser)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->next($playBackInfo->device->id);

        return $this->redirectToRoute('current-playback');
    }

    public function previous($idUser)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->previous($playBackInfo->device->id);

        return $this->redirectToRoute('current-playback');
    }

    public function pause($idUser)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->pause($playBackInfo->device->id);

        return $this->redirectToRoute('current-playback');
    }

    public function shuffle($idUser, $mode)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $state = $mode ? 'track' : 'off';
        $api->shuffle(array(
            'device_id' => $playBackInfo->device->id,
            'state' => $mode
        ));
        return $this->redirectToRoute('current-playback');
    }

    public function volume($idUser){
        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->getSpotifySession();
        $api = $this->buildApi($spotifySession, $user);

        $playBackInfo = $api->getMyCurrentPlaybackInfo();

        $api->changeVolume(array(
            'volume_percent' => 0
        ));
        return $this->redirectToRoute('current-playback');
//        return new Response($user->getId());
    }

    public function currentPlayBack()
    {

        $users = $this->doctrine->getRepository(User::class)->findAll();

        $spotifySession = $this->getSpotifySession();


        $playBacks = [];
        foreach ($users as $user) {


            $api = $this->buildApi($spotifySession, $user);
            $playBackInfo = $api->getMyCurrentPlaybackInfo();

            highlight_string("<?php\n\$data =\n" . var_export($user->getUsername(), true) . ";\n?>");
            if ($playBackInfo) {


                $playBack = new PlayBack();

                $playBack->setUserName($user->getName());
                $playBack->setUserId($user->getId());
                $playBack->setUserPhoto($user->getPhoto());

                $playBack->setShuffle($playBackInfo->shuffle_state);
                $playBack->setRepeat($playBackInfo->repeat_state);
                $playBack->setPlaying($playBackInfo->is_playing);
                $playBack->setPositionMs($playBackInfo->progress_ms);

                if ($playBackInfo->context) {
                    $context = new Context();
                    $context->setType($playBackInfo->context->type);
                    if ($playBackInfo->context->type == 'playlist') {
                        $playlistId = explode(':', $playBackInfo->context->uri)[4];
                        $playlist = $api->getPlaylist($playlistId);

                        $context->setId($playlist->id);
                        $context->setName($playlist->name);
                    }

                    $playBack->setContext($context);
                }

                $track = new Track();
                $track->setName($playBackInfo->item->name);
                $track->setUri($playBackInfo->item->uri);
                $track->setImage($playBackInfo->item->album->images[1]->url);
                $track->setDurationMs($playBackInfo->item->duration_ms);


                foreach ($playBackInfo->item->artists as $artistData) {
                    $artist = new Artist();
                    $artist->setName($artistData->name);
                    $track->addArtist($artist);
                }

                $playBack->setTrack($track);

                $playBacks[] = $playBack;

            }


        }

        return new Response(
            $this->twig->render('current-playback.html.twig', array(
                'playbacks' => $playBacks
            ))
        );
    }

    public function getSpotifySession()
    {
        $session = new Session(
            '8a9a13e1558241d9bb8a3115b8ab89bd',
            'a629d310d41343368eb0304865043bc4',
            'http://localhost/spotifyRandomTester/public/callback'
        );

        return $session;
    }

    public function buildApi($spotifySession, User $user)
    {
        try {
            $api = new SpotifyWebAPI();
            // Fetch the saved access token from somewhere. A database for example.
            $api->setAccessToken($user->getAccessToken());
            $api->me();


        } catch (SpotifyWebAPIException $exception) {

            $api = new SpotifyWebAPI();

            $spotifySession->refreshAccessToken($user->getRefreshToken());
            $accessToken = $spotifySession->getAccessToken();

            // Set our new access token on the API wrapper and continue to use the API as usual
            $api->setAccessToken($accessToken);
            $user = $this->mapUserCredential($user, $spotifySession);

            $this->createUser($user, $api->me());

        }

        return $api;
    }

}