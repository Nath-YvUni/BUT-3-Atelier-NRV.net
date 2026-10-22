<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use nrv\adapters\config\ContainerConfig;
use nrv\adapters\spi\repositories\PDOSpectacleRepository;

beforeEach(function () {
    $racineApp = dirname(__DIR__, 2);

    Dotenv::createImmutable($racineApp . '/config')->load();

    $this->pdo = ContainerConfig::build()->get('nrv.pdo');

    $this->pdo->beginTransaction();

    $this->pdo->exec(<<<'SQL'
        CREATE TEMP TABLE "soiree" (
            "id" integer PRIMARY KEY,
            "date_heure_debut" timestamp NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "spectacle" (
            "id" integer PRIMARY KEY,
            "soiree_id" integer NOT NULL,
            "titre" varchar(150) NOT NULL,
            "horaire_previsionnel" time NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "spectacle_image" (
            "id" integer PRIMARY KEY,
            "spectacle_id" integer NOT NULL,
            "url" varchar(255) NOT NULL
        ) ON COMMIT DROP;

                CREATE TEMP TABLE "style_musical" (
            "id" integer PRIMARY KEY,
            "libelle" varchar(50) NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "spectacle_style" (
            "spectacle_id" integer NOT NULL,
            "style_id" integer NOT NULL,
            PRIMARY KEY ("spectacle_id", "style_id")
        ) ON COMMIT DROP;
        SQL);

    $this->repository = new PDOSpectacleRepository($this->pdo);
});

afterEach(function () {
    $pdo = $this->pdo ?? null;

    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
});

test('retourne une liste vide sans spectacles en base', function () {
    expect($this->repository->getAll())->toBe([]);
});

test('classe les spectacles et sélectionne une seule image par spectacle', function () {
    $this->pdo->exec(<<<'SQL'
        INSERT INTO "soiree" ("id", "date_heure_debut") VALUES
            (1, '2027-06-04 20:00:00'),
            (2, '2027-06-05 20:30:00');

        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "horaire_previsionnel")
        VALUES
            (1, 2, 'Concert du lendemain', '20:30:00'),
            (2, 1, 'Concert tardif', '22:00:00'),
            (3, 1, 'Concert ouverture', '20:00:00');

        INSERT INTO "spectacle_image"
            ("id", "spectacle_id", "url")
        VALUES
            (20, 3, 'https://example.com/deuxieme.jpg'),
            (10, 3, 'https://example.com/premiere.jpg');
        SQL);

    $spectacles = $this->repository->getAll();

    $identifiants = array_map(
        static fn($spectacle) => $spectacle->getId(),
        $spectacles
    );

    expect($identifiants)->toBe([3, 2, 1]);

    expect($spectacles[0]->getTitre())
        ->toBe('Concert ouverture');

    expect($spectacles[0]->getSoireeId())->toBe(1);

    expect($spectacles[0]->getDate()->format('Y-m-d'))
        ->toBe('2027-06-04');

    expect($spectacles[0]->getHoraire()->format('H:i'))
        ->toBe('20:00');

    expect($spectacles[0]->getImage())
        ->toBe('https://example.com/premiere.jpg');

    expect($spectacles[1]->getImage())->toBeNull();
});

test('retourne aussi les spectacles sans style', function () {
    $this->pdo->exec(<<<'SQL'
        INSERT INTO "soiree" ("id", "date_heure_debut")
        VALUES (1, '2027-06-04 20:00:00');

        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "horaire_previsionnel")
        VALUES
            (1, 1, 'Concert rock', '20:00:00'),
            (2, 1, 'Concert sans style', '21:00:00');

        INSERT INTO "style_musical" ("id", "libelle")
        VALUES (1, 'Rock');

        INSERT INTO "spectacle_style" ("spectacle_id", "style_id")
        VALUES (1, 1);
        SQL);

    $spectacles = $this->repository->getAll();

    $identifiants = array_map(
        static fn($spectacle) => $spectacle->getId(),
        $spectacles
    );

    expect($identifiants)->toBe([1, 2]);
    expect($spectacles[0]->getStyles())->toBe(['Rock']);
    expect($spectacles[1]->getStyles())->toBe([]);
});