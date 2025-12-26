<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/users')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HR'); // Basic check, CEO also has this role in hierarchy usually or checking explicitly

        $repo = $this->entityManager->getRepository(User::class);
        $users = $repo->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'full_name' => $user->getFullName(),
                'phone' => $user->getPhone(),
                'role' => $user->getRoles()[0] ?? 'ROLE_EMPLOYEE', // Simplified
                'department' => $user->getDepartment()?->getName() ?? 'N/A',
                'manager' => $user->getParent()?->getFullName() ?? '-',
            ];
        }

        return $this->json($data);
    }

    #[Route('/heads', name: 'api_users_heads', methods: ['GET'])]
    public function heads(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $heads = [];

        foreach ($users as $user) {
            if (in_array('ROLE_DEPT_HEAD', $user->getRoles())) {
                $heads[] = [
                    'id' => $user->getId(),
                    'full_name' => $user->getFullName(),
                    'department' => $user->getDepartment()?->getName()
                ];
            }
        }

        return $this->json($heads);
    }

    #[Route('/departments', name: 'api_departments_list', methods: ['GET'])]
    public function departments(): JsonResponse
    {
        // Ideally fetch from Department repository
        // If Department entity exists properly:
        // $depts = $this->entityManager->getRepository(Department::class)->findAll();
        // For now, let's create a dynamic list if table is empty or just return hardcoded if needed?
        // Let's try to fetch real ones.
        $repo = $this->entityManager->getRepository(Department::class);
        $depts = $repo->findAll();

        $data = [];
        foreach ($depts as $d) {
            $data[] = [
                'id' => $d->getId(),
                'name' => $d->getName()
            ];
        }

        return $this->json($data);
    }

    #[Route('/create', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_HR');

        $data = json_decode($request->getContent(), true);

        $fullName = $data['full_name'] ?? null;
        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? 'password123';
        $role = $data['role'] ?? 'ROLE_EMPLOYEE';
        $deptId = $data['department_id'] ?? null;
        $managerId = $data['manager_id'] ?? null;

        if (!$fullName || !$phone) {
            return $this->json(['error' => 'Full Name and Phone are required'], 400);
        }

        // Check exists
        $exists = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);
        if ($exists) {
            return $this->json(['error' => 'User with this phone already exists'], 409);
        }

        $user = new User();
        $user->setFullName($fullName);
        $user->setPhone($phone);
        $user->setRoles([$role]);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setLang('ru'); // Default

        if ($deptId) {
            $dept = $this->entityManager->getRepository(Department::class)->find($deptId);
            if ($dept)
                $user->setDepartment($dept);
        }

        if ($managerId) {
            $manager = $this->entityManager->getRepository(User::class)->find($managerId);
            if ($manager)
                $user->setParent($manager);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'message' => 'User created'], 201);
    }
}
