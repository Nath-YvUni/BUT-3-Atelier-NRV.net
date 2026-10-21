<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetDetailsSoireeAction;
use nrv\application\dto\SoireeDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\domain\entities\Soiree;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function soireeDtoPourAction(int $id = 1): SoireeDTO
{
    return SoireeDTO::fromEntity(new Soiree(
        $id,
        1,
        3,
        'Soirée Rock',
        'Rock et métal',
        new DateTimeImmutable('2027-06-04 20:00:00'),
        300,
        25,
        'Salle Poirel',
        [['nom' => 'Alice']],
        [
            [
                'id' => 1,
                'titre' => 'Pistons & Chrome',
                'description' => 'Un concert de rock.',
                'url_video' => 'https://example.com/video.mp4',
                'artistes' => 'Alice',
                'styles' => 'Rock',
            ],
        ]
    ));
}

function appelerDetailsSoiree(GetDetailsSoireeAction $action, string $id)
{
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/soirees/' . $id);

    return $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        ['id' => $id]
    );
}

test('renvoie le détail de la soirée en JSON avec le statut 200', function () {
    $service = $this->createMock(ServiceSoireeInterface::class);

    $service->expects($this->once())
        ->method('getDetails')
        ->with(1)
        ->willReturn(soireeDtoPourAction(1));

    $reponse = appelerDetailsSoiree(new GetDetailsSoireeAction($service), '1');

    expect($reponse->getStatusCode())->toBe(200);

    expect($reponse->getHeaderLine('Content-Type'))
        ->toContain('application/json');

    $corps = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($corps['id'])->toBe(1);
    expect($corps['nom'])->toBe('Soirée Rock');
    expect($corps['thematique'])->toBe('Rock et métal');
    expect($corps['date_heure_debut'])->toBe('2027-06-04 20:00');
    expect($corps['nom_lieu'])->toBe('Salle Poirel');
    expect($corps['tarif'])->toBe(25);
    expect($corps['liste'][0]['titre'])->toBe('Pistons & Chrome');
});

test('convertit l\'identifiant de l\'URL en entier pour le service', function () {
    $service = $this->createMock(ServiceSoireeInterface::class);

    $service->expects($this->once())
        ->method('getDetails')
        ->with(7)
        ->willReturn(soireeDtoPourAction(7));

    $reponse = appelerDetailsSoiree(new GetDetailsSoireeAction($service), '7');

    expect($reponse->getStatusCode())->toBe(200);
});

test('répond 404 quand la soirée n\'existe pas', function () {
    $service = $this->createMock(ServiceSoireeInterface::class);

    $service->expects($this->once())
        ->method('getDetails')
        ->with(999)
        ->willThrowException(new SoireeIntrouvableException());

    expect(fn() => appelerDetailsSoiree(new GetDetailsSoireeAction($service), '999'))
        ->toThrow(HttpNotFoundException::class);
});

test('ne transforme pas une erreur du service en 404', function () {
    $service = $this->createMock(ServiceSoireeInterface::class);

    $service->expects($this->once())
        ->method('getDetails')
        ->willThrowException(new RuntimeException('Base indisponible'));

    $erreur = null;

    try {
        appelerDetailsSoiree(new GetDetailsSoireeAction($service), '1');
    } catch (Throwable $e) {
        $erreur = $e;
    }

    expect($erreur)->toBeInstanceOf(RuntimeException::class);
    expect($erreur)->not->toBeInstanceOf(HttpNotFoundException::class);
});

// Seules les soirées 1 et 12 existent : un identifiant invalide ne doit
// jamais être « deviné » (ex. « 12abc » ne doit pas donner la soirée 12).
test('refuse un identifiant invalide avec une erreur 400 ou 404', function (string $id) {
    $service = $this->createMock(ServiceSoireeInterface::class);

    $service->method('getDetails')
        ->willReturnCallback(function (int $id) {
            if ($id === 1 || $id === 12) {
                return soireeDtoPourAction($id);
            }

            throw new SoireeIntrouvableException();
        });

    $erreur = null;

    try {
        appelerDetailsSoiree(new GetDetailsSoireeAction($service), $id);
    } catch (HttpException $e) {
        $erreur = $e;
    }

    expect($erreur)->not->toBeNull();
    expect($erreur->getCode())->toBeIn([400, 404]);
})->with(['abc', '-1', '0', '1.5', '12abc', '']);
