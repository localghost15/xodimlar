<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PurchaseRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 100)]
    private ?string $category = null;

    #[ORM\Column(length: 50, options: ['default' => 'asset'])] // asset or consumable
    private ?string $type = 'asset';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoPath = null;

    #[ORM\Column(length: 50, options: ['default' => 'new'])]
    private ?string $status = 'new';

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $rejectedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeInterface $headApprovedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeInterface $budgetConfirmedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeInterface $ceoApprovedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function setHeadApprovedAt(?\DateTimeInterface $date): static
    {
        $this->headApprovedAt = $date;
        return $this;
    }

    public function setBudgetConfirmedAt(?\DateTimeInterface $date): static
    {
        $this->budgetConfirmedAt = $date;
        return $this;
    }

    public function setCeoApprovedAt(?\DateTimeInterface $date): static
    {
        $this->ceoApprovedAt = $date;
        return $this;
    }
}
