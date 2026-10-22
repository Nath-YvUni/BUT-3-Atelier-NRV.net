<?php
declare(strict_types=1);

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\usecases\ServiceSpectacle;
use nrv\domain\entities\Spectacle;

test('retourne une liste vide quand aucun spectacle existe', function () {
    $repository = $this->createMock(SpectacleRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('getAll')
        ->willReturn([]);

    $service = new ServiceSpectacle($repository);

    expect($service->getSpectacles())->toBe([]);
});

test('retourne les informations des spectacles pour le programme', function () {
    $spectacles = [
        new Spectacle(
            1,
            1,
            'Pistons & Chrome',
            new DateTimeImmutable('2027-06-04'),
            new DateTimeImmutable('20:00:00'),
            'https://example.com/pistons.jpg',
            [
                ['id' => 1, 'libelle' => 'Classic rock'],
            ]
        ),
        new Spectacle(
            4,
            2,
            'Mississippi to Moselle',
            new DateTimeImmutable('2027-06-05'),
            new DateTimeImmutable('20:30:00'),
            null,
            []
        ),
    ];

    $repository = $this->createMock(SpectacleRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('getAll')
        ->willReturn($spectacles);

    $service = new ServiceSpectacle($repository);

    expect($service->getSpectacles())->toBe([
        [
            'id' => 1,
            'soiree_id' => 1,
            'titre' => 'Pistons & Chrome',
            'date' => '2027-06-04',
            'horaire' => '20:00',
            'image' => 'https://example.com/pistons.jpg',
            'styles' => [
                ['id' => 1, 'libelle' => 'Classic rock'],
            ],
        ],
        [
            'id' => 4,
            'soiree_id' => 2,
            'titre' => 'Mississippi to Moselle',
            'date' => '2027-06-05',
            'horaire' => '20:30',
            'image' => null,
            'styles' => [],
        ],
    ]);
});

test('ne masque pas une erreur du repository par une liste vide', function () {
    $repository = $this->createMock(SpectacleRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('getAll')
        ->willThrowException(
            new RepositoryInternalException('Base indisponible')
        );

    $service = new ServiceSpectacle($repository);

    expect(fn() => $service->getSpectacles())
        ->toThrow(RepositoryInternalException::class);
});

test('transmet les filtres validés au repository', function () {
    $repository = $this->createMock(SpectacleRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('getAll')
        ->with(
            $this->callback(
                static fn(?DateTimeImmutable $date): bool =>
                    $date?->format('Y-m-d') === '2027-06-04'
            ),
            1
        )
        ->willReturn([]);

    $service = new ServiceSpectacle($repository);

    expect($service->getSpectacles('2027-06-04', '1'))->toBe([]);
});

test('refuse un identifiant de style invalide', function (string $styleId) {
    $repository = $this->createMock(SpectacleRepositoryInterface::class);

    $repository->expects($this->never())
        ->method('getAll');

    $service = new ServiceSpectacle($repository);

    expect(fn() => $service->getSpectacles(null, $styleId))
        ->toThrow(ValidationException::class);
})->with([
            'Rock',
            '0',
            '-1',
            '1.5',
            '2147483648',
        ]);