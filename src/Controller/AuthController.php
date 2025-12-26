<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        \Symfony\Bundle\SecurityBundle\Security $security
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? null;

        if (!$phone || !$password) {
            return $this->json([
                'error' => 'Missing credentials'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Cleanup phone if needed (e.g. remove + or spaces)
        // Simple sanitization matching fixture format 998...
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);

        // DEBUG LOGGING
        error_log("Login attempt: Phone=$phone (Clean=$cleanPhone)");

        $user = $userRepository->findOneBy(['phone' => $cleanPhone]);

        if (!$user) {
            error_log("User not found for phone: $cleanPhone");
            return $this->json([
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        error_log("User found: " . $user->getId());

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            error_log("Password invalid for user: " . $user->getId());
            return $this->json([
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // In a real app, generate JWT here.
        // For this task, we return User data + Role.

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'full_name' => $user->getFullName(),
                'phone' => $user->getPhone(),
                'lang' => $user->getLang(),
                'roles' => $user->getRoles(),
                // Simplification: Return the "highest" or "primary" role for frontend logic
                'main_role' => in_array('ROLE_CEO', $user->getRoles()) ? 'ROLE_CEO' :
                    (in_array('ROLE_HR', $user->getRoles()) ? 'ROLE_HR' :
                        (in_array('ROLE_DEPT_HEAD', $user->getRoles()) ? 'ROLE_DEPT_HEAD' : 'ROLE_EMPLOYEE'))
            ],
            'message' => 'Login successful'
        ]);
    }
}
