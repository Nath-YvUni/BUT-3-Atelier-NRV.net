<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use nrv\adapters\security\GestionJetonJWT;
use nrv\application\exceptions\AuthentificationException;

test('crée un jeton permettant de retrouver son utilisateur', function () {
    $gestion = new GestionJetonJWT(str_repeat('a', 64));

    $resultat = $gestion->creer(3);

    expect($resultat['token_type'])->toBe('Bearer');
    expect($resultat['expires_in'])->toBe(3600);
    expect($gestion->lire($resultat['access_token']))->toBe(3);
});

test('refuse un jeton signé avec une autre clé', function () {
    $emetteur = new GestionJetonJWT(str_repeat('a', 64));
    $lecteur = new GestionJetonJWT(str_repeat('b', 64));

    $resultat = $emetteur->creer(3);

    expect(fn() => $lecteur->lire($resultat['access_token']))
        ->toThrow(AuthentificationException::class);
});

test('refuse un jeton mal formé', function () {
    $gestion = new GestionJetonJWT(str_repeat('a', 64));

    expect(fn() => $gestion->lire('jeton-invalide'))
        ->toThrow(AuthentificationException::class);
});

test('refuse un jeton expiré', function () {
    $cle = str_repeat('a', 64);
    $gestion = new GestionJetonJWT($cle);

    $jeton = JWT::encode([
        'iss' => 'nrv.net',
        'aud' => 'nrv.web',
        'sub' => '3',
        'iat' => time() - 120,
        'exp' => time() - 60,
    ], $cle, 'HS256');

    expect(fn() => $gestion->lire($jeton))
        ->toThrow(AuthentificationException::class);
});

test('refuse un jeton sans expiration', function () {
    $cle = str_repeat('a', 64);
    $gestion = new GestionJetonJWT($cle);

    $jeton = JWT::encode([
        'iss' => 'nrv.net',
        'aud' => 'nrv.web',
        'sub' => '3',
        'iat' => time(),
    ], $cle, 'HS256');

    expect(fn() => $gestion->lire($jeton))
        ->toThrow(AuthentificationException::class);
});