<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use nrv\adapters\config\ContainerConfig;
use nrv\adapters\spi\repositories\PDOTarifRepository;
use nrv\adapters\spi\repositories\SoireeRepository;
use nrv\application\usecases\ServiceSoiree;

function insererSoireesDeBase(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
        INSERT INTO "lieu" ("id", "nom") VALUES
            (1, 'Salle Poirel'),
            (2, 'Parc de la Pépinière');

        INSERT INTO "soiree"
            ("id", "festival_id", "lieu_id", "nom", "thematique", "date_heure_debut", "capacite")
        VALUES
            (1, 1, 1, 'Soirée Rock', 'Rock et métal', '2027-06-04 20:00:00', 300),
            (2, 1, 2, 'Soirée Jazz', 'Jazz et blues', '2027-06-05 20:30:00', 150);

        INSERT INTO "tarif" ("id", "categorie", "prix") VALUES
            (7, 'normal', 35.50),
            (12, 'reduit', 25.50);
        SQL);
}

function contientTexte(mixed $valeur, string $texte): bool
{
    return str_contains(json_encode($valeur, JSON_UNESCAPED_UNICODE), $texte);
}

beforeEach(function () {
    $racineApp = dirname(__DIR__, 2);

    Dotenv::createImmutable($racineApp . '/config')->load();

    $this->pdo = ContainerConfig::build()->get('nrv.pdo');

    $this->pdo->beginTransaction();

    $this->pdo->exec(<<<'SQL'
        CREATE TEMP TABLE "lieu" (
            "id" integer PRIMARY KEY,
            "nom" varchar(100) NOT NULL
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

        CREATE TEMP TABLE "tarif" (
            "id" integer PRIMARY KEY,
            "categorie" varchar(10) NOT NULL,
            "prix" numeric(6,2) NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "spectacle" (
            "id" integer PRIMARY KEY,
            "soiree_id" integer NOT NULL,
            "titre" varchar(150) NOT NULL,
            "description" text NOT NULL,
            "url_video" varchar(255) NOT NULL,
            "horaire_previsionnel" time NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "artiste" (
            "id" integer PRIMARY KEY,
            "nom" varchar(100) NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "spectacle_artiste" (
            "spectacle_id" integer NOT NULL,
            "artiste_id" integer NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "style_musical" (
            "id" integer PRIMARY KEY,
            "libelle" varchar(50) NOT NULL
        ) ON COMMIT DROP;

        CREATE TEMP TABLE "spectacle_style" (
            "spectacle_id" integer NOT NULL,
            "style_id" integer NOT NULL
        ) ON COMMIT DROP;
        SQL);

    $this->repository = new SoireeRepository($this->pdo);
});

afterEach(function () {
    $pdo = $this->pdo ?? null;

    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
});

test('retourne null quand la soirée n\'existe pas', function () {
    insererSoireesDeBase($this->pdo);

    expect($this->repository->findById(999))->toBeNull();
});

test('retourne les informations de la soirée avec son lieu', function () {
    insererSoireesDeBase($this->pdo);

    $soiree = $this->repository->findById(1);

    expect($soiree->getId())->toBe(1);
    expect($soiree->getFestivalId())->toBe(1);
    expect($soiree->getLieuId())->toBe(1);
    expect($soiree->getNom())->toBe('Soirée Rock');
    expect($soiree->getThematique())->toBe('Rock et métal');
    expect($soiree->getDate()->format('Y-m-d H:i'))->toBe('2027-06-04 20:00');
    expect($soiree->getCapacite())->toBe(300);
    expect($soiree->getNomLieu())->toBe('Salle Poirel');
});

test('retourne le lieu de la bonne soirée', function () {
    insererSoireesDeBase($this->pdo);

    expect($this->repository->findById(2)->getNomLieu())
        ->toBe('Parc de la Pépinière');
});

test('retourne une liste de spectacles vide pour une soirée sans spectacle', function () {
    insererSoireesDeBase($this->pdo);

    expect($this->repository->findById(1)->getListe())->toBe([]);
});

test('charge pour chaque spectacle le titre, la description, la vidéo, les artistes et le style', function () {
    insererSoireesDeBase($this->pdo);

    $this->pdo->exec(<<<'SQL'
        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "description", "url_video", "horaire_previsionnel")
        VALUES
            (1, 1, 'Pistons & Chrome', 'Un concert de rock.', 'https://example.com/pistons.mp4', '20:00:00');

        INSERT INTO "artiste" ("id", "nom") VALUES (1, 'Alice'), (2, 'Bob');
        INSERT INTO "spectacle_artiste" ("spectacle_id", "artiste_id") VALUES (1, 1), (1, 2);

        INSERT INTO "style_musical" ("id", "libelle") VALUES (1, 'Rock'), (2, 'Métal');
        INSERT INTO "spectacle_style" ("spectacle_id", "style_id") VALUES (1, 1), (1, 2);
        SQL);

    $liste = $this->repository->findById(1)->getListe();

    expect($liste)->toHaveCount(1);
    expect($liste[0]['titre'])->toBe('Pistons & Chrome');
    expect($liste[0]['description'])->toBe('Un concert de rock.');
    expect($liste[0]['url_video'])->toBe('https://example.com/pistons.mp4');

    expect(contientTexte($liste[0]['artistes'], 'Alice'))->toBeTrue();
    expect(contientTexte($liste[0]['artistes'], 'Bob'))->toBeTrue();

    expect(contientTexte($liste[0]['styles'], 'Rock'))->toBeTrue();
    expect(contientTexte($liste[0]['styles'], 'Métal'))->toBeTrue();
});

test('ne retourne que les spectacles de la soirée demandée', function () {
    insererSoireesDeBase($this->pdo);

    $this->pdo->exec(<<<'SQL'
        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "description", "url_video", "horaire_previsionnel")
        VALUES
            (1, 1, 'Concert rock', 'Rock.', 'https://example.com/1.mp4', '20:00:00'),
            (2, 2, 'Concert jazz', 'Jazz.', 'https://example.com/2.mp4', '20:30:00');
        SQL);

    $liste = $this->repository->findById(1)->getListe();

    expect($liste)->toHaveCount(1);
    expect($liste[0]['titre'])->toBe('Concert rock');
});

test('ne duplique pas un spectacle qui a plusieurs artistes et plusieurs styles', function () {
    insererSoireesDeBase($this->pdo);

    $this->pdo->exec(<<<'SQL'
        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "description", "url_video", "horaire_previsionnel")
        VALUES
            (1, 1, 'Pistons & Chrome', 'Rock.', 'https://example.com/1.mp4', '20:00:00');

        INSERT INTO "artiste" ("id", "nom") VALUES (1, 'Alice'), (2, 'Bob'), (3, 'Chloé');
        INSERT INTO "spectacle_artiste" ("spectacle_id", "artiste_id") VALUES (1, 1), (1, 2), (1, 3);

        INSERT INTO "style_musical" ("id", "libelle") VALUES (1, 'Rock'), (2, 'Métal');
        INSERT INTO "spectacle_style" ("spectacle_id", "style_id") VALUES (1, 1), (1, 2);
        SQL);

    expect($this->repository->findById(1)->getListe())->toHaveCount(1);
});

test('trie les spectacles de la soirée par horaire prévisionnel', function () {
    insererSoireesDeBase($this->pdo);

    // Les identifiants sont volontairement dans l'ordre inverse des horaires.
    $this->pdo->exec(<<<'SQL'
        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "description", "url_video", "horaire_previsionnel")
        VALUES
            (1, 1, 'Concert tardif', 'Tard.', 'https://example.com/1.mp4', '22:00:00'),
            (2, 1, 'Concert du milieu', 'Milieu.', 'https://example.com/2.mp4', '21:00:00'),
            (3, 1, 'Concert ouverture', 'Tôt.', 'https://example.com/3.mp4', '20:00:00');
        SQL);

    $titres = array_map(
        static fn(array $spectacle) => $spectacle['titre'],
        $this->repository->findById(1)->getListe()
    );

    expect($titres)->toBe([
        'Concert ouverture',
        'Concert du milieu',
        'Concert tardif',
    ]);
});

test('liste chaque artiste une seule fois pour la soirée', function () {
    insererSoireesDeBase($this->pdo);

    // Alice joue dans deux spectacles de la même soirée.
    $this->pdo->exec(<<<'SQL'
        INSERT INTO "spectacle"
            ("id", "soiree_id", "titre", "description", "url_video", "horaire_previsionnel")
        VALUES
            (1, 1, 'Concert 1', 'Un.', 'https://example.com/1.mp4', '20:00:00'),
            (2, 1, 'Concert 2', 'Deux.', 'https://example.com/2.mp4', '21:00:00');

        INSERT INTO "artiste" ("id", "nom") VALUES (1, 'Alice'), (2, 'Bob');
        INSERT INTO "spectacle_artiste" ("spectacle_id", "artiste_id") VALUES (1, 1), (2, 1), (2, 2);
        SQL);

    $noms = array_map(
        static fn(array $artiste) => $artiste['nom'],
        $this->repository->findById(1)->getArtistes()
    );

    sort($noms);

    expect($noms)->toBe(['Alice', 'Bob']);
});

test('le détail de soirée conserve les centimes et retourne tous les tarifs', function () {
    insererSoireesDeBase($this->pdo);

    $service = new ServiceSoiree(
        $this->repository,
        new PDOTarifRepository($this->pdo)
    );

    $detail = $service->getDetails(1)->toArray();

    expect($detail['tarifs'])->toBe([
        ['id' => 7, 'categorie' => 'normal', 'prix' => '35.50'],
        ['id' => 12, 'categorie' => 'reduit', 'prix' => '25.50'],
    ]);

    expect($detail)->not->toHaveKey('tarif');
});