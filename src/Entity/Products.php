<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Assortiment>
     */
    #[ORM\OneToMany(targetEntity: Assortiment::class, mappedBy: 'product')]
    private Collection $assortiments;

    /**
     * @var Collection<int, ProductsMovement>
     */
    #[ORM\OneToMany(targetEntity: ProductsMovement::class, mappedBy: 'product')]
    private Collection $productsMovements;

    /**
     * @var Collection<int, Orders>
     */
    #[ORM\OneToMany(targetEntity: Orders::class, mappedBy: 'product')]
    private Collection $orders;

    #[ORM\Column]
    private ?float $production_cost = null;

    #[ORM\Column(length: 128)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $quantity_storaged = null;

    public function __construct()
    {
        $this->assortiments = new ArrayCollection();
        $this->productsMovements = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $assortiment->setProduct($this);
        }

        return $this;
    }

    public function removeAssortiment(Assortiment $assortiment): static
    {
        if ($this->assortiments->removeElement($assortiment)) {
            // set the owning side to null (unless already changed)
            if ($assortiment->getProduct() === $this) {
                $assortiment->setProduct(null);
            }
        }

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
            $productsMovement->setProduct($this);
        }

        return $this;
    }

    public function removeProductsMovement(ProductsMovement $productsMovement): static
    {
        if ($this->productsMovements->removeElement($productsMovement)) {
            // set the owning side to null (unless already changed)
            if ($productsMovement->getProduct() === $this) {
                $productsMovement->setProduct(null);
            }
        }

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
            $order->setProduct($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getProduct() === $this) {
                $order->setProduct(null);
            }
        }

        return $this;
    }

    public function getProductionCost(): ?float
    {
        return $this->production_cost;
    }

    public function setProductionCost(float $production_cost): static
    {
        $this->production_cost = $production_cost;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantityStoraged(): ?int
    {
        return $this->quantity_storaged;
    }

    public function setQuantityStoraged(int $quantity_storaged): static
    {
        $this->quantity_storaged = $quantity_storaged;

        return $this;
    }
}
