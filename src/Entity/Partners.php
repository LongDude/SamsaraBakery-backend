<?php

namespace App\Entity;

use App\Repository\PartnersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnersRepository::class)]
class Partners
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $firmname = null;

    #[ORM\Column(length: 128)]
    private ?string $address = null;

    #[ORM\Column(length: 20)]
    private ?string $contact_number = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $representatives;

    /**
     * @var Collection<int, Orders>
     */
    #[ORM\OneToMany(targetEntity: Orders::class, mappedBy: 'reciever_partner')]
    private Collection $orders;

    public function __construct()
    {
        $this->representatives = new ArrayCollection();
        $this->orders = new ArrayCollection();
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
     * @return Collection<int, Orders>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setRecieverPartner($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getRecieverPartner() === $this) {
                $order->setRecieverPartner(null);
            }
        }

        return $this;
    }
}
