<?php

namespace App\Entity;

use App\Repository\AssortimentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssortimentRepository::class)]
class Assortiment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assortiments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $quantity = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?float $price = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $daily_delivery = 0;

    #[ORM\ManyToOne(inversedBy: 'assortiments')]
    private Affiliates $affiliate;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDailyDelivery(): ?int
    {
        return $this->daily_delivery;
    }

    public function setDailyDelivery(int $daily_delivery): static
    {
        $this->daily_delivery = $daily_delivery;

        return $this;
    }

    public function getAffiliate(): Affiliates
    {
        return $this->affiliate;
    }

    public function setAffiliate(Affiliates $affiliate): static
    {
        $this->affiliate = $affiliate;

        return $this;
    }
}
