<?php

namespace App\Entity;

use App\Repository\IngredientsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientsRepository::class)]
class Ingredients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\Column(length: 128)]
    private ?string $name = null;

    /**
     * @var Collection<int, Deliveries>
     */
    #[ORM\OneToMany(targetEntity: Deliveries::class, mappedBy: 'ingredient')]
    private Collection $deliveries;

    /**
     * @var Collection<int, ProductsRecipies>
     */
    #[ORM\OneToMany(targetEntity: ProductsRecipies::class, mappedBy: 'ingredient_id')]
    private Collection $productsRecipies;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->product = new ArrayCollection();
        $this->productsRecipies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $delivery->setIngredient($this);
        }

        return $this;
    }

    public function removeDelivery(Deliveries $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getIngredient() === $this) {
                $delivery->setIngredient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductsRecipies>
     */
    public function getProductsRecipies(): Collection
    {
        return $this->productsRecipies;
    }

    public function addProductsRecipy(ProductsRecipies $productsRecipy): static
    {
        if (!$this->productsRecipies->contains($productsRecipy)) {
            $this->productsRecipies->add($productsRecipy);
            $productsRecipy->setIngredientId($this);
        }

        return $this;
    }

    public function removeProductsRecipy(ProductsRecipies $productsRecipy): static
    {
        if ($this->productsRecipies->removeElement($productsRecipy)) {
            // set the owning side to null (unless already changed)
            if ($productsRecipy->getIngredientId() === $this) {
                $productsRecipy->setIngredientId(null);
            }
        }

        return $this;
    }
}
