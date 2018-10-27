<?php
/**
 * Created by PhpStorm.
 * User: mthga
 * Date: 13/10/2018
 * Time: 23:14
 */

namespace App\Manager;


use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

abstract class BaseManager
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $className;

    /**
     * BaseManager constructor.
     * @param RegistryInterface $doctrine
     * @param string $className
     */
    public function __construct(RegistryInterface $doctrine, string $className)
    {
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getEntityManager();
        $this->className = $className;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository(){

        $repository = $this->doctrine->getRepository($this->className);

        return $repository;
    }

    /**
     * @param int $id
     * @return null|object
     */
    public function find(int $id){
        return $this->getRepository()->find($id);
    }

    /**
     * @return array
     */
    public function findAll(){
        return $this->getRepository()->findAll();
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null){
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     * @return null|object
     */
    public function findOneBy(array $criteria){
        return $this->getRepository()->findOneBy($criteria);
    }


    public function persist($entity){
        $this->em->persist($entity);
    }

    public function flush(){
        $this->em->flush();
    }

    public function persistAndFlush($entity){
        $this->persist($entity);
        $this->flush();
    }

    public function remove($entity){
        $entity->deleteEntity();
        $this->persist($entity);
    }

    public function hardRemove($entity){
        $this->remove($entity);
    }

    public function clear(){
        $this->em->clear();
    }

}