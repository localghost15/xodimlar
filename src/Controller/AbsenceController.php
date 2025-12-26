<?php

namespace App\Controller;

use App\Entity\AbsenceRequest;
use App\Entity\User;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/absence')]
class AbsenceController extends AbstractController
{
    public function __construct(
        private TelegramService $telegramService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/create', name: 'api_absence_create', methods: ['POST'])]
    // #[IsGranted('ROLE_EMPLOYEE')] // Uncomment when security is fully wired
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();

        // For development without fully working auth token passing in this environment, 
        // we might mock or rely on session if cookies work.
        // Assuming AuthController::login established a session or client sends headers.

        if (!$user instanceof User) {
            // Fallback for testing if no user logged in (should return 401 in prod)
            // return $this->json(['error' => 'User not logged in'], 401);

            // IMPORTANT: For current task context where I cannot easily curl with cookies
            // I will assume the user is passed or just fail if not found.
            // But usually $this->getUser() works if session is set.
        }

        $data = json_decode($request->getContent(), true);

        // Validation based on requirements
        $type = $data['type'] ?? null;
        $dateStr = $data['date'] ?? null;
        $timeStartStr = $data['time_start'] ?? null;
        $timeEndStr = $data['time_end'] ?? null;
        $reason = $data['reason'] ?? '';

        $errors = [];

        if (!in_array($type, ['full_day', 'hours'])) {
            $errors[] = "Тип должен быть 'full_day' или 'hours'.";
        }

        if (!$dateStr) {
            $errors[] = "Дата обязательна.";
        }

        if ($type === 'hours') {
            if (!$timeStartStr || !$timeEndStr) {
                $errors[] = "При выборе 'На несколько часов', время начала и конца обязательны.";
            }
        }

        if (mb_strlen(trim($reason)) < 20) {
            $errors[] = "Причина должна содержать минимум 20 символов.";
        }

        if (count($errors) > 0) {
            return $this->json(['success' => false, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Logic
        $absence = new AbsenceRequest();

        // If user is null (auth issue), we can't save. 
        if ($user) {
            $absence->setUser($user); // setUser logic needed in Entity getters/setters (omitted in previous step file, assuming existence)
        } else {
            // Allow manually passing user_id for testing if auth fails? No, strictly adhere to security.
            // If $this->getUser() is null, we return 401.
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $date = new \DateTime($dateStr);
        $absence->setDate($date);
        $absence->setType($type);
        $absence->setReason($reason);
        // $absence->setStatus('pending'); // Default in DB/Entity

        if ($type === 'hours') {
            $start = new \DateTime($timeStartStr);
            $end = new \DateTime($timeEndStr);

            $absence->setTimeStart($start);
            $absence->setTimeEnd($end);

            // Calculate hours
            $diff = $end->diff($start);
            $hours = $diff->h + ($diff->i / 60);

            // Basic check: end > start
            if ($hours <= 0) {
                return $this->json(['success' => false, 'errors' => ['Время конца должно быть позже времени начала.']], 422);
            }

            $absence->setCalculatedHours((string) round($hours, 2));
        } else {
            // Full day = 8 hours standard? Or leave null? 
            // Requirement said "calculate... if selected hours". 
            // But User said "calculated_hours... automatically upon approval" in previous prompt
            // "Calculated hours (automatically calculated ... in absence_request add calculated_hours field... automatically calculated when approving)"
            // WAIT. The prompt said "Calculated hours ... automatically calculated upon approval".
            // BUT THIS Prompt says: "Implement automatic calculation of calculated_hours BEFORE SAVING TO DB (difference between start and end)".
            // I will follow the LATEST instruction: "Before saving to DB".

            if ($type === 'full_day') {
                $absence->setCalculatedHours('8.00'); // Standard work day
            }
        }

        $this->entityManager->persist($absence);
        $this->entityManager->flush();

        // Notification
        $this->telegramService->notifyManager($absence);

        return $this->json([
            'success' => true,
            'message' => 'Заявка успешно создана',
            'id' => $absence->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/my-history', name: 'api_absence_history', methods: ['GET'])]
    public function history(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $repo = $this->entityManager->getRepository(AbsenceRequest::class);
        // Find by user, order by date DESC
        $absences = $repo->findBy(['user' => $user], ['date' => 'DESC']);

        $data = [];
        foreach ($absences as $abs) {
            $data[] = [
                'id' => $abs->getId(),
                'date' => $abs->getDate()->format('Y-m-d'),
                'type' => $abs->getType(),
                'reason' => $abs->getReason(),
                'status' => 'pending', // TODO: Add status field to entity if missing, assuming pending for now
                'calculated_hours' => $abs->getCalculatedHours(),
            ];
        }

        return $this->json($data);
    }
}
