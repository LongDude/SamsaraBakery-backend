<?php

namespace App\Entity\Views;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: "director_production_report_view")]
class DirectorProductionReportView
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 128)]
    private string $product_name;

    #[ORM\Id]
    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date;

    #[ORM\Column(type: "float")]
    private float $sells_revenue;

    #[ORM\Column(type: "float")]
    private float $orders_revenue;

    #[ORM\Column(type: "float")]
    private float $production_cost;

    #[ORM\Column(type: "integer")]
    private int $producted_count;

    #[ORM\Column(type: "integer")]
    private int $sold_count;

    #[ORM\Column(type: "integer")]
    private int $ordered_count;

    #[ORM\Column(type: "float")]
    private float $realisation_index;

    #[ORM\Column(type: "float")]
    private float $order_index;

    #[ORM\Column(type: "float")]
    private float $net_revenue;

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getSellsRevenue(): float
    {
        return $this->sells_revenue;
    }

    public function getOrdersRevenue(): float
    {
        return $this->orders_revenue;
    }

    public function getProductionCost(): float
    {
        return $this->production_cost;
    }

    public function getProductedCount(): int
    {
        return $this->producted_count;
    }

    public function getSoldCount(): int
    {
        return $this->sold_count;
    }

    public function getOrderedCount(): int
    {
        return $this->ordered_count;
    }

    public function getRealisationIndex(): float
    {
        return $this->realisation_index;
    }

    public function getOrderIndex(): float
    {
        return $this->order_index;
    }

    public function getNetRevenue(): float
    {
        return $this->net_revenue;
    }
}
