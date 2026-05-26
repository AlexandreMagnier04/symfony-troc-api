<?php

namespace App\Controller;

use App\Service\OfferService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private OfferService $offerService,
    ) {}

    #[Route('/api/admin/offers/{id}', name: 'app_admin_delete_offer', methods: ['DELETE'])]
    public function deleteOffer(int $id): JsonResponse
    {
        if (!$this->offerService->deleteOffer($id)) {
            return new JsonResponse(['error' => 'Annonce introuvable'], 404);
        }

        return new JsonResponse(['message' => 'L\'annonce et ses propositions liées ont été supprimées par l\'administrateur.']);
    }
}
