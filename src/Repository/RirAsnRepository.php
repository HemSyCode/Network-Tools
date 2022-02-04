<?php

namespace App\Repository;

use App\Entity\RirAsn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RirAsn|null find($id, $lockMode = null, $lockVersion = null)
 * @method RirAsn|null findOneBy(array $criteria, array $orderBy = null)
 * @method RirAsn[]    findAll()
 * @method RirAsn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RirAsnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RirAsn::class);
    }

    public function getObjectsByHandles(array $data): array
    {
        $query = $this->createQueryBuilder('rirAsn');
        /** @var RirAsn $rirAsn */
        foreach ($data as $rirAsn)
            $query->orWhere($query->expr()->eq('rirAsn.handle', "'".$rirAsn->getHandle()."'"));
        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return RirAsn[] Returns an array of RirAsn objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RirAsn
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
