<?php

namespace App\Entity\Views;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: "director_orders_view")]
class DirectorOrdersView
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $order_id;

    #[ORM\Column(type: "string", length: 128)]
    private string $partner_firmname;

    #[ORM\Column(type: "string", length: 128)]
    private string $product;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "integer")]
    private int $quantity;

    #[ORM\Column(type: "string", length: 32)]
    private string $status;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    public function getOrderId(): int
    {
        return $this->order_id;
    }

    public function getPartnerFirmname(): string
    {
        return $this->partner_firmname;
    }

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
}
