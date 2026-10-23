<?php
declare(strict_types=1);

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\StatistiqueRepositoryInterface;
use nrv\application\usecases\ServiceStatistique;
use nrv\domain\entities\StatistiqueSoiree;

test('retourne les statistiques des soirées et les totaux', function () {
    $repository = $this->createMock(
        StatistiqueRepositoryInterface::class
    );

    $repository->expects($this->once())
        ->method('trouverParSoiree')
        ->willReturn([
            new StatistiqueSoiree(7, 'Rock', 3, 3, 9495),
            new StatistiqueSoiree(12, 'Blues', 4, 1, 2550),
            new StatistiqueSoiree(19, 'Sans vente', 8, 0, 0),
        ]);

    $service = new ServiceStatistique($repository);

    expect($service->getStatistiques())->toBe([
        'soirees' => [
            [
                'id' => 7,
                'nom' => 'Rock',
                'capacite' => 3,
                'places_vendues' => 3,
                'places_disponibles' => 0,
                'recette' => '94.95',
            ],
            [
                'id' => 12,
                'nom' => 'Blues',
                'capacite' => 4,
                'places_vendues' => 1,
                'places_disponibles' => 3,
                'recette' => '25.50',
            ],
            [
                'id' => 19,
                'nom' => 'Sans vente',
                'capacite' => 8,
                'places_vendues' => 0,
                'places_disponibles' => 8,
                'recette' => '0.00',
            ],
        ],
        'total_billets_vendus' => 4,
        'recette_totale' => '120.45',
        'devise' => 'EUR',
    ]);
});

test('retourne des totaux nuls sans soirée', function () {
    $repository = $this->createMock(
        StatistiqueRepositoryInterface::class
    );

    $repository->expects($this->once())
        ->method('trouverParSoiree')
        ->willReturn([]);

    $service = new ServiceStatistique($repository);

    expect($service->getStatistiques())->toBe([
        'soirees' => [],
        'total_billets_vendus' => 0,
        'recette_totale' => '0.00',
        'devise' => 'EUR',
    ]);
});

test('propage une erreur du repository', function () {
    $repository = $this->createMock(
        StatistiqueRepositoryInterface::class
    );

    $repository->expects($this->once())
        ->method('trouverParSoiree')
        ->willThrowException(
            new RepositoryInternalException('Base indisponible.')
        );

    $service = new ServiceStatistique($repository);

    expect(fn() => $service->getStatistiques())
        ->toThrow(RepositoryInternalException::class);
});