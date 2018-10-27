<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 24/10/2018
 * Time: 18:25
 */

namespace App\Mapper\User;


use App\Entity\User;

class SpotifyInformation
{
    public function map(User $user, $spotifyUserInfo)
    {
        $user->setName($spotifyUserInfo->display_name);
        $user->setUsername($spotifyUserInfo->id);
        $user->setPhoto(isset($spotifyUserInfo->images[0]->url) ? $spotifyUserInfo->images[0]->url : null);
        return $user;
    }
}