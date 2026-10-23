<?php
declare(strict_types=1);

use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;

function creerPassDeTest(int $prix, int $tauxRemise = 300): PassTroisJours
{
    return new PassTroisJours(
        1,
        new Tarif(2, 'reduit', $prix),
        $tauxRemise,
        [
            new SoireeResume(3, 'Metal Storm', new DateTimeImmutable('2027-06-08 19:30:00')),
            new SoireeResume(5, 'Reggae Vibration', new DateTimeImmutable('2027-06-12 20:00:00')),
            new SoireeResume(6, 'Punk Attitude', new DateTimeImmutable('2027-06-15 20:30:00')),
        ]
    );
}

test('répartit le prix du pass entre les trois billets', function () {
    $pass = creerPassDeTest(2500);

    expect($pass->prixBillets())->toBe([2425, 2425, 2425]);
    expect(array_sum($pass->prixBillets()))->toBe($pass->sousTotal());
});

test('répartit les centimes restants entre les billets', function () {
    $pass = creerPassDeTest(2500, 101);

    expect($pass->sousTotal())->toBe(7424);
    expect($pass->prixBillets())->toBe([2475, 2475, 2474]);
    expect(array_sum($pass->prixBillets()))->toBe($pass->sousTotal());
});

test('le nombre de prix correspond au nombre de billets', function () {
    $pass = creerPassDeTest(3333);

    expect($pass->prixBillets())->toHaveCount(3);
    expect(array_sum($pass->prixBillets()))->toBe($pass->sousTotal());
});
