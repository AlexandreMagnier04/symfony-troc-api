<?php

namespace App\Controller;

use App\Entity\TrocProposal;
use App\Service\TrocProposalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TrocProposalController extends AbstractController
{
    public function __construct(
        private TrocProposalService $trocProposalService,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/api/proposals', name: 'app_troc_proposal', methods: ['POST'])]
    public function propose(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->trocProposalService->propose($this->getUser(), $data['requested_id'], $data['offered_id']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }

        return new JsonResponse(['message' => 'Proposition de troc envoyée !'], 201);
    }

    #[Route('/api/proposals/{id}/respond', name: 'app_troc_proposal_respond', methods: ['PATCH'])]
    public function respond(int $id, Request $request): JsonResponse
    {
        $trade = $this->em->getRepository(TrocProposal::class)->find($id);

        if (!$trade) {
            return new JsonResponse(['error' => 'Proposition de troc introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $message = $this->trocProposalService->respond($trade, $this->getUser(), $data['action'] ?? '');
        } catch (\InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'autorisé') ? 403 : 400;
            return new JsonResponse(['error' => $e->getMessage()], $status);
        }

        return new JsonResponse(['message' => $message]);
    }
}
