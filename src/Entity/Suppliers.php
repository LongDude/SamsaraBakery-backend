<?php

namespace App\Entity;

use App\Repository\SuppliersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuppliersRepository::class)]
class Suppliers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firmname = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 20)]
    private ?string $contact_number = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'suppliers_represent')]
    private Collection $representatives;

    /**
     * @var Collection<int, Deliveries>
     */
    #[ORM\OneToMany(targetEntity: Deliveries::class, mappedBy: 'supplier')]
    private Collection $deliveries;

    public function __construct()
    {
        $this->representatives = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirmname(): ?string
    {
        return $this->firmname;
    }

    public function setFirmname(string $firmname): static
    {
        $this->firmname = $firmname;

        return $this;
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

    /**
     * @return Collection<int, User>
     */
    public function getRepresentatives(): Collection
    {
        return $this->representatives;
    }

    public function addRepresentative(User $representative): static
    {
        if (!$this->representatives->contains($representative)) {
            $this->representatives->add($representative);
        }

        return $this;
    }

    public function removeRepresentative(User $representative): static
    {
        $this->representatives->removeElement($representative);

        return $this;
    }

    /**
     * @return Collection<int, Deliveries>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Deliveries $delivery): static
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setSupplier($this);
        }

        return $this;
    }

    public function removeDelivery(Deliveries $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getSupplier() === $this) {
                $delivery->setSupplier(null);
            }
        }

        return $this;
    }
}
