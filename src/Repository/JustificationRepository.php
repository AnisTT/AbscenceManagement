<?php

namespace App\Repository;

use App\Entity\Justification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Justification>
 */
class JustificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Justification::class);
    }

    public function save(Justification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Justification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Compte les justifications en attente
     */
    public function countEnAttente(): int
    {
        return $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->where('j.statut = :statut')
            ->setParameter('statut', Justification::STATUT_EN_ATTENTE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les justifications en attente
     */
    public function findEnAttente(): array
    {
        return $this->createQueryBuilder('j')
            ->select('j', 'a', 'e')
            ->join('j.absence', 'a')
            ->join('a.etudiant', 'e')
            ->where('j.statut = :statut')
            ->setParameter('statut', Justification::STATUT_EN_ATTENTE)
            ->orderBy('j.dateSoumission', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les dernières justifications traitées
     */
    public function findLatestProcessed(int $limit = 5): array
    {
        return $this->createQueryBuilder('j')
            ->select('j', 'a', 'e')
            ->join('j.absence', 'a')
            ->join('a.etudiant', 'e')
            ->where('j.statut != :statut')
            ->setParameter('statut', Justification::STATUT_EN_ATTENTE)
            ->orderBy('j.dateValidation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
