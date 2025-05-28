<?php

namespace App\Entity;

use App\Enum\DeliveryStatus;
use App\Repository\DeliveriesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeliveriesRepository::class)]
class Deliveries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $quantity = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?float $price = 0;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Suppliers $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ingredients $ingredient = null;

    #[ORM\Column(enumType: DeliveryStatus::class)]
    private ?DeliveryStatus $status = DeliveryStatus::REQUEST_SENT;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSupplier(): ?Suppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?Suppliers $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getIngredient(): ?Ingredients
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredients $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getStatus(): ?DeliveryStatus
    {
        return $this->status;
    }

    public function setStatus(DeliveryStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
