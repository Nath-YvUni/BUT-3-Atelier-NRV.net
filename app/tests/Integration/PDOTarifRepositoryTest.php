<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use nrv\adapters\config\ContainerConfig;
use nrv\adapters\spi\repositories\PDOTarifRepository;

beforeEach(function () {
    $racineApp = dirname(__DIR__, 2);

    Dotenv::createImmutable($racineApp . '/config')->load();

    $this->pdo = ContainerConfig::build()->get('nrv.pdo');

    $this->pdo->beginTransaction();

    $this->pdo->exec(<<<'SQL'
        CREATE TEMP TABLE "tarif" (
            "id" integer PRIMARY KEY,
            "categorie" varchar(10) NOT NULL,
            "prix" numeric(6,2) NOT NULL
        ) ON COMMIT DROP;

        INSERT INTO "tarif" ("id", "categorie", "prix") VALUES
            (2, 'reduit', 25.50),
            (1, 'normal', 35.00);
        SQL);

    $this->repository = new PDOTarifRepository($this->pdo);
});

afterEach(function () {
    $pdo = $this->pdo ?? null;

    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
});

test('retourne tous les tarifs triés par identifiant', function () {
    $tarifs = $this->repository->trouverTous();

    expect($tarifs)->toHaveCount(2);
    expect($tarifs[0]->getId())->toBe(1);
    expect($tarifs[0]->getCategorie())->toBe('normal');
    expect($tarifs[0]->getPrix())->toBe(3500);
    expect($tarifs[1]->getPrix())->toBe(2550);
});

test('retourne un tarif par son identifiant', function () {
    expect($this->repository->trouverParId(2)->getCategorie())->toBe('reduit');
});

test('retourne null pour un tarif inconnu', function () {
    expect($this->repository->trouverParId(99))->toBeNull();
});
