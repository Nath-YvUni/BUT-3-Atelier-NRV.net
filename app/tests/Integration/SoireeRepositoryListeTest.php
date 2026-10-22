<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use nrv\adapters\config\ContainerConfig;
use nrv\adapters\spi\repositories\SoireeRepository;

beforeEach(function () {
    $racineApp = dirname(__DIR__, 2);

    Dotenv::createImmutable($racineApp . '/config')->load();

    $this->pdo = ContainerConfig::build()->get('nrv.pdo');

    $this->pdo->beginTransaction();

    $this->pdo->exec(<<<'SQL'
        CREATE TEMP TABLE "lieu" (
            "id" integer PRIMARY KEY,
            "nom" varchar(100) NOT NULL,
            "adresse" varchar(255) NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "soiree" (
            "id" integer PRIMARY KEY,
            "festival_id" integer NOT NULL,
            "lieu_id" integer NOT NULL,
            "nom" varchar(100) NOT NULL,
            "thematique" varchar(255) NOT NULL,
            "date_heure_debut" timestamp NOT NULL,
            "capacite" integer NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "billet" (
            "id" integer PRIMARY KEY,
            "soiree_id" integer NOT NULL
        ) ON COMMIT DROP;

        INSERT INTO "lieu" ("id", "nom", "adresse") VALUES
            (1, 'L''Autre Canal', '45 boulevard d''Austrasie, 54000 Nancy'),
            (2, 'Zénith de Nancy', 'Rue du Zénith, 54320 Maxéville');

        INSERT INTO "soiree"
            ("id", "festival_id", "lieu_id", "nom", "thematique", "date_heure_debut", "capacite")
        VALUES
            (3, 1, 2, 'Metal Storm', 'Soirée métal', '2027-06-08 19:30:00', 5000),
            (2, 1, 1, 'Blues Night', 'Soirée blues', '2027-06-05 20:30:00', 800),
            (4, 1, 1, 'Chansons d''ici', 'Soirée chanson', '2027-06-05 23:59:59', 2);

        INSERT INTO "billet" ("id", "soiree_id") VALUES
            (1, 2), (2, 4), (3, 4);
        SQL);

    $this->repository = new SoireeRepository($this->pdo);
});

afterEach(function () {
    $pdo = $this->pdo ?? null;

    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
});

test('retourne toutes les soirées triées par date avec leur lieu', function () {
    $soirees = $this->repository->findByAll();

    expect(array_map(fn($soiree) => $soiree->getId(), $soirees))->toBe([2, 4, 3]);
    expect($soirees[0]->getLieu()->getAdresse())->toBe('45 boulevard d\'Austrasie, 54000 Nancy');
    expect($soirees[2]->getLieu()->getNom())->toBe('Zénith de Nancy');
    expect($soirees[0]->getDateHeureDebut()->format('Y-m-d H:i'))->toBe('2027-06-05 20:30');
});

test('compte les billets vendus pour chaque soirée', function () {
    $soirees = $this->repository->findByAll();

    expect($soirees[0]->getPlacesVendues())->toBe(1);
    expect($soirees[1]->getPlacesVendues())->toBe(2);
    expect($soirees[1]->estComplete())->toBeTrue();
    expect($soirees[2]->getPlacesVendues())->toBe(0);
});

test('filtre les soirées sur toute la journée demandée', function () {
    $soirees = $this->repository->findByAll(new DateTimeImmutable('2027-06-05'));

    expect(array_map(fn($soiree) => $soiree->getId(), $soirees))->toBe([2, 4]);
});

test('retourne une liste vide quand aucune soirée n\'a lieu ce jour-là', function () {
    expect($this->repository->findByAll(new DateTimeImmutable('2027-06-06')))->toBe([]);
});
