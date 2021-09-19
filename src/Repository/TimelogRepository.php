<?php

namespace App\Repository;

use App\Entity\Timelog;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Timelog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timelog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timelog[]    findAll()
 * @method Timelog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimelogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timelog::class);
    }

    // /**
    //  * @return Timelog[] Returns an array of Timelog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Timelog
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function entriesForToday()
    {
        return $this->createQueryBuilder('t')
            ->where('DATE(t.start) = :val')
            ->setParameter('val', new \DateTime('00:00'))
            ->getQuery()
            ->getResult();
    }

    /*
     * if($queryParams['project']) {$criteria[] = $queryParams['project'];}
        if($queryParams['datePreselect']) { $criteria[] = $queryParams['datePreselect'];}
        if($queryParams['dateGiven']) { $criteria[] = $queryParams['dateGiven']; }
        if($queryParams['hours']) { $criteria[] = $queryParams['hours']; }
     */
    /*
     *         $hours = $dateGiven = $datePreselect = $project = null;
            $criteria = [];

            if($queryParams['project']) {$criteria['project'] = $queryParams['project'];}
            if($queryParams['datePreselect']) { $criteria['datePreselect'] = $queryParams['datePreselect'];}
            if($queryParams['dateGiven']) { $criteria['dateGiven'] = $queryParams['dateGiven']; }
            if($queryParams['hours']) { $criteria[] = $queryParams['hours']; }
     */
    public function findByCriteria(array $criteria)
    {
        $qb = $this->createQueryBuilder('t');

        if ($criteria['project']) {
            $project = $this->getEntityManager()->find(Project::class, (int)$criteria['project']);

            $qb->andWhere('t.project = :project')
                ->setParameter('project', $project);
        }

        if ($criteria['datePreselect']) {
            switch ($criteria['datePreselect']) {
                case 'today':
                    $today = new \DateTime('0:0');
                    $qb->andWhere('DATE(t.start) = :today')
                        ->setParameter('yesterday', $today);
                    break;
                case 'yesterday':
                    $yesterday = new \DateTime('-1 days 0:0');
                    $qb->andWhere('DATE(t.start) = :yesterday')
                        ->setParameter('yesterday', $yesterday);
                    break;
            }
        }

        if ($criteria['dateGiven']) {
            try {
                $dateGiven = new \DateTime($criteria['dateGiven']);
            } catch (\Exception $e) {
                $dateGiven = new \DateTime();
            }
            $qb->andWhere('DATE(t.start) = :start')
                ->setParameter('start', $dateGiven);
        }

        if (isset($criteria['hours'])) {
            //@todo
        }

        $qb->orderBy('t.start');

        return $qb->getQuery()
            ->getResult();
    }

    public function findByRange($startRange, $endRange, $project = null)
    {
        //$startRange, $endRange
//        switch ($calc) {
//            case 'today':
//                $startRange = new \DateTime('0:0');
//                $endRange = new \DateTime('23:59:59');
//        }

        $qb = $this->createQueryBuilder('t');

        if ($project !== null) {
            $qb->andWhere('t.project = :project')
                ->setParameter('project', $project);
        }

        $qb->andWhere('t.start BETWEEN :start AND :end')
            ->setParameter('start', $startRange)
            ->setParameter('end', $endRange);

        $qb->orderBy('t.start');

        return $qb->getQuery()
            ->getResult();
    }
}
