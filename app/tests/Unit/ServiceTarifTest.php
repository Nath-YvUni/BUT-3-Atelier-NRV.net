<?php
declare(strict_types=1);

use nrv\application\ports\spi\TarifRepositoryInterface;
use nrv\application\usecases\ServiceTarif;
use nrv\domain\entities\Tarif;

test('retourne les tarifs avec un prix à deux décimales', function () {
    $repository = $this->createMock(TarifRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('trouverTous')
        ->willReturn([
            new Tarif(1, 'normal', 3500),
            new Tarif(2, 'reduit', 2550),
        ]);

    expect((new ServiceTarif($repository))->getTarifs())->toBe([
        ['id' => 1, 'categorie' => 'normal', 'prix' => '35.00'],
        ['id' => 2, 'categorie' => 'reduit', 'prix' => '25.50'],
    ]);
});

test('retourne une liste vide quand aucun tarif n\'existe', function () {
    $repository = $this->createMock(TarifRepositoryInterface::class);

    $repository->method('trouverTous')->willReturn([]);

    expect((new ServiceTarif($repository))->getTarifs())->toBe([]);
});
