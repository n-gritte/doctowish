<?php

namespace App\Repository;

use App\Entity\Doctor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Doctor>
 *
 * @method Doctor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Doctor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Doctor[]    findAll()
 * @method Doctor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoctorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doctor::class);
    }

//    /**
//     * @return Doctor[] Returns an array of Doctor objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Doctor
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getSearchDoctor($term){
        $query = $this->createQueryBuilder('d')
            ->join('d.user', 'u')
            // ->andWhere('d.title LIKE :term')
            ->andWhere('
                d.title LIKE :term 
                OR u.firstname LIKE :term
                OR u.lastname LIKE :term
            ')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery()
        ;
        return $query->getResult();
    }

    public function getSearchDoctorCity($term){
        $query = $this->createQueryBuilder('d')
            ->select('d.city')
            ->distinct()
            ->andWhere('d.city LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->getQuery()
        ;
        return $query->getResult();
    }
}
