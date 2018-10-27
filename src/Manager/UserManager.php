<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 13/10/2018
 * Time: 23:16
 */

namespace App\Manager;

use App\Entity\User;
use App\Mapper\User\Data;
use SpotifyWebAPI\Session;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserManager extends BaseManager
{

    const CLASS_NAME = User::class;

    public $dataMapper;

    /**
     * UserManager constructor.
     * @param RegistryInterface $doctrine
     * @param Data $dataMapper
     */
    public function __construct(
        RegistryInterface $doctrine,
        Data $dataMapper
    )
    {
        parent::__construct($doctrine, self::CLASS_NAME);
        $this->dataMapper = $dataMapper;
    }

    public function persistSpotifyUser(User $user)
    {

        $oldUser = $this->getRepository()->findBy([
            'username' => $user->getUsername()
        ]);

        if ($oldUser) {
            $this->dataMapper->map($oldUser, $user);
            $this->persist($oldUser);
        }
        $this->persist($user);
    }


}