<?php

namespace App\Repository;

use App\Entity\AbsenceRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbsenceRequest>
 *
 * @method AbsenceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbsenceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbsenceRequest[]    findAll()
 * @method AbsenceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbsenceRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbsenceRequest::class);
    }

    public function save(AbsenceRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AbsenceRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByFilter(array $filters): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->leftJoin('u.departmentRel', 'd')
            ->addSelect('u', 'd')
            ->orderBy('a.date', 'DESC');

        if (!empty($filters['date_from'])) {
            $qb->andWhere('a.date >= :date_from')
                ->setParameter('date_from', new \DateTime($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $qb->andWhere('a.date <= :date_to')
                ->setParameter('date_to', new \DateTime($filters['date_to']));
        }

        if (!empty($filters['department_id'])) {
            $qb->andWhere('u.departmentRel = :dept_id')
                ->setParameter('dept_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('u.fullName LIKE :search OR u.phone LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
