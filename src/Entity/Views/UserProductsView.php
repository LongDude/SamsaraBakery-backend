<?php

namespace App\Entity\Views;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: "user_products_view")]
class UserProductsView
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 128)]
    private string $product;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    public function getProduct(): string
    {
        return $this->product;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}