# BUT-3-Atelier-NRV.net
## Réalisé par :
  - Hugo ANTZORN
  - Ilias BOUDOUAH 
  - Thibaut LOUYOT 
  - Nathan YVON

## Installation (après le clone)

Prérequis : Docker et Docker Compose.

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

4. **Créer les tables** dans Adminer (http://localhost:8880) :
   - Système : `PostgreSQL`, serveur : `nrv.db`, identifiants de `nrv.env`, base : `nrv`
   - **Importer** → choisir `app/sql/nrv.schema.sql` → **Exécuter**
   - Puis de la même façon `app/sql/nrv.data.sql` pour le jeu de données de test (mot de passe des comptes : `password`)

5. **Accéder aux services** :

   | Service | URL / port |
   |---|---|
   | API | http://localhost:7080 |
   | Adminer | http://localhost:8880 (serveur : `nrv.db`) |
   | PostgreSQL | `localhost:5532` |

Lancer les tests :

```sh
docker compose exec api.nrv vendor/bin/pest
```

## Organisation

L'organisation et le suivi des différentes tâches sont réalisés à l'aide de Trello.
https://trello.com/invite/b/6ad613197f9e398967059399/ATTI266b2148012815678fe6e48761df17d841B535BD/sae-nrvnet-billetterie-festival
