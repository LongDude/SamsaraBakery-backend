<?php

namespace App\Entity\Views;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: "director_affiliate_finance_view")]
class DirectorAffiliatesFinanceView
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $affiliate_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $affiliate_address;

    #[ORM\Column(type: "string", length: 20)]
    private string $contact_number;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $manager_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $manager_name;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $manager_phone;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $day;

    #[ORM\Column(type: "float")]
    private float $revenue;

    #[ORM\Column(type: "float")]
    private float $cost;

    #[ORM\Column(type: "float")]
    private float $net_revenue;

    public function getDay(): \DateTimeInterface
    {
        return $this->day;
    }

    public function getRevenue(): float
    {
        return $this->revenue;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function getNetRevenue(): float
    {
        return $this->net_revenue;
    }

    public function getAffiliateId(): int
    {
        return $this->affiliate_id;
    }

    public function getAffiliateAddress(): string
    {
        return $this->affiliate_address;
    }

    public function getContactNumber(): string
    {
        return $this->contact_number;
    }

    public function getManagerId(): ?int
    {
        return $this->manager_id;
    }

    public function getManagerName(): ?string
    {
        return $this->manager_name;
    }

    public function getManagerPhone(): ?string
    {
        return $this->manager_phone;
    }
}
