<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 64)]
    private ?string $Username = null;

    /**
     * @var Collection<int, Suppliers>
     */
    #[ORM\ManyToMany(targetEntity: Suppliers::class, mappedBy: 'representatives')]
    private Collection $suppliers_represent;
    /**
     * @var Collection<int, Partners>
     */
    #[ORM\ManyToMany(targetEntity: Partners::class, mappedBy: 'representatives')]
    private Collection $partners_represent;

    #[ORM\OneToOne(mappedBy: 'manager')]
    private ?Affiliates $affiliate = null;
    
    public function __construct()
    {
        $this->suppliers_represent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $roles[] = 'ROLE_USER';
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): static
    {
        $this->Username = $Username;

        return $this;
    }

    /**
     * @return Collection<int, Partners>
     */
    public function getPartnersRepresent(): Collection
    {
        return $this->partners_represent;
    }

    public function addPartnersRepresent(Partners $partners_represent): static
    {
        if (!$this->partners_represent->contains($partners_represent)) {
            $this->partners_represent->add($partners_represent);
            $partners_represent->addRepresentative($this);
        }

        return $this;
    }

    public function removePartnersRepresent(Partners $partners_represent): static
    {
        if ($this->partners_represent->removeElement($partners_represent)) {
            $partners_represent->removeRepresentative($this);
        }

        return $this;
    }


    /**
     * @return Collection<int, Suppliers>
     */
    public function getSuppliersRepresent(): Collection
    {
        return $this->suppliers_represent;
    }

    public function addSuppliersRepresent(Suppliers $suppliersRepresent): static
    {
        if (!$this->suppliers_represent->contains($suppliersRepresent)) {
            $this->suppliers_represent->add($suppliersRepresent);
            $suppliersRepresent->addRepresentative($this);
        }

        return $this;
    }

    public function removeSuppliersRepresent(Suppliers $suppliersRepresent): static
    {
        if ($this->suppliers_represent->removeElement($suppliersRepresent)) {
            $suppliersRepresent->removeRepresentative($this);
        }

        return $this;
    }

    public function getAffiliate(): ?Affiliates
    {
        return $this->affiliate;
    }

    public function setAffiliate(?Affiliates $affiliate): static
    {
        // unset the owning side of the relation if necessary
        if ($affiliate === null && $this->affiliate !== null) {
            $this->affiliate->setManager(null);
        }

        // set the owning side of the relation if necessary
        if ($affiliate !== null && $affiliate->getManager() !== $this) {
            $affiliate->setManager($this);
        }

        $this->affiliate = $affiliate;

        return $this;
    }
}
