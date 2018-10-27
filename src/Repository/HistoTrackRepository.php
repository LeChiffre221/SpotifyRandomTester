<?php

namespace App\Repository;

use App\Entity\HistoTrack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HistoTrack|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoTrack|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoTrack[]    findAll()
 * @method HistoTrack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoTrackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HistoTrack::class);
    }

//    /**
//     * @return HistoTrack[] Returns an array of HistoTrack objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HistoTrack
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
