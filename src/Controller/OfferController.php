<?php

namespace App\Controller;

use App\Service\OfferService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends AbstractController
{
    public function __construct(
        private OfferService $offerService,
    ) {}

    #[Route('/api/offers', name: 'app_offer_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $offer = $this->offerService->createOffer($data['title'], $data['description'], $this->getUser());

        return new JsonResponse(['message' => 'Annonce créée avec succès !', 'id' => $offer->getId()], 201);
    }

    #[Route('/api/offers', name: 'app_offers_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $offers = $this->offerService->getAvailableOffers();

        $results = array_map(fn($offer) => [
            'id' => $offer->getId(),
            'title' => $offer->getTitle(),
            'description' => $offer->getDescription(),
            'owner' => $offer->getOwner()->getEmail(),
        ], $offers);

        return new JsonResponse($results);
    }
}
