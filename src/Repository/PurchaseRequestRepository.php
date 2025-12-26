<?php

namespace App\Repository;

use App\Entity\PurchaseRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseRequest>
 *
 * @method PurchaseRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseRequest[]    findAll()
 * @method PurchaseRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseRequest::class);
    }

    public function save(PurchaseRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PurchaseRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
