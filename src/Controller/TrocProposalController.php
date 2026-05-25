<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;


final class TrocProposalController extends AbstractController
{
    #[Route('/api/proposals', name: 'app_troc_proposal', methods: ['POST'])]
    public function propose(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser(); // Le demandeur
        $data = json_decode($request->getContent(), true);

        // On récupère les deux objets impliqués
        $requestedItem = $em->getRepository(\App\Entity\Offer::class)->find($data['requested_id']);
        $offeredItem = $em->getRepository(\App\Entity\Offer::class)->find($data['offered_id']);

        // On vérifie que le demandeur est bien propriétaire de l'objet offert
        if ($offeredItem->getOwner() !== $user) {
            return new JsonResponse(['error' => 'Cet objet ne vous appartient pas'], 403);
        }

        $troc = new \App\Entity\TrocProposal();
        $troc->setRequester($user);
        $troc->setRequestedItem($requestedItem);
        $troc->setOfferedItem($offeredItem);
        $troc->setStatus('pending'); // État "En cours"
        $troc->setCreatedAt(new \DateTimeImmutable());

        $em->persist($troc);
        $em->flush();

        return new JsonResponse(['message' => 'Proposition de troc envoyée !'], 201);
    }
    #[Route('/api/proposals/{id}/respond', name: 'app_troc_proposal_respond', methods: ['PATCH'])]
    public function respond(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser(); // L'utilisateur connecté (celui qui doit répondre)
        $trade = $em->getRepository(\App\Entity\TrocProposal::class)->find($id);

        // 1. On vérifie que la proposition de troc existe
        if (!$trade) {
            return new JsonResponse(['error' => 'Proposition de troc introuvable'], 404);
        }

        // 2. SECURITÉ : Seul le propriétaire de l'objet demandé peut répondre
        // (On vérifie que l'item voulu appartient bien à l'utilisateur actuel)
        if ($trade->getRequestedItem()->getOwner() !== $user) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à répondre à ce troc'], 403);
        }

        // 3. Récupérer l'action (accept ou refuse)
        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? null;

        if ($action === 'accept') {
            $trade->setStatus('accepted');

            // LOGIQUE MÉTIER : On marque les deux objets comme "troqués"
            // Ils ne seront plus affichés dans la liste des objets disponibles
            $trade->getRequestedItem()->setStatus('traded');
            $trade->getOfferedItem()->setStatus('traded');

            $message = 'Troc accepté ! Les objets sont désormais marqués comme troqués.';
        } elseif ($action === 'refuse') {
            $trade->setStatus('refused');
            $message = 'Troc refusé.';
        } else {
            return new JsonResponse(['error' => 'Action non valide (utilisez "accept" ou "refuse")'], 400);
        }

        $em->flush(); // On enregistre

        return new JsonResponse(['message' => $message]);
    }
}
