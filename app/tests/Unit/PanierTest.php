<?php
declare(strict_types=1);

use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;

function soireeResumeDeTest(int $id): SoireeResume
{
    return new SoireeResume($id, 'Soirée ' . $id, new DateTimeImmutable('2027-06-05 20:30:00'));
}

function passDeTest(int $prix, int $tauxRemise = 300): PassTroisJours
{
    return new PassTroisJours(
        1,
        new Tarif(2, 'reduit', $prix),
        $tauxRemise,
        [soireeResumeDeTest(3), soireeResumeDeTest(5), soireeResumeDeTest(6)]
    );
}

test('le sous-total d\'une ligne vaut prix × quantité', function () {
    $ligne = new LignePanier(1, soireeResumeDeTest(2), new Tarif(1, 'normal', 3500), 4);

    expect($ligne->sousTotal())->toBe(14000);
});

test('le pass 3 jours applique 3 % de remise sur les trois billets', function () {
    $pass = passDeTest(2500);

    expect($pass->nombreBillets())->toBe(3);
    expect($pass->montantAvantRemise())->toBe(7500);
    expect($pass->montantRemise())->toBe(225);
    expect($pass->sousTotal())->toBe(7275);
});

test('la remise du pass est arrondie au centime le plus proche', function () {
    expect(passDeTest(3333)->montantRemise())->toBe(300);
});

test('le panier totalise lignes et pass', function () {
    $panier = new Panier(
        1,
        [
            new LignePanier(1, soireeResumeDeTest(2), new Tarif(1, 'normal', 3500), 2),
            new LignePanier(2, soireeResumeDeTest(5), new Tarif(2, 'reduit', 2500), 1),
        ],
        [passDeTest(2500)],
        null
    );

    expect($panier->nombreBillets())->toBe(6);
    expect($panier->montantTotal())->toBe(16775);
});

test('un panier avec une commande en attente n\'est plus modifiable', function () {
    expect((new Panier(1, [], [], null))->estModifiable())->toBeTrue();
    expect((new Panier(1, [], [], 2))->estModifiable())->toBeFalse();
});

test('retrouve une ligne par soirée et tarif', function () {
    $panier = new Panier(
        1,
        [new LignePanier(1, soireeResumeDeTest(2), new Tarif(1, 'normal', 3500), 2)],
        [],
        null
    );

    expect($panier->contientLigne(2, 1))->toBeTrue();
    expect($panier->contientLigne(2, 2))->toBeFalse();
    expect($panier->contientLigne(3, 1))->toBeFalse();
});


test('billetsParSoiree additionne les lignes et les pass par soirée', function () {
    $panier = new Panier(
        1,
        [
            new LignePanier(
                1,
                soireeResumeDeTest(2),
                new Tarif(1, 'normal', 3500),
                2
            ),
            new LignePanier(
                2,
                soireeResumeDeTest(3),
                new Tarif(2, 'reduit', 2500),
                3
            ),
        ],
        [passDeTest(2500)],
        null
    );

    expect($panier->billetsParSoiree())->toBe([
        2 => 2,
        3 => 4,
        5 => 1,
        6 => 1,
    ]);
});

