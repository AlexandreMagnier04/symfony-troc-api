<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    # id de l'utilisateur
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    # email de l'utilisateur 
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    # rôles de l'utilisateur
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    # mot de passe de l'utilisateur (haché)
    private ?string $password = null;

    /**
     * @var Collection<int, Offer>
     */
    #[ORM\OneToMany(targetEntity: Offer::class, mappedBy: 'owner')]
    # offres créées par cet utilisateur
    private Collection $offers;

    /**
     * @var Collection<int, TrocProposal>
     */
    #[ORM\OneToMany(targetEntity: TrocProposal::class, mappedBy: 'requester')]
    # propositions de troc faites par cet utilisateur
    private Collection $trocProposals;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->trocProposals = new ArrayCollection();
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
        $this->roles = $roles;

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
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): static
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setOwner($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): static
    {
        if ($this->offers->removeElement($offer)) {
            // set the owning side to null (unless already changed)
            if ($offer->getOwner() === $this) {
                $offer->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrocProposal>
     */
    public function getTrocProposals(): Collection
    {
        return $this->trocProposals;
    }

    public function addTrocProposal(TrocProposal $trocProposal): static
    {
        if (!$this->trocProposals->contains($trocProposal)) {
            $this->trocProposals->add($trocProposal);
            $trocProposal->setRequester($this);
        }

        return $this;
    }

    public function removeTrocProposal(TrocProposal $trocProposal): static
    {
        if ($this->trocProposals->removeElement($trocProposal)) {
            // set the owning side to null (unless already changed)
            if ($trocProposal->getRequester() === $this) {
                $trocProposal->setRequester(null);
            }
        }

        return $this;
    }
}
