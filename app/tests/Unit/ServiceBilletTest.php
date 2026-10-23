<?php
declare(strict_types=1);

use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\application\usecases\ServiceBillet;
use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;

function panierBilletTest(): Panier
{
    return new Panier(
        1,
        [
            new LignePanier(
                1,
                new SoireeResume(2, 'Blues Night', new DateTimeImmutable('2027-06-05 20:30:00')),
                new Tarif(1, 'normal', 3500),
                2
            ),
        ],
        [
            new PassTroisJours(
                3,
                new Tarif(2, 'reduit', 2500),
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

beforeEach(function () {
    $this->billets = $this->createMock(BilletRepositoryInterface::class);
    $this->service = new ServiceBillet($this->billets);
});

test('génère les billets classiques et ceux du pass', function () {
    $this->billets->expects($this->once())
        ->method('creerPourCommande')
        ->with(
            4,
            $this->callback(function (array $nouveaux) {
                expect($nouveaux)->toHaveCount(5);

                expect(array_column($nouveaux, 'soiree_id'))
                    ->toBe([2, 2, 3, 5, 6]);

                expect(array_column($nouveaux, 'tarif_id'))
                    ->toBe([1, 1, 2, 2, 2]);

                expect(array_column($nouveaux, 'pass_id'))
                    ->toBe([null, null, 3, 3, 3]);

                expect(array_column($nouveaux, 'prix'))
                    ->toBe([3500, 3500, 2425, 2425, 2425]);

                foreach ($nouveaux as $billet) {
                    expect($billet['token'])->toMatch('/^[0-9a-f]{64}$/');
                }

                expect(count(array_unique(array_column($nouveaux, 'token'))))
                    ->toBe(5);

                return true;
            })
        );

    $this->service->genererBillets(4, panierBilletTest());
});

test('répartit correctement les centimes du pass', function () {
    $panier = new Panier(
        1,
        [],
        [
            new PassTroisJours(
                3,
                new Tarif(2, 'reduit', 2500),
                101,
                [
                    new SoireeResume(3, 'Metal Storm', new DateTimeImmutable('2027-06-08 19:30:00')),
                    new SoireeResume(5, 'Reggae Vibration', new DateTimeImmutable('2027-06-12 20:00:00')),
                    new SoireeResume(6, 'Punk Attitude', new DateTimeImmutable('2027-06-15 20:30:00')),
                ]
            ),
        ],
        null
    );

    $this->billets->expects($this->once())
        ->method('creerPourCommande')
        ->with(
            4,
            $this->callback(function (array $nouveaux) {
                expect(array_column($nouveaux, 'prix'))
                    ->toBe([2475, 2475, 2474]);

                expect(array_sum(array_column($nouveaux, 'prix')))
                    ->toBe(7424);

                return true;
            })
        );

    $this->service->genererBillets(4, $panier);
});

test('ne crée aucun billet pour un panier vide', function () {
    $panier = new Panier(1, [], [], null);

    $this->billets->expects($this->never())
        ->method('creerPourCommande');

    $this->service->genererBillets(4, $panier);
});

test('refuse un token mal formé sans interroger le repository', function () {
    $this->billets->expects($this->never())
        ->method('trouverParToken');

    expect(function () {
        $this->service->consulterBillet('token-invalide');
    })->toThrow(BilletIntrouvableException::class);
});

test('refuse un token valide mais introuvable', function () {
    $token = str_repeat('ab', 32);

    $this->billets->expects($this->once())
        ->method('trouverParToken')
        ->with($token)
        ->willReturn(null);

    expect(function () use ($token) {
        $this->service->consulterBillet($token);
    })->toThrow(BilletIntrouvableException::class);
});

test('récupère une liste vide pour un utilisateur sans billet', function () {
    $this->billets->expects($this->once())
        ->method('findAllByUser')
        ->with(3)
        ->willReturn([]);

    $resultat = $this->service->getUserBillets(3);

    expect($resultat)->toBe([]);
});
