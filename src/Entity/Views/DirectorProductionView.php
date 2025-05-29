<?php

namespace App\Entity\Views;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: "director_production_view")]
class DirectorProductionView
{
    #[ORM\Column(type: "integer")]
    private int $product_id;

    #[ORM\Column(type: "string", length: 128)]
    private string $product_name;

    #[ORM\Column(type: "float")]
    private float $production_cost;

    public function getProductId(): int
    {
        return $this->product_id;
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductionCost(): float
    {
        return $this->production_cost;
    }
}
