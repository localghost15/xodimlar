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
        private EntityManagerInterface $entityManager,
        private \App\Service\AssetService $assetService
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
        $type = $data['type'] ?? 'asset';
        $price = $data['price'] ?? null;
        $description = $data['description'] ?? '';
        $link = $data['link'] ?? '';

        if (!$title || !$category || !$price) {
            return $this->json(['success' => false, 'errors' => ['Title, Category and Price are required']], 422);
        }

        $purchase = new PurchaseRequest();
        $purchase->setUser($user);
        $purchase->setTitle($title);
        $purchase->setTitle($title);
        $purchase->setCategory($category);
        $purchase->setType($type);
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

                if ($purchase->getType() === 'asset') {
                    // Create Asset automatically
                    $inv = 'INV-' . date('Y') . '-' . mt_rand(1000, 9999);
                    $sn = 'SN-' . strtoupper(substr(md5(uniqid()), 0, 8));
                    $this->assetService->createAssetFromPurchase($purchase, $user, $inv, $sn);
                    // status is set to 'asset_created' inside service
                    $newStatus = 'asset_created';
                } else {
                    $newStatus = 'ceo_approved';
                    $purchase->setCeoApprovedAt(new \DateTime());
                }

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

                if ($purchase->getType() === 'asset') {
                    $inv = 'INV-' . date('Y') . '-' . mt_rand(1000, 9999);
                    $sn = 'SN-' . strtoupper(substr(md5(uniqid()), 0, 8));
                    $this->assetService->createAssetFromPurchase($purchase, $user, $inv, $sn);
                } else {
                    $purchase->setStatus('ceo_approved');
                    $purchase->setCeoApprovedAt(new \DateTime());
                }

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
    #[Route('/my-history', name: 'api_purchase_history', methods: ['GET'])]
    public function history(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $repo = $this->entityManager->getRepository(PurchaseRequest::class);
        $requests = $repo->findBy(['user' => $user], ['id' => 'DESC']);

        $data = [];
        foreach ($requests as $req) {
            $data[] = [
                'id' => $req->getId(),
                'title' => $req->getTitle(),
                'category' => $req->getCategory(),
                'type' => $req->getType(),
                'price' => $req->getPrice(),
                'status' => $req->getStatus(),
                'created_at' => 'Today', // TODO: Add createdAt field to entity
            ];
        }

        return $this->json($data);
    }

    #[Route('/pending-approvals', name: 'api_purchase_pending', methods: ['GET'])]
    public function pendingApprovals(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $roles = $user->getRoles();
        $statusCriteria = null;

        if (in_array('ROLE_DEPT_HEAD', $roles)) {
            $statusCriteria = 'new';
        } elseif (in_array('ROLE_ACCOUNTANT', $roles)) {
            $statusCriteria = 'head_approved';
        } elseif (in_array('ROLE_CEO', $roles)) {
            // CEO usually sees 'budget_confirmed' but via dashboard stats normally. 
            // Also can see 'new' or 'head_approved' if he wants to skip steps? 
            // For now let's focus on Head/Accountant list.
            // If CEO calls this, maybe show everything? 
            // Let's stick to showing him budget_confirmed items here too as a fallback list
            $statusCriteria = 'budget_confirmed';
        } else {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $repo = $this->entityManager->getRepository(PurchaseRequest::class);
        $requests = $repo->findBy(['status' => $statusCriteria], ['id' => 'ASC']);

        $data = [];
        foreach ($requests as $req) {
            $data[] = [
                'id' => $req->getId(),
                'title' => $req->getTitle(),
                'user_name' => $req->getUser()->getFullName(),
                'price' => $req->getPrice(),
                'category' => $req->getCategory(),
                'status' => $req->getStatus(),
                'description' => $req->getDescription(),
                'type' => $req->getType(),
            ];
        }

        return $this->json($data);
    }
}

