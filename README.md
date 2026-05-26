# TrocAPI - Marketplace de Troc (Framework PHP Symfony)

Cette API REST permet à des utilisateurs de s'authentifier, de publier des annonces de trocs et de gérer leurs propositions de troc, en interagissant via un système sécurisé.

---

## 1. Architecture Technique & Choix Technologiques

L'application respecte une **architecture n-tiers** et les standards des API REST modernes :

- **Framework :** Symfony 6/7.
- **Persistance & ORM :** Doctrine ORM pour la gestion relationnelle de la base de données MySQL (via XAMPP).
- **Sécurité (Authentification Stateless) :** Implémentation de **JWT (JSON Web Token)** via `LexikJWTAuthenticationBundle`.
- **Qualité de code :** Respect des conventions de nommage camelCase et utilisation des _Conventional Commits_ pour le suivi Git.

---

## 2. Modèle de Données & Règles Métier de Sécurité

### Schéma Relationnel (Entités)

- **User :** Gère l'authentification (Email, Mot de passe hashé) et possède un tableau de rôles (`ROLE_USER`, `ROLE_ADMIN`).
- **Offer :** Représente une annonce. Liée à un `User` (propriétaire). Possède un statut (`available`, `traded`).
- **TrocProposal :** Table de liaison gérant l'interaction de troc entre un demandeur (`requester`), l'objet demandé (`requestedItem`) et l'objet proposé en échange (`offeredItem`). Statuts gérés en cascade : `pending`, `accepted`, `refused`.

### Règles de Contrôle d'Accès (RBAC)

1. **401 Unauthorized :** Toutes les routes de création ou de modification d'annonces/trocs exigent un Bearer Token JWT valide.
2. **403 Forbidden (Vérification de propriété) :** Un utilisateur ne peut pas valider ou refuser une proposition de troc si l'objet demandé ne lui appartient pas en base de données.
3. **Espace Admin :** Les routes préfixées par `/api/admin/` utilisent le pare-feu Symfony pour restreindre l'accès au seul `ROLE_ADMIN`. L'administrateur dispose d'un droit de modération destructif (suppression d'annonces en nettoyant les propositions liées en amont via PHP pour contourner les blocages de contraintes SQL).

---

## 3. Installation et Procédure de Test Rapide

> 💡 **Note importante concernant le versioning :** J'ai volontairement push le .env ainsi que les clés SSL (`config/jwt/`) pour faciliter l'utilisation. Tout est pré-configuré pour fonctionner immédiatement.

### Prérequis

- PHP 8.2+
- Composer
- XAMPP (avec MySQL démarré sur le port 3306)
- Postman (utiliser la collection fournie pour les tests)

### Procédure de déploiement

**1. Cloner le projet et installer les dépendances :**

```bash
composer install
```

**2. Créer la base de données et exécuter la structure via la migration :**

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

**3. Lancer le serveur de développement Symfony :**

```bash
symfony serve
```

### Injection des Données de Test (Fixtures)

Pour charger immédiatement le jeu de données complet et valider l'API, exécutez :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

**Comptes de test injectés** (mot de passe commun : `password123`, ou `admin123` pour l'admin) :

| Rôle          | Email           | Détails                                                                                    |
| ------------- | --------------- | ------------------------------------------------------------------------------------------ |
| Admin         | `admin@troc.fr` | Pouvoir de suppression sur `/api/admin/offers/{id}`                                        |
| Utilisateur A | `alex@test.fr`  | Possède une PS4 [ID 1] et un Écran PC [ID 2] en annonce                                    |
| Utilisateur B | `julie@test.fr` | Possède un VTT [ID 3] et a émis une proposition de troc `pending` [ID 1] sur la PS4 d'Alex |

---

## 4. Plan de Validation des Erreurs HTTP

| Scénario de Test                         | Route                         | Token            | Statut attendu   | Résultat attendu             |
| ---------------------------------------- | ----------------------------- | ---------------- | ---------------- | ---------------------------- |
| Création anonyme d'une offre             | `POST /api/offers`            | Aucun            | 401 Unauthorized | Bloqué par le Pare-feu JWT   |
| Acceptation d'un troc par un tiers       | `PATCH /api/proposals/1/respond` | Token de Julie   | 403 Forbidden    | Bloqué par le Contrôleur     |
| Suppression d'offre par un User standard | `DELETE /api/admin/offers/1`  | Token d'Alex     | 403 Forbidden    | Bloqué par l'Access Control  |
| Suppression d'offre par un Admin         | `DELETE /api/admin/offers/1`  | Token de l'Admin | 200 OK           | Succès (Nettoyage PHP + SQL) |

---

## 5. Guide de Test détaillé dans Postman (Étape par Étape)

### Étape 1 : L'Authentification (Obtenir le Token de Julie)

1. Envoyez une requête `POST` sur `http://localhost:8000/api/login`.
2. Dans le **Body** (sélectionner `raw` et le format `JSON`), entrez :

```json
{
    "email": "julie@test.fr",
    "password": "password123"
}
```

3. Copiez le long jeton (`token`) reçu dans la réponse.
4. Pour toutes les requêtes de Julie, allez dans l'onglet **Authorization**, choisissez **Bearer Token**, et collez ce jeton.

---

### Étape 2 : Faire une proposition de troc (User B ➔ User A)

> ⚠️ Au moment de la proposition de troc, il faut bien renseigner l'ID de l'offre demandée **et** l'ID de ce qu'on propose en échange.

1. Créez une requête `POST` sur `http://localhost:8000/api/proposals`.
2. Vérifiez que le **Bearer Token de Julie** est bien configuré dans l'onglet **Authorization**.
3. Dans le **Body** (`raw`, `JSON`), liez les deux offres par leur ID :

```json
{
    "requested_id": 1,
    "offered_id": 3
}
```

- `"requested_id"` → ID de l'offre que **vous voulez obtenir** (l'objet appartenant à l'autre personne). Ici : la PS4 d'Alex (ID 1).
- `"offered_id"` → ID de l'offre que **vous proposez en échange** (votre propre objet). Ici : le VTT de Julie (ID 3).

> L'API vérifie que l'offre renseignée dans `offered_id` vous appartient bien. Si ce n'est pas le cas, elle retourne une erreur **403 Forbidden**.

4. Envoyez la requête. L'API répond **201 Created**. Notez l'ID de la proposition de troc renvoyé dans la réponse (ex. : `ID = 1`).

---

### Étape 3 : Répondre à la proposition

> ⚠️ Il faut se connecter avec le compte de **celui qui reçoit** l'offre, sinon la requête est refusée. Dans l'URL, remplacez `{id}` par l'ID du troc.

1. Faites un `POST /api/login` avec `alex@test.fr` / `password123` pour obtenir le token d'Alex.
2. Créez une nouvelle requête de méthode `PATCH`.
3. **URL dynamique :** remplacez `{id}` par l'ID du troc.  
   Exemple : `http://localhost:8000/api/proposals/1/respond`
4. Dans l'onglet **Authorization**, appliquez le **Bearer Token d'Alex**.
5. Dans le **Body** (`raw`, `JSON`), envoyez la décision :

```json
{
    "action": "accept"
}
```

---

### Étape 4 : La Modération Admin (Suppression destructive)

> ⚠️ Bien renseigner l'ID de l'offre à supprimer dans l'URL.

1. Générez le Token de l'Admin : faites un `POST /api/login` avec `admin@troc.fr` / `admin123`.
2. Créez une requête de méthode `DELETE`.
3. **URL dynamique :** remplacez `{id}` par l'ID de l'offre à supprimer.  
   Exemple : `http://localhost:8000/api/admin/offers/1`
4. Dans l'onglet **Authorization**, appliquez le **Bearer Token de l'Admin**.
5. Envoyez. L'API supprime proprement la ressource (**200 OK**).
