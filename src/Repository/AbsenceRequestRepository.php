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
}
