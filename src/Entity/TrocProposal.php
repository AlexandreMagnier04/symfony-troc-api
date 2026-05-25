<?php

namespace App\Entity;

use App\Repository\TrocProposalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrocProposalRepository::class)]
class TrocProposal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    # id de la proposition de troc
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    # statut de la proposition de troc
    private ?string $status = null;

    #[ORM\Column]
    # date de création de la proposition de troc
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'trocProposals')]
    #[ORM\JoinColumn(nullable: false)]
    # utilisateur qui propose le troc
    private ?User $requester = null;

    #[ORM\ManyToOne(inversedBy: 'trocProposals')]
    #[ORM\JoinColumn(nullable: false)]
    # objet demandé dans le troc
    private ?Offer $requestedItem = null;

    #[ORM\ManyToOne(inversedBy: 'trocProposals')]
    #[ORM\JoinColumn(nullable: false)]
    # objet proposé dans le troc
    private ?Offer $offeredItem = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requester): static
    {
        $this->requester = $requester;

        return $this;
    }

    public function getRequestedItem(): ?Offer
    {
        return $this->requestedItem;
    }

    public function setRequestedItem(?Offer $requestedItem): static
    {
        $this->requestedItem = $requestedItem;

        return $this;
    }

    public function getOfferedItem(): ?Offer
    {
        return $this->offeredItem;
    }

    public function setOfferedItem(?Offer $offeredItem): static
    {
        $this->offeredItem = $offeredItem;

        return $this;
    }
}
