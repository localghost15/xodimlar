<?php

namespace App\Controller;

use App\Entity\PurchaseRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/purchase')]
class PurchaseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/create', name: 'api_purchase_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;
        $category = $data['category'] ?? null;
        $price = $data['price'] ?? null;
        $description = $data['description'] ?? '';
        $link = $data['link'] ?? '';

        if (!$title || !$category || !$price) {
            return $this->json(['success' => false, 'errors' => ['Title, Category and Price are required']], 422);
        }

        $purchase = new PurchaseRequest();
        $purchase->setUser($user);
        $purchase->setTitle($title);
        $purchase->setCategory($category);
        $purchase->setPrice($price);
        $purchase->setDescription($description);
        $purchase->setLink($link);
        $purchase->setStatus('new'); // Initial status

        $this->entityManager->persist($purchase);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $purchase->getId(),
            'message' => 'Purchase request created'
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}/approve', name: 'api_purchase_approve', methods: ['POST'])]
    public function approve(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $purchase = $this->entityManager->getRepository(PurchaseRequest::class)->find($id);
        if (!$purchase) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $currentStatus = $purchase->getStatus();
        $roles = $user->getRoles();
        $newStatus = null;

        // Workflow Logic
        // 1. Dept Head approves 'new' -> 'head_approved'
        if ($currentStatus === 'new') {
            if (in_array('ROLE_DEPT_HEAD', $roles) || in_array('ROLE_CEO', $roles)) {
                $newStatus = 'head_approved';
                $purchase->setHeadApprovedAt(new \DateTime());
            } else {
                return $this->json(['error' => 'Access denied. Waiting for Dept Head.'], 403);
            }
        }
        // 2. Accountant confirms budget 'head_approved' -> 'budget_confirmed'
        elseif ($currentStatus === 'head_approved') {
            if (in_array('ROLE_ACCOUNTANT', $roles) || in_array('ROLE_CEO', $roles)) {
                $newStatus = 'budget_confirmed';
                $purchase->setBudgetConfirmedAt(new \DateTime());
            } else {
                return $this->json(['error' => 'Access denied. Waiting for Accountant.'], 403);
            }
        }
        // 3. CEO Final Approval 'budget_confirmed' -> 'ceo_approved'
        elseif ($currentStatus === 'budget_confirmed') {
            if (in_array('ROLE_CEO', $roles)) {
                $newStatus = 'ceo_approved';
                $purchase->setCeoApprovedAt(new \DateTime());
            } else {
                return $this->json(['error' => 'Access denied. Waiting for CEO.'], 403);
            }
        } else {
            return $this->json(['error' => 'Cannot approve in current status: ' . $currentStatus], 400);
        }

        if ($newStatus) {
            $purchase->setStatus($newStatus);
            $this->entityManager->flush();
            return $this->json(['success' => true, 'new_status' => $newStatus]);
        }

        return $this->json(['error' => 'Unknown error'], 500);
    }

    #[Route('/mass-approve', name: 'api_purchase_mass_approve', methods: ['POST'])]
    public function massApprove(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User || !in_array('ROLE_CEO', $user->getRoles())) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return $this->json(['error' => 'No IDs provided'], 400);
        }

        $repo = $this->entityManager->getRepository(PurchaseRequest::class);
        $count = 0;

        foreach ($ids as $id) {
            $purchase = $repo->find($id);
            if ($purchase && $purchase->getStatus() === 'budget_confirmed') {
                $purchase->setStatus('ceo_approved');
                $purchase->setCeoApprovedAt(new \DateTime());
                $count++;
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'approved_count' => $count,
            'message' => "Approved $count requests"
        ]);
    }
}

