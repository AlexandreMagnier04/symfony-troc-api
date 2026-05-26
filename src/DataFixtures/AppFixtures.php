<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Offer;
use App\Entity\TrocProposal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // === 1. CRÉATION DES UTILISATEURS ===

        // L'Admin du site
        $admin = new User();
        $admin->setEmail('admin@troc.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Utilisateur A
        $userA = new User();
        $userA->setEmail('alex@test.fr');
        $userA->setRoles(['ROLE_USER']);
        $userA->setPassword($this->hasher->hashPassword($userA, 'password123'));
        $manager->persist($userA);

        // Utilisateur B
        $userB = new User();
        $userB->setEmail('julie@test.fr');
        $userB->setRoles(['ROLE_USER']);
        $userB->setPassword($this->hasher->hashPassword($userB, 'password123'));
        $manager->persist($userB);


        // === 2. CRÉATION DES OFFRES ===

        // Offres de Alex (User A)
        $offer1 = new Offer();
        $offer1->setTitle('Console PS4 Slim 500Go');
        $offer1->setDescription('En parfait état avec une manette. Cherche un vélo ou une carte graphique.');
        $offer1->setStatus('available');
        $offer1->setOwner($userA);
        $manager->persist($offer1);

        $offer2 = new Offer();
        $offer2->setTitle('Écran PC 24 pouces ASUS');
        $offer2->setDescription('Dalle IPS 144Hz, idéal pour le gaming. Contre des mangas ou du matériel audio.');
        $offer2->setStatus('available');
        $offer2->setOwner($userA);
        $manager->persist($offer2);

        // Offres de Julie (User B)
        $offer3 = new Offer();
        $offer3->setTitle('VTT Rockrider Noir');
        $offer3->setDescription('Taille M, bon état général, freins à disque. Échange contre une console de jeux.');
        $offer3->setStatus('available');
        $offer3->setOwner($userB);
        $manager->persist($offer3);

        $offer4 = new Offer();
        $offer4->setTitle('Collection complète Manga Naruto');
        $offer4->setDescription('Tomes 1 à 72 en excellent état. Étudie toute proposition.');
        $offer4->setStatus('available');
        $offer4->setOwner($userB);
        $manager->persist($offer4);


        // === 3. CRÉATION D'UNE PROPOSITION DE TROC ===

        // Julie (User B) propose son VTT (offer3) contre la PS4 de Alex (offer1)
        $proposal = new TrocProposal();
        $proposal->setRequester($userB);
        $proposal->setOfferer($userA);
        $proposal->setRequestedItem($offer1);
        $proposal->setOfferedItem($offer3);
        $proposal->setStatus('pending');
        $proposal->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($proposal);

        // On envoie tout en base de données !
        $manager->flush();
    }
}
