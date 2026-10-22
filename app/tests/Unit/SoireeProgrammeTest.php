<?php
declare(strict_types=1);

use nrv\domain\entities\Lieu;
use nrv\domain\entities\SoireeProgramme;

function soireeProgrammeDeTest(int $capacite, int $placesVendues): SoireeProgramme
{
    return new SoireeProgramme(
        2,
        'Blues Night',
        'Soirée blues',
        new DateTimeImmutable('2027-06-05 20:30:00'),
        new Lieu(1, 'L\'Autre Canal', '45 boulevard d\'Austrasie, 54000 Nancy'),
        $capacite,
        $placesVendues
    );
}

test('les places disponibles valent la capacité moins les places vendues', function () {
    $soiree = soireeProgrammeDeTest(800, 1);

    expect($soiree->placesDisponibles())->toBe(799);
    expect($soiree->estComplete())->toBeFalse();
});

test('une soirée dont toutes les places sont vendues est complète', function () {
    $soiree = soireeProgrammeDeTest(4, 4);

    expect($soiree->placesDisponibles())->toBe(0);
    expect($soiree->estComplete())->toBeTrue();
});

test('les places disponibles ne deviennent jamais négatives', function () {
    expect(soireeProgrammeDeTest(4, 6)->placesDisponibles())->toBe(0);
});
