<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 24/10/2018
 * Time: 18:25
 */

namespace App\Mapper\User;


use App\Entity\User;

class Data
{
    public function map(User $oldUser, User $newUser)
    {
        $oldUser->setRefreshToken($newUser->getRefreshToken());
        $oldUser->setAccessToken($newUser->getAccessToken());
        $oldUser->setName($newUser->getName());
        $oldUser->setUsername($newUser->getUsername());
        $oldUser->setPhoto($newUser->getPhoto());
        
        return $oldUser;
    }
}