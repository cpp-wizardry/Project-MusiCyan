<?php

namespace App\Repository;

use App\Entity\Artist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artist>
 */
class ArtistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artist::class);
    }

    /**
     * Recherche les artistes par nom (partiel).
     *
     * @param string $name Nom pour la recherche
     *
     * @return array
     */
    public function searchByName(string $name): array
    {
        return $this->createQueryBuilder('a')
            ->where('LOWER(a.name) LIKE LOWER(:name)')
            ->setParameter('name', $name . '%')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
