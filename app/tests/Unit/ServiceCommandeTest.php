<?php
declare(strict_types=1);

use nrv\application\exceptions\CommandeEnAttenteException;
use nrv\application\exceptions\PanierVideException;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\PanierRepositoryInterface;
use nrv\application\ports\api\ServiceBilletInterface;
use nrv\application\usecases\ServiceCommande;
use nrv\domain\entities\Commande;
use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\PlacesInsuffisantesException;
use nrv\application\exceptions\ValidationException;

function panierCommande(): Panier
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
                2,
                new Tarif(2, 'reduit', 2500),
                300,
                [
                    new SoireeResume(3, 'Metal Storm', new DateTimeImmutable('2027-06-08 19:30:00')),
                    new SoireeResume(5, 'Reggae Vibration', new DateTimeImmutable('2027-06-12 20:00:00')),
                    new SoireeResume(6, 'Punk Attitude', new DateTimeImmutable('2027-06-15 20:30:00')),
                ]
            ),
        ],
        3
    );
}


function commandeEnAttenteTest(): Commande
{
    return new Commande(
        3,
        3,
        1,
        'CMD-2026-0003',
        new DateTimeImmutable('2026-10-20 15:00:00'),
        'en_attente',
        14275
    );
}

function commandePayeeTest(): Commande
{
    return new Commande(
        3,
        3,
        null,
        'CMD-2026-0003',
        new DateTimeImmutable('2026-10-20 15:00:00'),
        'payee',
        14275
    );
}

function carteValideTest(): array
{
    return [
        'numero_carte' => '1234567812345678',
        'expiration' => '2099-12',
        'cryptogramme' => '123',
    ];
}


beforeEach(function () {
    $this->commandes = $this->createMock(CommandeRepositoryInterface::class);
    $this->paniers = $this->createMock(PanierRepositoryInterface::class);
    $this->billets = $this->createMock(ServiceBilletInterface::class);

    $this->service = new ServiceCommande(
        $this->commandes,
        $this->paniers,
        $this->billets
    );
});

test('crée la commande et la renvoie avec le contenu du panier', function () {
    $this->commandes->expects($this->once())
        ->method('commander')
        ->with(3, [])
        ->willReturn(true);

    $this->paniers->method('trouverParUtilisateur')->with(3)->willReturn(panierCommande());

    $this->commandes->method('findById')
        ->with(3, 3)
        ->willReturn(new Commande(
            3,
            3,
            1,
            'CMD-2026-0003',
            new DateTimeImmutable('2026-10-20 15:00:00'),
            'en_attente',
            14275
        ));

    $commande = $this->service->creerCommande(3, []);

    expect($commande)->toMatchArray([
        'id' => 3,
        'reference' => 'CMD-2026-0003',
        'date_commande' => '2026-10-20T15:00:00',
        'statut' => 'en_attente',
        'montant_total' => '142.75',
        'devise' => 'EUR',
    ]);

    expect($commande['contenu']['lignes'])->toBe([
        [
            'soiree_id' => 2,
            'nom_soiree' => 'Blues Night',
            'date_heure_debut' => '2027-06-05T20:30:00',
            'tarif_id' => 1,
            'categorie' => 'normal',
            'prix_unitaire' => '35.00',
            'quantite' => 2,
            'sous_total' => '70.00',
        ],
    ]);

    expect($commande['contenu']['pass'])->toBe([
        [
            'id' => 2,
            'soiree_ids' => [3, 5, 6],
            'tarif_id' => 2,
            'categorie' => 'reduit',
            'prix_unitaire_avant_remise' => '25.00',
            'taux_remise' => '3.00',
            'sous_total' => '72.75',
        ],
    ]);
});

test('refuse une deuxième commande quand une commande est déjà en attente', function () {
    $this->commandes->method('commander')->willReturn(false);
    $this->commandes->expects($this->never())->method('findById');

    expect(fn() => $this->service->creerCommande(4, []))
        ->toThrow(CommandeEnAttenteException::class);
});

test('laisse remonter le panier vide', function () {
    $this->commandes->method('commander')->willThrowException(new PanierVideException());

    expect(fn() => $this->service->creerCommande(2, []))
        ->toThrow(PanierVideException::class);
});


test('effectue le paiement avec succès', function () {
    $panier = panierCommande();

    $this->commandes->expects($this->exactly(2))
        ->method('findById')
        ->with(3, 3)
        ->willReturnOnConsecutiveCalls(
            commandeEnAttenteTest(),
            commandePayeeTest()
        );

    $this->paniers->expects($this->once())
        ->method('trouverParUtilisateur')
        ->with(3)
        ->willReturn($panier);

    $this->commandes->expects($this->once())
        ->method('payer')
        ->willReturnCallback(function ($id, $panierRecu, $genererBillets) use ($panier) {
            expect($id)->toBe(3);
            expect($panierRecu)->toBe($panier);

            $genererBillets($id, $panierRecu);

            return [];
        });

    $this->billets->expects($this->once())
        ->method('genererBillets')
        ->with(3, $panier);

    $this->commandes->expects($this->once())
        ->method('compterBillets')
        ->with(3)
        ->willReturn(5);

    $resultat = $this->service->payerCommande(3, 3, carteValideTest());

    expect($resultat)->toBeArray();
});

test('refuse le paiement si les places sont insuffisantes', function () {
    $this->commandes->method('findById')
        ->willReturn(commandeEnAttenteTest());

    $this->paniers->method('trouverParUtilisateur')
        ->willReturn(panierCommande());

    $this->commandes->method('payer')
        ->willReturn([
            ['soiree_id' => 2, 'places_manquantes' => 2]
        ]);

    $this->commandes->expects($this->never())
        ->method('compterBillets');

    $this->billets->expects($this->never())
        ->method('genererBillets');

    expect(function () {
        $this->service->payerCommande(3, 3, carteValideTest());
    })->toThrow(PlacesInsuffisantesException::class);
});

test('refuse un numéro de carte invalide', function () {
    $this->commandes->method('findById')
        ->willReturn(commandeEnAttenteTest());

    $donnees = carteValideTest();
    $donnees['numero_carte'] = '1234';

    $this->commandes->expects($this->never())
        ->method('payer');

    expect(function () use ($donnees) {
        $this->service->payerCommande(3, 3, $donnees);
    })->toThrow(ValidationException::class);
});

test('refuse une carte expirée', function () {
    $this->commandes->method('findById')
        ->willReturn(commandeEnAttenteTest());

    $donnees = carteValideTest();
    $donnees['expiration'] = '2020-01';

    expect(function () use ($donnees) {
        $this->service->payerCommande(3, 3, $donnees);
    })->toThrow(ValidationException::class);
});

test('refuse un cryptogramme invalide', function () {
    $this->commandes->method('findById')
        ->willReturn(commandeEnAttenteTest());

    $donnees = carteValideTest();
    $donnees['cryptogramme'] = '12';

    expect(function () use ($donnees) {
        $this->service->payerCommande(3, 3, $donnees);
    })->toThrow(ValidationException::class);
});

test('refuse un champ inconnu', function () {
    $this->commandes->method('findById')
        ->willReturn(commandeEnAttenteTest());

    $donnees = carteValideTest();
    $donnees['nom_carte'] = 'Alice';

    expect(function () use ($donnees) {
        $this->service->payerCommande(3, 3, $donnees);
    })->toThrow(ValidationException::class);
});

test('refuse une commande appartenant à un autre utilisateur', function () {
    $this->commandes->expects($this->once())
        ->method('findById')
        ->with(4, 3)
        ->willReturn(null);

    $this->commandes->expects($this->never())
        ->method('payer');

    expect(function () {
        $this->service->payerCommande(4, 3, carteValideTest());
    })->toThrow(CommandeIntrouvableException::class);
});

test('ne paie pas une seconde fois une commande déjà payée', function () {
    $this->commandes->method('findById')
        ->with(3, 3)
        ->willReturn(commandePayeeTest());

    $this->commandes->expects($this->never())
        ->method('payer');

    $this->billets->expects($this->never())
        ->method('genererBillets');

    $this->commandes->expects($this->once())
        ->method('compterBillets')
        ->with(3)
        ->willReturn(5);

    $resultat = $this->service->payerCommande(3, 3, carteValideTest());

    expect($resultat)->toBeArray();
});


test('récupère une commande en attente depuis le panier', function () {
    $this->commandes->method('findById')
        ->with(3, 3)
        ->willReturn(commandeEnAttenteTest());

    $this->paniers->expects($this->once())
        ->method('trouverParUtilisateur')
        ->with(3)
        ->willReturn(panierCommande());

    $this->commandes->expects($this->never())
        ->method('trouverContenuPaye');

    $commande = $this->service->getCommandeById(3, 3);

    expect($commande)->toMatchArray([
        'id' => 3,
        'reference' => 'CMD-2026-0003',
        'statut' => 'en_attente',
        'montant_total' => '142.75',
    ]);

    expect($commande['contenu']['lignes'])->toHaveCount(1);
    expect($commande['contenu']['pass'])->toHaveCount(1);
});

test('récupère une commande payée depuis les billets', function () {
    $panier = panierCommande();

    $this->commandes->method('findById')
        ->with(3, 3)
        ->willReturn(commandePayeeTest());

    $this->paniers->expects($this->never())
        ->method('trouverParUtilisateur');

    $this->commandes->expects($this->once())
        ->method('trouverContenuPaye')
        ->with(3)
        ->willReturn([
            'lignes' => $panier->getLignes(),
            'pass' => $panier->getPass()
        ]);

    $commande = $this->service->getCommandeById(3, 3);

    expect($commande)->toMatchArray([
        'id' => 3,
        'reference' => 'CMD-2026-0003',
        'statut' => 'payee',
    ]);

    expect($commande['contenu']['lignes'])->toHaveCount(1);
    expect($commande['contenu']['pass'])->toHaveCount(1);
});

test('refuse de consulter une commande introuvable', function () {
    $this->commandes->method('findById')
        ->with(3, 99)
        ->willReturn(null);

    $this->paniers->expects($this->never())
        ->method('trouverParUtilisateur');

    $this->commandes->expects($this->never())
        ->method('trouverContenuPaye');

    expect(function () {
        $this->service->getCommandeById(3, 99);
    })->toThrow(CommandeIntrouvableException::class);
});

