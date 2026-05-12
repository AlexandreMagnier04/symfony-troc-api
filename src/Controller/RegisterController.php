<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // 1. Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);

        // 2. Créer une nouvelle instance de l'entité User
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']); // Rôle par défaut

        // 3. Hasher le mot de passe 
        $user->setPassword(
            $userPasswordHasher->hashPassword($user, $data['password'])
        );

        // 4. Enregistrer en base de données 
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Utilisateur créé avec succès !'], 201);
    }
}
