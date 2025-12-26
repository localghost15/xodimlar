<?php

namespace App\Controller;

use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/departments')]
class DepartmentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'api_departments_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Allow read access generally or restrict? Plan said HR/CEO.
        // Assuming HR/CEO for management, but listing might be needed for dropdowns (which are public/authenticated).
        // The list endpoint for dropdowns was in UserController. This is the management list.
        $this->denyAccessUnlessGranted('ROLE_HR');

        $departments = $this->entityManager->getRepository(Department::class)->findAll();
        $data = [];

        foreach ($departments as $d) {
            $data[] = [
                'id' => $d->getId(),
                'name' => $d->getName(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/create', name: 'api_departments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HR');

        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if (!$name) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $dept = new Department();
        $dept->setName($name);

        $this->entityManager->persist($dept);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'id' => $dept->getId()], 201);
    }

    #[Route('/{id}', name: 'api_departments_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HR');

        $dept = $this->entityManager->getRepository(Department::class)->find($id);
        if (!$dept) {
            return $this->json(['error' => 'Department not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if ($name) {
            $dept->setName($name);
            $this->entityManager->flush();
        }

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_departments_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HR');

        $dept = $this->entityManager->getRepository(Department::class)->find($id);
        if (!$dept) {
            return $this->json(['error' => 'Department not found'], 404);
        }

        // Check for users using this department?
        // Doctrine might throw constraint violation if foreign keys exist.
        // For simple prototype, we just try to remove.
        try {
            $this->entityManager->remove($dept);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete department (likely in use)'], 400);
        }

        return $this->json(['success' => true]);
    }
}
