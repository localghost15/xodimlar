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
        // 0. Create Departments
        $deptAdmin = new \App\Entity\Department();
        $deptAdmin->setName('Administration');
        $manager->persist($deptAdmin);

        $deptHR = new \App\Entity\Department();
        $deptHR->setName('HR Department');
        $manager->persist($deptHR);

        $deptIT = new \App\Entity\Department();
        $deptIT->setName('IT Department');
        $manager->persist($deptIT);

        $deptFinance = new \App\Entity\Department();
        $deptFinance->setName('Finance Department');
        $manager->persist($deptFinance);

        // 1. Admin/CEO
        $ceo = new User();
        $ceo->setPhone('998901234567');
        $ceo->setFullName('Director CEO');
        $ceo->setRoles(['ROLE_CEO']);
        $ceo->setPassword($this->userPasswordHasher->hashPassword($ceo, 'password123'));
        $ceo->setLang('ru');
        $ceo->setDepartment($deptAdmin);
        $manager->persist($ceo);

        // 2. HR Manager
        $hr = new User();
        $hr->setPhone('998907654321');
        $hr->setFullName('HR Manager');
        $hr->setRoles(['ROLE_HR']);
        $hr->setPassword($this->userPasswordHasher->hashPassword($hr, 'password123'));
        $hr->setLang('ru');
        $hr->setDepartment($deptHR);
        $manager->persist($hr);

        // 4. Dept Head (IT)
        $head = new User();
        $head->setPhone('998905554433');
        $head->setFullName('Tech Lead');
        $head->setRoles(['ROLE_DEPT_HEAD']);
        $head->setPassword($this->userPasswordHasher->hashPassword($head, 'password123'));
        $head->setDepartment($deptIT);
        $manager->persist($head);

        // 3. Employee (Under IT Head)
        $emp = new User();
        $emp->setPhone('998901112233');
        $emp->setFullName('John Employee');
        $emp->setRoles(['ROLE_EMPLOYEE']);
        $emp->setPassword($this->userPasswordHasher->hashPassword($emp, 'password123'));
        $emp->setLang('uz');
        $emp->setDepartment($deptIT);
        $emp->setParent($head);
        $manager->persist($emp);

        // 5. Accountant
        $acc = new User();
        $acc->setPhone('998909998877');
        $acc->setFullName('Accountant User');
        $acc->setRoles(['ROLE_ACCOUNTANT']);
        $acc->setPassword($this->userPasswordHasher->hashPassword($acc, 'password123'));
        $acc->setDepartment($deptFinance);
        $manager->persist($acc);

        $manager->flush();
    }
}
