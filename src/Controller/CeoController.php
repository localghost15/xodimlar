<?php

namespace App\Controller;

use App\Entity\AbsenceRequest;
use App\Entity\PurchaseRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/ceo')]
class CeoController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/stats', name: 'api_ceo_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        // 1. Who is not in office TODAY (AbsenceRequest approved + date match)
        // Ignoring 'hours' type complexity for simple "Who is missing" list, or include them with times.
        $today = new \DateTime('today');

        $absences = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(AbsenceRequest::class, 'a')
            ->where('a.date = :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        $absentData = [];
        foreach ($absences as $abs) {
            $absentData[] = [
                'id' => $abs->getId(),
                'user_name' => $abs->getUser()->getFullName(),
                'reason' => $abs->getReason(),
                'type' => $abs->getType(), // full_day or hours
                'time_info' => $abs->getType() === 'hours' ?
                    ($abs->getTimeStart()?->format('H:i') . ' - ' . $abs->getTimeEnd()?->format('H:i')) : 'Весь день'
            ];
        }

        // 2. Purchases waiting for CEO (budget_confirmed)
        $purchases = $this->entityManager->getRepository(PurchaseRequest::class)->findBy([
            'status' => 'budget_confirmed'
        ]);

        $purchaseData = [];
        foreach ($purchases as $p) {
            $purchaseData[] = [
                'id' => $p->getId(),
                'title' => $p->getTitle(),
                'category' => $p->getCategory(),
                'price' => $p->getPrice(),
                'user_name' => $p->getUser()->getFullName(),
                'date' => $p->getBudgetConfirmedAt()?->format('d.m.Y H:i')
            ];
        }

        // 3. Discipline (Top Employees by hours this month)
        // Harder because 'calculatedHours' is a string/decimal.
        // We'll calculate month start.
        $startOfMonth = new \DateTime('first day of this month 00:00:00');

        // Use DQL Sum
        $topAbsences = $this->entityManager->createQueryBuilder()
            ->select('identity(a.user) as user_id', 'SUM(a.calculatedHours) as total_hours')
            ->from(AbsenceRequest::class, 'a')
            ->where('a.date >= :startMonth')
            ->groupBy('a.user')
            ->orderBy('total_hours', 'DESC')
            ->setMaxResults(5)
            ->setParameter('startMonth', $startOfMonth)
            ->getQuery()
            ->getResult();

        $disciplineData = [];
        $userRepo = $this->entityManager->getRepository(User::class);
        foreach ($topAbsences as $row) {
            $user = $userRepo->find($row['user_id']);
            if ($user) {
                $disciplineData[] = [
                    'user_name' => $user->getFullName(),
                    'department' => $user->getDepartment()?->getName() ?? 'N/A',
                    'total_hours' => $row['total_hours']
                ];
            }
        }

        return $this->json([
            'absent_today' => $absentData,
            'stat_date' => $today->format('d.m.Y'),
            'waiting_purchases' => $purchaseData,
            'discipline_top' => $disciplineData
        ]);
    }
}
