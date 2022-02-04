<?php

namespace App\Repository;

use App\Entity\RirIpNetwork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RirIpNetwork|null find($id, $lockMode = null, $lockVersion = null)
 * @method RirIpNetwork|null findOneBy(array $criteria, array $orderBy = null)
 * @method RirIpNetwork[]    findAll()
 * @method RirIpNetwork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RirIpNetworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RirIpNetwork::class);
    }

    public function getObjectsByHandles(array $data): array
    {
        $query = $this->createQueryBuilder('rirIpNetwork');
        /** @var RirIpNetwork $rirIpNetwork */
        foreach ($data as $rirIpNetwork)
            $query->orWhere($query->expr()->eq('rirIpNetwork.handle', "'".$rirIpNetwork->getHandle()."'"));
        return $query->getQuery()->getResult();
    }

    public function getObjectsByIps(array $data)
    {
        $query = $this->createQueryBuilder('rirIpNetwork');
        /** @var RirIpNetwork $rirIpNetwork */
        foreach ($data as $rirIpNetwork)
        {
            $query
                ->orWhere($query->expr()->orX(
                    $query->expr()->eq('rirIpNetwork.ipStartDec', "'".$rirIpNetwork->getIpStartDec()."'"),
                    $query->expr()->eq('rirIpNetwork.ipEndDec', "'".$rirIpNetwork->getIpEndDec()."'")
                ))
            ;
        }
//        $query->orderBy('rirIpNetwork.handle', 'ASC');
        return $query->getQuery()->getResult();
    }

    public function truncate()
    {
        $tableName = $this->getEntityManager()->getClassMetadata('App\Entity\RirIpNetwork')->getTableName();
        $this->getEntityManager()->getConnection()->executeStatement('START TRANSACTION;SET FOREIGN_KEY_CHECKS=0; TRUNCATE '.$tableName.'; SET FOREIGN_KEY_CHECKS=1; COMMIT;');
    }

    // /**
    //  * @return RirIpNetwork[] Returns an array of RirIpNetwork objects
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
    public function findOneBySomeField($value): ?RirIpNetwork
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
