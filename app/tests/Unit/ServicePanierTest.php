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

test('ajoute un pass avec la remise de 3 % au panier', function () {
    $this->paniers->method('trouverParUtilisateur')
        ->with(3)
        ->willReturnOnConsecutiveCalls(null, panierAvecPass());

    $this->paniers->expects($this->once())
        ->method('ajouterPass')
        ->with(3, 1, [3, 5, 6], PassTroisJours::TAUX_REMISE);

    $panier = $this->service->ajouterPass(3, [
        'soiree_ids' => [3, 5, 6],
        'tarif_id' => 1,
    ]);

    expect($panier['pass'][0]['montant_avant_remise'])->toBe('105.00');
    expect($panier['pass'][0]['montant_remise'])->toBe('3.15');
    expect($panier['pass'][0]['sous_total'])->toBe('101.85');
    expect($panier['nombre_billets'])->toBe(3);
});

test('refuse l\'ajout d\'un pass quand le panier est validé', function () {
    $this->paniers->method('trouverParUtilisateur')
        ->willReturn(panierAvecLigne(2, 2));

    $this->paniers->expects($this->never())->method('ajouterPass');

    expect(fn() => $this->service->ajouterPass(4, [
        'soiree_ids' => [3, 5, 6],
        'tarif_id' => 1,
    ]))->toThrow(PanierVerrouilleException::class);
});

test('refuse un pass avec des soirées ou un tarif inconnus', function () {
    $this->paniers->expects($this->never())->method('ajouterPass');

    $erreurs = erreursValidation(fn() => $this->service->ajouterPass(3, [
        'soiree_ids' => [3, 98, 99],
        'tarif_id' => 9,
    ]));

    expect($erreurs)->toHaveKeys(['soiree_ids', 'tarif_id']);
    expect($erreurs['soiree_ids'])->toContain('98, 99');
});

test('refuse un pass mal formé', function (array $donnees, string $champ) {
    $this->paniers->expects($this->never())->method('ajouterPass');

    $erreurs = erreursValidation(fn() => $this->service->ajouterPass(3, $donnees));

    expect($erreurs)->toHaveKey($champ);
})->with([
    'deux soirées' => [['soiree_ids' => [3, 5], 'tarif_id' => 1], 'soiree_ids'],
    'quatre soirées' => [['soiree_ids' => [2, 3, 5, 6], 'tarif_id' => 1], 'soiree_ids'],
    'soirée en double' => [['soiree_ids' => [3, 3, 5], 'tarif_id' => 1], 'soiree_ids'],
    'soirée en texte' => [['soiree_ids' => [3, '5', 6], 'tarif_id' => 1], 'soiree_ids'],
    'soirées absentes' => [['tarif_id' => 1], 'soiree_ids'],
    'tarif absent' => [['soiree_ids' => [3, 5, 6]], 'tarif_id'],
    'tarif nul' => [['soiree_ids' => [3, 5, 6], 'tarif_id' => 0], 'tarif_id'],
    'champ inconnu' => [['soiree_ids' => [3, 5, 6], 'tarif_id' => 1, 'taux_remise' => 50], 'taux_remise'],
]);
