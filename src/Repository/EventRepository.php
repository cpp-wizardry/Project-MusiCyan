<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findByUserEmail(string $email): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.users', 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
    }
    public function findByDate(\DateTime $date): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date >= :date')
            ->setParameter('date', $date)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findByArtist(int $id): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.artist = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

}
