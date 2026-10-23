<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use nrv\adapters\config\ContainerConfig;
use nrv\adapters\spi\repositories\PDOStatistiqueRepository;

beforeEach(function () {
    $racineApp = dirname(__DIR__, 2);

    Dotenv::createImmutable($racineApp . '/config')->load();

    $this->pdo = ContainerConfig::build()->get('nrv.pdo');
    $this->pdo->beginTransaction();

    $this->pdo->exec(<<<'SQL'
        CREATE TEMP TABLE "soiree" (
            "id" integer PRIMARY KEY,
            "nom" varchar(100) NOT NULL,
            "date_heure_debut" timestamp NOT NULL,
            "capacite" integer NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "commande" (
            "id" integer PRIMARY KEY,
            "statut" varchar(15) NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "billet" (
            "id" integer PRIMARY KEY,
            "commande_id" integer NOT NULL,
            "soiree_id" integer NOT NULL,
            "prix" numeric(6,2) NOT NULL
        ) ON COMMIT DROP;
        SQL);

    $this->repository = new PDOStatistiqueRepository($this->pdo);
});

afterEach(function () {
    $pdo = $this->pdo ?? null;

    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
});

test('retourne une liste vide sans soirée', function () {
    expect($this->repository->trouverParSoiree())->toBe([]);
});

test('conserve une soirée sans billet avec des statistiques nulles', function () {
    $this->pdo->exec(<<<'SQL'
        INSERT INTO "soiree"
            ("id", "nom", "date_heure_debut", "capacite")
        VALUES
            (1, 'Sans vente', '2027-06-04 20:00:00', 8);
        SQL);

    $statistiques = $this->repository->trouverParSoiree();

    expect($statistiques)->toHaveCount(1);
    expect($statistiques[0]->getId())->toBe(1);
    expect($statistiques[0]->getPlacesVendues())->toBe(0);
    expect($statistiques[0]->getPlacesDisponibles())->toBe(8);
    expect($statistiques[0]->getRecette())->toBe(0);
});

test('compte uniquement les ventes payées et additionne les prix des billets', function () {
    $this->pdo->exec(<<<'SQL'
        INSERT INTO "soiree"
            ("id", "nom", "date_heure_debut", "capacite")
        VALUES
            (1, 'Rock', '2027-06-05 20:00:00', 3),
            (2, 'Blues', '2027-06-04 20:00:00', 4),
            (3, 'Sans vente payée', '2027-06-06 20:00:00', 8);

        INSERT INTO "commande" ("id", "statut") VALUES
            (10, 'payee'),
            (11, 'payee'),
            (12, 'en_attente'),
            (13, 'refusee');

        INSERT INTO "billet"
            ("id", "commande_id", "soiree_id", "prix")
        VALUES
            (1, 10, 1, 35.50),
            (2, 10, 1, 25.50),
            (3, 11, 1, 33.95),
            (4, 10, 2, 25.50),
            (5, 12, 1, 99.99),
            (6, 13, 1, 88.88),
            (7, 12, 3, 99.99);
        SQL);

    $statistiques = $this->repository->trouverParSoiree();

    expect(array_map(
        static fn($statistique) => $statistique->getId(),
        $statistiques
    ))->toBe([2, 1, 3]);

    $blues = $statistiques[0];

    expect($blues->getPlacesVendues())->toBe(1);
    expect($blues->getPlacesDisponibles())->toBe(3);
    expect($blues->getRecette())->toBe(2550);

    $rock = $statistiques[1];

    expect($rock->getPlacesVendues())->toBe(3);
    expect($rock->getPlacesDisponibles())->toBe(0);
    expect($rock->getRecette())->toBe(9495);

    $sansVente = $statistiques[2];

    expect($sansVente->getPlacesVendues())->toBe(0);
    expect($sansVente->getPlacesDisponibles())->toBe(8);
    expect($sansVente->getRecette())->toBe(0);
});