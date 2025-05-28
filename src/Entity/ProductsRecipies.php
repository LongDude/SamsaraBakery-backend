<?php

namespace App\Entity;

use App\Repository\ProductsRecipiesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRecipiesRepository::class)]
class ProductsRecipies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productsRecipies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $product_id = null;

    #[ORM\ManyToOne(inversedBy: 'productsRecipies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ingredients $ingredient_id = null;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $quantity = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?Products
    {
        return $this->product_id;
    }

    public function setProductId(?Products $product_id): static
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getIngredientId(): ?Ingredients
    {
        return $this->ingredient_id;
    }

    public function setIngredientId(?Ingredients $ingredient_id): static
    {
        $this->ingredient_id = $ingredient_id;

        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
