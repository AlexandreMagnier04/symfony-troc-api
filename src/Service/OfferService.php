<?php

namespace App\Service;

use App\Entity\Offer;
use App\Entity\TrocProposal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OfferService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function createOffer(string $title, string $description, User $owner): Offer
    {
        $offer = new Offer();
        $offer->setTitle($title);
        $offer->setDescription($description);
        $offer->setStatus('available');
        $offer->setOwner($owner);

        $this->em->persist($offer);
        $this->em->flush();

        return $offer;
    }

    /** @return Offer[] */
    public function getAvailableOffers(): array
    {
        return $this->em->getRepository(Offer::class)->findBy(['status' => 'available']);
    }

    public function deleteOffer(int $id): bool
    {
        $offer = $this->em->getRepository(Offer::class)->find($id);

        if (!$offer) {
            return false;
        }

        $proposals = $this->em->getRepository(TrocProposal::class)
            ->createQueryBuilder('p')
            ->where('p.requestedItem = :offer OR p.offeredItem = :offer')
            ->setParameter('offer', $offer)
            ->getQuery()
            ->getResult();

        foreach ($proposals as $proposal) {
            $this->em->remove($proposal);
        }

        $this->em->remove($offer);
        $this->em->flush();

        return true;
    }
}
