<?php
declare(strict_types=1);

use nrv\application\exceptions\ValidationException;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\TarifRepositoryInterface;
use nrv\application\usecases\ServiceSoiree;

beforeEach(function () {
    $this->soirees = $this->createMock(SoireeRepositoryInterface::class);
    $this->service = new ServiceSoiree(
        $this->soirees,
        $this->createMock(TarifRepositoryInterface::class)
    );
});

test('retourne les soirées au format du contrat', function () {
    $this->soirees->expects($this->once())
        ->method('findByAll')
        ->with(null)
        ->willReturn([soireeProgrammeDeTest(800, 1)]);

    expect($this->service->getSoirees())->toBe([
        [
            'id' => 2,
            'nom' => 'Blues Night',
            'thematique' => 'Soirée blues',
            'date_heure_debut' => '2027-06-05T20:30:00',
            'lieu' => [
                'id' => 1,
                'nom' => 'L\'Autre Canal',
                'adresse' => '45 boulevard d\'Austrasie, 54000 Nancy',
            ],
            'capacite' => 800,
            'places_vendues' => 1,
            'places_disponibles' => 799,
            'complete' => false,
            '_links' => [
                'self' => ['href' => '/api/soirees/2', 'method' => 'GET'],
            ],
        ],
    ]);
});

test('transmet la date demandée au repository', function () {
    $this->soirees->expects($this->once())
        ->method('findByAll')
        ->with($this->callback(
            fn(?DateTimeImmutable $date) => $date?->format('Y-m-d H:i:s') === '2027-06-05 00:00:00'
        ))
        ->willReturn([]);

    expect($this->service->getSoirees('2027-06-05'))->toBe([]);
});

test('ignore une date vide', function () {
    $this->soirees->expects($this->once())
        ->method('findByAll')
        ->with(null)
        ->willReturn([]);

    expect($this->service->getSoirees(''))->toBe([]);
});

test('refuse une date invalide', function (string $date) {
    $this->soirees->expects($this->never())->method('findByAll');

    expect(fn() => $this->service->getSoirees($date))->toThrow(ValidationException::class);
})->with(['05/06/2027', '2027-02-30', '2027-6-5', '2027-06-05x']);
