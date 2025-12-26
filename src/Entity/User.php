<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    // Department mapping handled by $departmentRel below
    // Wait, plan said "User: department_id". I will map it as ManyToOne if departments are shared.
    // Plan: "department_id (FK -> department.id)". So User M:1 Department.

    // Changing to ManyToOne
    #[ORM\ManyToOne(targetEntity: Department::class)]
    #[ORM\JoinColumn(name: 'department_id', referencedColumnName: 'id', nullable: true)]
    private ?Department $departmentRel = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?self $parent = null;

    #[ORM\Column(type: 'bigint', unique: true, nullable: true)]
    private ?string $telegramId = null;

    #[ORM\Column(length: 2, options: ['default' => 'ru'])]
    private ?string $lang = 'ru';

    // Getters and Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->phone;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user has at least ROLE_EMPLOYEE
        $roles[] = 'ROLE_EMPLOYEE';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getDepartment(): ?Department
    {
        return $this->departmentRel;
    }

    public function setDepartment(?Department $department): static
    {
        $this->departmentRel = $department;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getTelegramId(): ?string
    {
        return $this->telegramId;
    }

    public function setTelegramId(?string $telegramId): static
    {
        $this->telegramId = $telegramId;
        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): static
    {
        $this->lang = $lang;
        return $this;
    }
}
