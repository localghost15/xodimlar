<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Admin/CEO
        $ceo = new User();
        $ceo->setPhone('998901234567');
        $ceo->setFullName('Director CEO');
        $ceo->setRoles(['ROLE_CEO']);
        $ceo->setPassword(
            $this->userPasswordHasher->hashPassword(
                $ceo,
                'password123'
            )
        );
        $ceo->setLang('ru');
        $manager->persist($ceo);

        // 2. HR Manager
        $hr = new User();
        $hr->setPhone('998907654321');
        $hr->setFullName('HR Manager');
        $hr->setRoles(['ROLE_HR']);
        $hr->setPassword(
            $this->userPasswordHasher->hashPassword(
                $hr,
                'password123'
            )
        );
        $hr->setLang('ru');
        $manager->persist($hr);

        // 3. Employee
        $emp = new User();
        $emp->setPhone('998901112233');
        $emp->setFullName('John Employee');
        $emp->setRoles(['ROLE_EMPLOYEE']);
        $emp->setPassword(
            $this->userPasswordHasher->hashPassword(
                $emp,
                'password123'
            )
        );
        $emp->setLang('uz');
        $manager->persist($emp);

        $manager->flush();
    }
}
