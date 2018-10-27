<?php


namespace App\Mapper\User;


use App\Entity\User;
use SpotifyWebAPI\Session;

class SpotifyCredential
{

    public function map(User $user, Session $spotifySession)
    {
        $user->setAccessToken($spotifySession->getAccessToken());
        $user->setRefreshToken($spotifySession->getRefreshToken());
        return $user;
    }


}