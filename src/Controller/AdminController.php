<?php

namespace App\Controller;

use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    // Supprimer n'importe quelle annonce
    #[Route('/api/admin/offers/{id}', name: 'app_admin_delete_offer', methods: ['DELETE'])]
    public function deleteOffer(int $id, EntityManagerInterface $em): JsonResponse
    {
        $offer = $em->getRepository(Offer::class)->find($id);

        if (!$offer) {
            return new JsonResponse(['error' => 'Annonce introuvable'], 404);
        }

        // On va chercher et nettoyer toutes les propositions de troc liées à cette offre
        $proposals = $em->getRepository(\App\Entity\TrocProposal::class)->createQueryBuilder('p')
            ->where('p.requestedItem = :offer OR p.offeredItem = :offer')
            ->setParameter('offer', $offer)
            ->getQuery()
            ->getResult();

        foreach ($proposals as $proposal) {
            $em->remove($proposal);
        }

        // Ensuite, on supprime l'offre elle-même
        $em->remove($offer);
        $em->flush();

        return new JsonResponse(['message' => 'L\'annonce et ses propositions liées ont été supprimées par l\'administrateur.']);
    }
}
