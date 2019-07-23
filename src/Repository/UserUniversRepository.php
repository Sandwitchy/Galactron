<?php

namespace App\Repository;

use App\Entity\UserUnivers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserUnivers|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserUnivers|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserUnivers[]    findAll()
 * @method UserUnivers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserUniversRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserUnivers::class);
    }

    // /**
    //  * @return UserUnivers[] Returns an array of UserUnivers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserUnivers
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
