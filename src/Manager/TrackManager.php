<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 13/10/2018
 * Time: 23:16
 */

namespace App\Manager;

use App\Entity\Track;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TrackManager extends BaseManager
{

    const CLASS_NAME = Track::class;

    /**
     * TrackManager constructor.
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        parent::__construct($doctrine, self::CLASS_NAME);
    }

}