<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 13/10/2018
 * Time: 23:02
 */

namespace App\Command;

use App\Service\Spotify;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetSpotifyPlaybackForAllUser extends Command
{

    private $spotifyService;

    /**
     * GetSpotifyPlaybackForAllUser constructor.
     * @param Spotify $spotifyService
     */
    public function __construct(Spotify $spotifyService)
    {
        parent::__construct();
        $this->spotifyService = $spotifyService;

    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:get-spotify-playback-for-all-user')

            // the short description shown while running "php bin/console list"
            ->setDescription('Get current spotify playback for all user registed in app')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to get current spotify playback for all user and register in db...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Get user spotify playback',
            '============',
            '',
        ]);

        $this->spotifyService->registerHistoTrack();

    }


}