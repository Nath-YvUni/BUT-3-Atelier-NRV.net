<?php
declare(strict_types=1);

use nrv\application\dto\SoireeDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\usecases\ServiceSoiree;
use nrv\domain\entities\Soiree;

function soireeDeTestPourService(array $liste = []): Soiree
{
    return new Soiree(
        1,
        1,
        3,
        'Soirée Rock',
        'Rock et métal',
        new DateTimeImmutable('2027-06-04 20:00:00'),
        300,
        25,
        'Salle Poirel',
        [['nom' => 'Alice'], ['nom' => 'Bob']],
        $liste
    );
}

test('retourne le détail d\'une soirée existante', function () {
    $repository = $this->createMock(SoireeRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('findById')
        ->with(1)
        ->willReturn(soireeDeTestPourService());

    $service = new ServiceSoiree($repository);

    $detail = $service->getDetails(1);

    expect($detail)->toBeInstanceOf(SoireeDTO::class);

    expect($detail->toArray())->toMatchArray([
        'id' => 1,
        'festival_id' => 1,
        'lieu_id' => 3,
        'nom' => 'Soirée Rock',
        'thematique' => 'Rock et métal',
        'date_heure_debut' => '2027-06-04 20:00',
        'capacite' => 300,
        'tarif' => 25,
        'nom_lieu' => 'Salle Poirel',
    ]);
});

test('lève une exception quand la soirée n\'existe pas', function () {
    $repository = $this->createMock(SoireeRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('findById')
        ->with(999)
        ->willReturn(null);

    $service = new ServiceSoiree($repository);

    expect(fn() => $service->getDetails(999))
        ->toThrow(SoireeIntrouvableException::class);
});

test('ne masque pas une erreur du repository par une soirée introuvable', function () {
    $repository = $this->createMock(SoireeRepositoryInterface::class);

    $repository->expects($this->once())
        ->method('findById')
        ->willThrowException(new RuntimeException('Base indisponible'));

    $service = new ServiceSoiree($repository);

    $erreur = null;

    try {
        $service->getDetails(1);
    } catch (Throwable $e) {
        $erreur = $e;
    }

    expect($erreur)->toBeInstanceOf(RuntimeException::class);
    expect($erreur)->not->toBeInstanceOf(SoireeIntrouvableException::class);
});

test('retourne les spectacles de la soirée avec toutes leurs informations', function () {
    $spectacles = [
        [
            'id' => 1,
            'titre' => 'Pistons & Chrome',
            'description' => 'Un concert de rock.',
            'url_video' => 'https://example.com/video.mp4',
            'artistes' => 'Alice, Bob',
            'styles' => 'Rock',
        ],
        [
            'id' => 2,
            'titre' => 'Electric Tour',
            'description' => 'Un concert électrique.',
            'url_video' => 'https://example.com/electric.mp4',
            'artistes' => 'Bob',
            'styles' => 'Métal',
        ],
    ];

    $repository = $this->createMock(SoireeRepositoryInterface::class);

    $repository->method('findById')
        ->willReturn(soireeDeTestPourService($spectacles));

    $detail = (new ServiceSoiree($repository))->getDetails(1)->toArray();

    expect($detail['liste'])->toBe($spectacles);
});

test('retourne une liste de spectacles vide pour une soirée sans spectacle', function () {
    $repository = $this->createMock(SoireeRepositoryInterface::class);

    $repository->method('findById')
        ->willReturn(soireeDeTestPourService([]));

    $detail = (new ServiceSoiree($repository))->getDetails(1)->toArray();

    expect($detail['liste'])->toBe([]);
});
