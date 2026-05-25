<?php

namespace App\Controller;

use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends AbstractController
{
    #[Route('/api/offers', name: 'app_offer_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // 1. Récupérer l'utilisateur grâce au Token JWT
        $user = $this->getUser();

        // 2. Décoder le JSON reçu
        $data = json_decode($request->getContent(), true);

        // 3. Créer l'entité et remplir les données
        $offer = new Offer();
        $offer->setTitle($data['title']);
        $offer->setDescription($data['description']);
        $offer->setStatus('available'); // État par défaut
        $offer->setOwner($user); // On lie l'annonce à l'utilisateur connecté

        // 4. Sauvegarder en base de données
        $em->persist($offer);
        $em->flush();

        return new JsonResponse([
            'message' => 'Annonce créée avec succès !',
            'id' => $offer->getId()
        ], 201);
    }

    #[Route('/api/offers', name: 'app_offers_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        // On récupère uniquement les annonces "disponibles"
        $offers = $em->getRepository(Offer::class)->findBy(['status' => 'available']);

        $results = [];
        foreach ($offers as $offer) {
            $results[] = [
                'id' => $offer->getId(),
                'title' => $offer->getTitle(),
                'description' => $offer->getDescription(),
                'owner' => $offer->getOwner()->getEmail(), // On affiche l'email du proprio
            ];
        }

        return new JsonResponse($results);
    }
}
