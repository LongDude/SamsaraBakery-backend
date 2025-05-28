<?php

namespace App\Entity;

use App\Repository\ProductsMovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductsMovementRepository::class)]
class ProductsMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productsMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Affiliates $affiliate = null;

    #[ORM\ManyToOne(inversedBy: 'productsMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?float $realised_price = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $realised_count = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?float $recieved_cost = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $recieved_count = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAffiliate(): ?Affiliates
    {
        return $this->affiliate;
    }

    public function setAffiliate(?Affiliates $affiliate): static
    {
        $this->affiliate = $affiliate;

        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getRealisedPrice(): ?float
    {
        return $this->realised_price;
    }

    public function setRealisedPrice(float $realised_price): static
    {
        $this->realised_price = $realised_price;

        return $this;
    }

    public function getRealisedCount(): ?int
    {
        return $this->realised_count;
    }

    public function setRealisedCount(int $realised_count): static
    {
        $this->realised_count = $realised_count;

        return $this;
    }

    public function getRecievedCost(): ?float
    {
        return $this->recieved_cost;
    }

    public function setRecievedCost(float $recieved_cost): static
    {
        $this->recieved_cost = $recieved_cost;

        return $this;
    }

    public function getRecievedCount(): ?int
    {
        return $this->recieved_count;
    }

    public function setRecievedCount(int $recieved_count): static
    {
        $this->recieved_count = $recieved_count;

        return $this;
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
}
