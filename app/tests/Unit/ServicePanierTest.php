<?php
declare(strict_types=1);

use nrv\application\exceptions\PanierVerrouilleException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\spi\PanierRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\TarifRepositoryInterface;
use nrv\application\usecases\ServicePanier;
use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;
use nrv\domain\entities\PassTroisJours;

function panierAvecLigne(int $quantite, ?int $commandeEnAttenteId = null): Panier
{
    return new Panier(
        1,
        [
            new LignePanier(
                1,
                new SoireeResume(2, 'Blues Night', new DateTimeImmutable('2027-06-05 20:30:00')),
                new Tarif(1, 'normal', 3500),
                $quantite
            ),
        ],
        [],
        $commandeEnAttenteId
    );
}

function panierAvecPass(): Panier
{
    return new Panier(
        1,
        [],
        [
            new PassTroisJours(
                4,
                new Tarif(1, 'normal', 3500),
                300,
                [
                    new SoireeResume(3, 'Metal Storm', new DateTimeImmutable('2027-06-08 19:30:00')),
                    new SoireeResume(5, 'Reggae Vibration', new DateTimeImmutable('2027-06-12 20:00:00')),
                    new SoireeResume(6, 'Punk Attitude', new DateTimeImmutable('2027-06-15 20:30:00')),
                ]
            ),
        ],
        null
    );
}

function erreursValidation(callable $appel): array
{
    try {
        $appel();
    } catch (ValidationException $e) {
        return $e->getErrors();
    }

    throw new RuntimeException('Une ValidationException était attendue.');
}

beforeEach(function () {
    $this->paniers = $this->createMock(PanierRepositoryInterface::class);
    $this->soirees = $this->createMock(SoireeRepositoryInterface::class);
    $this->tarifs = $this->createMock(TarifRepositoryInterface::class);

    $this->soirees->method('existe')->willReturnCallback(fn(int $id) => in_array($id, [2, 3, 5, 6], true));
    $this->tarifs->method('trouverParId')->willReturnCallback(
        fn(int $id) => $id === 1 ? new Tarif(1, 'normal', 3500) : null
    );

    $this->service = new ServicePanier($this->paniers, $this->soirees, $this->tarifs);
});

test('crée le panier et la ligne au premier ajout', function () {
    $this->paniers->method('trouverParUtilisateur')
        ->with(3)
        ->willReturnOnConsecutiveCalls(null, panierAvecLigne(2));

    $this->paniers->expects($this->once())
        ->method('ajouterBillets')
        ->with(3, 2, 1, 2);

    $resultat = $this->service->ajouterBillets(3, [
        'soiree_id' => 2,
        'tarif_id' => 1,
        'quantite' => 2,
    ]);

    expect($resultat['cree'])->toBeTrue();
    expect($resultat['panier']['lignes'][0]['quantite'])->toBe(2);
    expect($resultat['panier']['montant_total'])->toBe('70.00');
});

test('augmente la quantité quand la ligne existe déjà', function () {
    $this->paniers->method('trouverParUtilisateur')
        ->willReturnOnConsecutiveCalls(panierAvecLigne(2), panierAvecLigne(4));

    $this->paniers->expects($this->once())
        ->method('ajouterBillets')
        ->with(3, 2, 1, 2);

    $resultat = $this->service->ajouterBillets(3, [
        'soiree_id' => 2,
        'tarif_id' => 1,
        'quantite' => 2,
    ]);

    expect($resultat['cree'])->toBeFalse();
    expect($resultat['panier']['lignes'][0]['sous_total'])->toBe('140.00');
});

test('refuse l\'ajout quand le panier est validé', function () {
    $this->paniers->method('trouverParUtilisateur')
        ->willReturn(panierAvecLigne(2, 2));

    $this->paniers->expects($this->never())->method('ajouterBillets');

    expect(fn() => $this->service->ajouterBillets(4, [
        'soiree_id' => 2,
        'tarif_id' => 1,
        'quantite' => 1,
    ]))->toThrow(PanierVerrouilleException::class);
});

test('refuse une soirée ou un tarif inconnu', function () {
    $this->paniers->expects($this->never())->method('ajouterBillets');

    $erreurs = erreursValidation(fn() => $this->service->ajouterBillets(3, [
        'soiree_id' => 999,
        'tarif_id' => 9,
        'quantite' => 1,
    ]));

    expect($erreurs)->toHaveKeys(['soiree_id', 'tarif_id']);
});

test('refuse des données mal formées', function (array $donnees, string $champ) {
    $this->paniers->expects($this->never())->method('ajouterBillets');

    $erreurs = erreursValidation(fn() => $this->service->ajouterBillets(3, $donnees));

    expect($erreurs)->toHaveKey($champ);
})->with([
            'quantité nulle' => [['soiree_id' => 2, 'tarif_id' => 1, 'quantite' => 0], 'quantite'],
            'quantité négative' => [['soiree_id' => 2, 'tarif_id' => 1, 'quantite' => -3], 'quantite'],
            'quantité en texte' => [['soiree_id' => 2, 'tarif_id' => 1, 'quantite' => '2'], 'quantite'],
            'quantité décimale' => [['soiree_id' => 2, 'tarif_id' => 1, 'quantite' => 1.5], 'quantite'],
            'soirée absente' => [['tarif_id' => 1, 'quantite' => 1], 'soiree_id'],
            'tarif absent' => [['soiree_id' => 2, 'quantite' => 1], 'tarif_id'],
            'champ inconnu' => [['soiree_id' => 2, 'tarif_id' => 1, 'quantite' => 1, 'prix' => 1], 'prix'],
        ]);


test('renvoie un panier vide quand l\'utilisateur n\'en a pas encore', function () {
    $this->paniers->method('trouverParUtilisateur')->with(3)->willReturn(null);

    $panier = $this->service->consulterPanier(3);

    expect($panier['id'])->toBeNull();
    expect($panier['lignes'])->toBe([]);
    expect($panier['pass'])->toBe([]);
    expect($panier['nombre_billets'])->toBe(0);
    expect($panier['montant_total'])->toBe('0.00');
    expect($panier['modifiable'])->toBeTrue();
});

test('renvoie le contenu et le montant total du panier', function () {
    $this->paniers->method('trouverParUtilisateur')->with(3)->willReturn(panierAvecLigne(2));

    $panier = $this->service->consulterPanier(3);

    expect($panier['lignes'])->toHaveCount(1);
    expect($panier['lignes'][0]['sous_total'])->toBe('70.00');
    expect($panier['nombre_billets'])->toBe(2);
    expect($panier['montant_total'])->toBe('70.00');
    expect($panier['modifiable'])->toBeTrue();
});

test('inclut les pass avec leur remise dans le montant total', function () {
    $soirees = [
        new SoireeResume(1, 'Rock\'n\'Nancy', new DateTimeImmutable('2027-06-04 20:00:00')),
        new SoireeResume(3, 'Metal Storm', new DateTimeImmutable('2027-06-08 19:30:00')),
        new SoireeResume(7, 'Rock Legends', new DateTimeImmutable('2027-06-17 19:00:00')),
    ];

    $this->paniers->method('trouverParUtilisateur')->willReturn(new Panier(
        1,
        [],
        [new PassTroisJours(1, new Tarif(1, 'normal', 3500), 300, $soirees)],
        null
    ));

    $panier = $this->service->consulterPanier(3);

    expect($panier['pass'][0]['montant_avant_remise'])->toBe('105.00');
    expect($panier['pass'][0]['montant_remise'])->toBe('3.15');
    expect($panier['pass'][0]['sous_total'])->toBe('101.85');
    expect($panier['nombre_billets'])->toBe(3);
    expect($panier['montant_total'])->toBe('101.85');
});

test('indique qu\'un panier lié à une commande en attente n\'est plus modifiable', function () {
    $this->paniers->method('trouverParUtilisateur')->willReturn(panierAvecLigne(2, 2));

    $panier = $this->service->consulterPanier(4);

    expect($panier['modifiable'])->toBeFalse();
    expect($panier['commande_en_attente_id'])->toBe(2);
});
