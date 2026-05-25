<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    # id de l'offre
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    # titre de l'offre
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    # description de l'offre
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    # statut de l'offre
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    # propriétaire de l'offre
    private ?User $owner = null;

    /**
     * @var Collection<int, TrocProposal>
     */
    #[ORM\OneToMany(targetEntity: TrocProposal::class, mappedBy: 'requestedItem')]
    # proposition de troc pour cette offre
    private Collection $trocProposals;

    public function __construct()
    {
        $this->trocProposals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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
            $trocProposal->setRequestedItem($this);
        }

        return $this;
    }

    public function removeTrocProposal(TrocProposal $trocProposal): static
    {
        if ($this->trocProposals->removeElement($trocProposal)) {
            // set the owning side to null (unless already changed)
            if ($trocProposal->getRequestedItem() === $this) {
                $trocProposal->setRequestedItem(null);
            }
        }

        return $this;
    }
}
