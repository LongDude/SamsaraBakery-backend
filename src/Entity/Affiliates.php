<?php

namespace App\Entity;

use App\Repository\AffiliatesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AffiliatesRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_ADDRESS', fields: ['address'])]
class Affiliates
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $address = null;

    #[ORM\Column(length: 20)]
    private ?string $contact_number = null;

    #[ORM\OneToOne(inversedBy: 'affiliate')]
    private ?User $manager = null;

    /**
     * @var Collection<int, ProductsMovement>
     */
    #[ORM\OneToMany(targetEntity: ProductsMovement::class, mappedBy: 'affiliate')]
    private Collection $productsMovements;

    /**
     * @var Collection<int, Assortiment>
     */
    #[ORM\OneToMany(targetEntity: Assortiment::class, mappedBy: 'affiliate')]
    private Collection $assortiments;

    public function __construct()
    {
        $this->productsMovements = new ArrayCollection();
        $this->assortiments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->contact_number;
    }

    public function setContactNumber(string $contact_number): static
    {
        $this->contact_number = $contact_number;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, ProductsMovement>
     */
    public function getProductsMovements(): Collection
    {
        return $this->productsMovements;
    }

    public function addProductsMovement(ProductsMovement $productsMovement): static
    {
        if (!$this->productsMovements->contains($productsMovement)) {
            $this->productsMovements->add($productsMovement);
            $productsMovement->setAffiliate($this);
        }

        return $this;
    }

    public function removeProductsMovement(ProductsMovement $productsMovement): static
    {
        if ($this->productsMovements->removeElement($productsMovement)) {
            // set the owning side to null (unless already changed)
            if ($productsMovement->getAffiliate() === $this) {
                $productsMovement->setAffiliate(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Assortiment>
     */
    public function getAssortiments(): Collection
    {
        return $this->assortiments;
    }

    public function addAssortiment(Assortiment $assortiment): static
    {
        if (!$this->assortiments->contains($assortiment)) {
            $this->assortiments->add($assortiment);
            $assortiment->setAffiliate($this);
        }

        return $this;
    }

    public function removeAssortiment(Assortiment $assortiment): static
    {
        $this->assortiments->removeElement($assortiment);
        return $this;
    }
}
