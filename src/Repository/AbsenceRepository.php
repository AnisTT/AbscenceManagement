<?php

namespace App\Repository;

use App\Entity\Absence;
use App\Entity\Classe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Absence>
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    public function save(Absence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Absence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Compte le nombre total d'absences
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les absences justifiées
     */
    public function countJustifiees(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.justifiee = :justifiee')
            ->setParameter('justifiee', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les absences non justifiées
     */
    public function countNonJustifiees(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.justifiee = :justifiee')
            ->setParameter('justifiee', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les dernières absences
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'e', 'c', 'm')
            ->join('a.etudiant', 'e')
            ->join('e.classe', 'c')
            ->join('a.matiere', 'm')
            ->orderBy('a.dateAbsence', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les absences par classe pour le graphique
     */
    public function countByClasse(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('c.nom as classe, COUNT(a.id) as total')
            ->join('a.etudiant', 'e')
            ->join('e.classe', 'c')
            ->groupBy('c.id')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Absences du mois en cours
     */
    public function countThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month');
        $startOfMonth->setTime(0, 0, 0);

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.dateAbsence >= :start')
            ->setParameter('start', $startOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Absences de la semaine en cours
     */
    public function countThisWeek(): int
    {
        $startOfWeek = new \DateTime('monday this week');
        $startOfWeek->setTime(0, 0, 0);

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.dateAbsence >= :start')
            ->setParameter('start', $startOfWeek)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques mensuelles (6 derniers mois)
     */
    public function getMonthlyStats(): array
    {
        $sixMonthsAgo = new \DateTime('-6 months');
        $sixMonthsAgo->setTime(0, 0, 0);

        return $this->createQueryBuilder('a')
            ->select("SUBSTRING(a.dateAbsence, 1, 7) as mois, COUNT(a.id) as total")
            ->where('a.dateAbsence >= :start')
            ->setParameter('start', $sixMonthsAgo)
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
