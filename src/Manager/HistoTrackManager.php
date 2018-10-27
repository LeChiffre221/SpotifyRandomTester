<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 13/10/2018
 * Time: 23:16
 */

namespace App\Manager;

use App\Entity\Artist;
use App\Entity\HistoTrack;
use Symfony\Bridge\Doctrine\RegistryInterface;

class HistoTrackManager extends BaseManager
{

    const CLASS_NAME = HistoTrack::class;

    /**
     * TrackManager constructor.
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        parent::__construct($doctrine, self::CLASS_NAME);
    }

}