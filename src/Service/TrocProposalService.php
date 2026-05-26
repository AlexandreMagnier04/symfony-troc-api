<?php

namespace App\Service;

use App\Entity\Offer;
use App\Entity\TrocProposal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TrocProposalService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function propose(User $requester, int $requestedId, int $offeredId): TrocProposal
    {
        $requestedItem = $this->em->getRepository(Offer::class)->find($requestedId);
        $offeredItem = $this->em->getRepository(Offer::class)->find($offeredId);

        if ($offeredItem->getOwner() !== $requester) {
            throw new \InvalidArgumentException('Cet objet ne vous appartient pas');
        }

        $troc = new TrocProposal();
        $troc->setRequester($requester);
        $troc->setOfferer($requestedItem->getOwner());
        $troc->setRequestedItem($requestedItem);
        $troc->setOfferedItem($offeredItem);
        $troc->setStatus('pending');
        $troc->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($troc);
        $this->em->flush();

        return $troc;
    }

    public function respond(TrocProposal $trade, User $user, string $action): string
    {
        if ($trade->getRequestedItem()->getOwner() !== $user) {
            throw new \InvalidArgumentException('Vous n\'êtes pas autorisé à répondre à ce troc');
        }

        if ($action === 'accept') {
            $trade->setStatus('accepted');
            $trade->getRequestedItem()->setStatus('traded');
            $trade->getOfferedItem()->setStatus('traded');
            $message = 'Troc accepté ! Les objets sont désormais marqués comme troqués.';
        } elseif ($action === 'refuse') {
            $trade->setStatus('refused');
            $message = 'Troc refusé.';
        } else {
            throw new \InvalidArgumentException('Action non valide (utilisez "accept" ou "refuse")');
        }

        $this->em->flush();

        return $message;
    }
}
