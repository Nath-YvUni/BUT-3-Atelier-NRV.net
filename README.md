# BUT-3-Atelier-NRV.net

## Réalisé par :

- Hugo ANTZORN
- Ilias BOUDOUAH
- Thibaut LOUYOT
- Nathan YVON

## Installation (après le clone)

Prérequis : Docker et Docker Compose, Node.js (pour compiler le SCSS du front).

1. **Créer les fichiers d'environnement** à partir des modèles `.dist` (ces fichiers ne sont pas versionnés) :

   ```sh
   cp nrv.env.dist nrv.env
   cp app/config/.env.dist app/config/.env
   ```

2. **Remplir les identifiants de la base** : `POSTGRES_USER` / `POSTGRES_PASSWORD` dans `nrv.env` doivent être identiques à `lib.username` / `lib.password` dans `app/config/.env`.

3. **Lancer les conteneurs** (les dépendances Composer sont installées automatiquement au démarrage de l'API) :

   ```sh
   docker compose up -d --build
   ```

4. **Compiler le SCSS du front** (le fichier `front/css/main.css` est généré et n'est pas versionné) :

   ```sh
   cd front
   npm install
   npm run build:css
   ```

5. **Créer les tables** dans Adminer (http://localhost:8880) :
   - Système : `PostgreSQL`, serveur : `nrv.db`, identifiants de `nrv.env`, base : `nrv`
   - **Importer** → choisir `app/sql/nrv.schema.sql` → **Exécuter**
   - Puis de la même façon `app/sql/nrv.data.sql` pour le jeu de données de test (mot de passe des comptes : `password`)

6. **Accéder aux services** :

   | Service    | URL / port                                 |
   | ---------- | ------------------------------------------ |
   | Front      | http://localhost:7081                      |
   | API        | http://localhost:7080                      |
   | Adminer    | http://localhost:8880 (serveur : `nrv.db`) |
   | PostgreSQL | `localhost:5532`                           |

## Comptes de test

Créés par `app/sql/nrv.data.sql`. Mot de passe commun à tous les comptes : `password`.

| Email                  | Rôle         | Données associées                                                            |
| ---------------------- | ------------ | ---------------------------------------------------------------------------- |
| `gestionnaire@nrv.net` | gestionnaire | —                                                                            |
| `alice@example.com`    | utilisateur  | Commande payée (`CMD-2026-0001`) avec un pass 3 jours                        |
| `bob@example.com`      | utilisateur  | Panier non validé (billets + pass 3 jours)                                   |
| `claire@example.com`   | utilisateur  | Panier validé, commande en attente (`CMD-2026-0002`) sur une soirée complète |

## API

| Méthode | Route                          | Description                      |
| ------- | ------------------------------ | -------------------------------- |
| GET     | `/api/spectacles`              | Lister et filtrer les spectacles |
| GET     | `/api/spectacles/{id}`         | Consulter un spectacle           |
| GET     | `/api/soirees`                 | Lister les soirées               |
| GET     | `/api/soirees/{id}`            | Consulter une soirée             |
| GET     | `/api/styles`                  | Lister les styles musicaux       |
| GET     | `/api/tarifs`                  | Consulter les tarifs             |
| GET     | `/api/lieux`                   | Lister les lieux                 |
| GET     | `/api/artistes`                | Lister les artistes              |
| POST    | `/api/utilisateurs`            | Créer un compte                  |
| POST    | `/api/auth/connexion`          | Se connecter                     |
| GET     | `/api/me`                      | Consulter son profil             |
| GET     | `/api/panier`                  | Consulter son panier             |
| POST    | `/api/panier/lignes`           | Ajouter des billets              |
| PATCH   | `/api/panier/lignes/{id}`      | Modifier une quantité            |
| DELETE  | `/api/panier/lignes/{id}`      | Supprimer une ligne              |
| POST    | `/api/panier/pass`             | Ajouter un pass                  |
| DELETE  | `/api/panier/pass/{id}`        | Supprimer un pass                |
| DELETE  | `/api/panier`                  | Vider son panier                 |
| POST    | `/api/commandes`               | Créer une commande               |
| GET     | `/api/commandes`               | Lister ses commandes             |
| GET     | `/api/commandes/{id}`          | Consulter une commande           |
| POST    | `/api/commandes/{id}/paiement` | Simuler un paiement              |
| GET     | `/api/commandes/{id}/billets`  | Récupérer les billets            |
| GET     | `/api/billets/{token}`         | Consulter un billet              |
| GET     | `/api/admin/statistiques`      | Consulter les ventes             |
| POST    | `/api/admin/soirees`           | Créer une soirée                 |
| POST    | `/api/admin/spectacles`        | Créer un spectacle               |

Filtres prévus pour les spectacles : `date`, `style_id`, `page`, `limit`.

Les paramètres, formats JSON et exemples sont disponibles dans la
[documentation complète de l’API](Documents/API/contrat-api.md).

## Commandes utiles

Recompiler automatiquement le SCSS à chaque modification (depuis `front/`) :

```sh
npm run watch:css
```

Lancer les tests :

```sh
docker compose exec api.nrv vendor/bin/pest
```

## Organisation

L'organisation et le suivi des différentes tâches sont réalisés à l'aide de Trello.
https://trello.com/invite/b/6ad613197f9e398967059399/ATTI266b2148012815678fe6e48761df17d841B535BD/sae-nrvnet-billetterie-festival
