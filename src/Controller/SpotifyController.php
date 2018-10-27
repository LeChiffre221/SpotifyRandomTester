<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 22/09/2018
 * Time: 17:51
 */

namespace App\Controller;


use App\Entity\User;
use App\Manager\UserManager;
use App\Mapper\User\SpotifyCredential;
use App\Mapper\User\SpotifyInformation;
use App\Model\Artist;
use App\Model\Context;
use App\Model\PlayBack;
use App\Model\Track;
use App\Service\Spotify;
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


class SpotifyController extends Controller
{

    private $twig;

    private $doctrine;

    private $spotifyService;


    /**
     * SpotifyController constructor.
     * @param Environment $twig
     * @param RegistryInterface $doctrine
     * @param Spotify $spotifyService
     */
    public function __construct(
        Environment $twig,
        RegistryInterface $doctrine,
        Spotify $spotifyService
    )
    {
        $this->twig = $twig;
        $this->doctrine = $doctrine;
        $this->spotifyService = $spotifyService;

    }

    public function index()
    {
        return new Response(
            $this->twig->render('spotify/homepage.html.twig')
        );
    }

    public function connectSpotifyAccount()
    {
        $session = $this->spotifyService->getSpotifySession();
        $options = $this->spotifyService->getSpotifyAuthorizationOptions();

        return new RedirectResponse($session->getAuthorizeUrl($options));
    }

    public function callback(SpotifyCredential $spotifyCredentialMapper, SpotifyInformation $spotifyUserInfoMapper, UserManager $userManager)
    {

        $spotifySession = $this->spotifyService->getSpotifySession();

        // Request a access token using the code from Spotify
        $spotifySession->requestAccessToken($_GET['code']);

        $user = new User;
        $user = $spotifyCredentialMapper->map($user, $spotifySession);

        $api = $this->spotifyService->buildApiForUser($spotifySession, $user);
        $user = $spotifyUserInfoMapper->map($user, $api->me());

        $userManager->persistSpotifyUser($user);
        $userManager->flush();

        return new RedirectResponse('spotify-current-playback');
    }

    public function changeTrack($idUser)
    {

        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $spotifySession = $this->spotifyService->getSpotifySession();
        $api = $this->spotifyService->buildApiForUser($spotifySession, $user);

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

        return $this->redirectToRoute('spotify-current-playback');
    }

    public function repeat($idUser, $mode)
    {
        $user = $this->doctrine->getRepository(User::class)->find($idUser);

        $repeatMode = $mode ? 'track' : 'off';
        $this->spotifyService->repeatMode($user, $repeatMode);

        return $this->redirectToRoute('spotify-current-playback');
    }

    public function play($idUser)
    {
        $user = $this->doctrine->getRepository(User::class)->find($idUser);
        $this->spotifyService->play($user);
        return $this->redirectToRoute('spotify-current-playback');
    }

    public function next($idUser)
    {
        $user = $this->doctrine->getRepository(User::class)->find($idUser);
        $this->spotifyService->next($user);
        return $this->redirectToRoute('spotify-current-playback');
    }

    public function previous($idUser)
    {
        $user = $this->doctrine->getRepository(User::class)->find($idUser);
        $this->spotifyService->previous($user);
        return $this->redirectToRoute('spotify-current-playback');
    }

    public function pause($idUser)
    {
        $user = $this->doctrine->getRepository(User::class)->find($idUser);
        $this->spotifyService->pause($user);
        return $this->redirectToRoute('spotify-current-playback');
    }

    public function shuffle($idUser, $mode)
    {
        $user = $this->doctrine->getRepository(User::class)->find($idUser);
        $this->spotifyService->shuffleMode($user, $mode);
        return $this->redirectToRoute('spotify-current-playback');
    }


    public function currentPlayBack()
    {

        $users = $this->doctrine->getRepository(User::class)->findAll();

        $spotifySession = $this->spotifyService->getSpotifySession();


        $playBacks = [];
        foreach ($users as $user) {

            $api = $this->spotifyService->buildApiForUser($spotifySession, $user);
            $playBackInfo = $api->getMyCurrentPlaybackInfo();

/*            highlight_string("<?php\n\$data =\n" . var_export($user->getUsername(), true) . ";\n?>");*/
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
            $this->twig->render('spotify/current-playback.html.twig', array(
                'playbacks' => $playBacks
            ))
        );
    }





}