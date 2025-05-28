<?php

namespace App\Entity;

use App\Repository\ProductsRecipiesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;  
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

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $quantity = 0;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
