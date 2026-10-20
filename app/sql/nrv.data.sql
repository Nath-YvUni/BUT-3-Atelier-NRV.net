-- Jeu de données de test NRV.net (à importer après nrv.schema.sql, sur une base vide)
-- Mot de passe de tous les comptes : password


-- ── Programme ─

INSERT INTO "festival" ("id", "nom", "date_debut", "date_fin") VALUES
(1, 'Nancy Rock Vibration 2027', '2027-06-04', '2027-06-17');

INSERT INTO "lieu" ("id", "nom", "adresse") VALUES
(1, 'L''Autre Canal', '45 boulevard d''Austrasie, 54000 Nancy'),
(2, 'Zénith de Nancy', 'Rue du Zénith, 54320 Maxéville'),
(3, 'Salle Poirel', '3 rue Victor Poirel, 54000 Nancy');

-- Soirée 4 : capacité de 4 places, déjà toutes vendues (test d'achat refusé)
INSERT INTO "soiree" ("id", "festival_id", "lieu_id", "nom", "thematique", "date_heure_debut", "capacite") VALUES
(1, 1, 2, 'Rock''n''Nancy', 'Soirée classic rock', '2027-06-04 20:00', 5000),
(2, 1, 1, 'Blues Night', 'Soirée blues', '2027-06-05 20:30', 800),
(3, 1, 2, 'Metal Storm', 'Soirée métal', '2027-06-08 19:30', 5000),
(4, 1, 3, 'Chansons d''ici', 'Soirée chanson française', '2027-06-10 20:00', 4),
(5, 1, 1, 'Reggae Vibration', 'Soirée reggae', '2027-06-12 20:00', 800),
(6, 1, 1, 'Punk Attitude', 'Soirée punk', '2027-06-15 20:30', 800),
(7, 1, 2, 'Rock Legends', 'Soirée de clôture rock', '2027-06-17 19:00', 5000);

INSERT INTO "style_musical" ("id", "libelle") VALUES
(1, 'Classic rock'),
(2, 'Blues rock'),
(3, 'Métal'),
(4, 'Chanson'),
(5, 'Reggae'),
(6, 'Punk'),
(7, 'Hard rock'),
(8, 'Folk'),
(9, 'Blues');

INSERT INTO "artiste" ("id", "nom") VALUES
(1, 'The Rusty Pistons'),
(2, 'Velvet Thunder'),
(3, 'Marie Laroche'),
(4, 'Big Joe Mulligan'),
(5, 'Delta Sisters'),
(6, 'Iron Lorraine'),
(7, 'Necrofest'),
(8, 'Hurlement'),
(9, 'Léo Marchal'),
(10, 'Camille Doré'),
(11, 'Roots Kingdom'),
(12, 'Lion Steppers'),
(13, 'Les Pavés Sauvages'),
(14, 'No Futur Club'),
(15, 'Thunderhawk'),
(16, 'The Midnight Riders'),
(17, 'Sarah Blackwood'),
(18, 'Kaya Sound System');

INSERT INTO "spectacle" ("id", "soiree_id", "titre", "description", "url_video", "horaire_previsionnel") VALUES
(1, 1, 'Pistons & Chrome', 'Les Rusty Pistons ouvrent le festival avec leurs riffs seventies et leurs solos interminables.', 'https://example.com/videos/spectacle-1.mp4', '20:00'),
(2, 1, 'Electric Tour', 'Velvet Thunder présente son nouvel album, entre classic rock et hard rock survolté.', 'https://example.com/videos/spectacle-2.mp4', '21:15'),
(3, 1, 'Thunderhawk Live', 'Un mur d''amplis, deux batteries et un chanteur à la voix de stentor.', 'https://example.com/videos/spectacle-3.mp4', '22:30'),
(4, 2, 'Mississippi to Moselle', 'Big Joe Mulligan revisite les standards du delta blues, guitare slide à la main.', 'https://example.com/videos/spectacle-4.mp4', '20:30'),
(5, 2, 'Delta Sisters', 'Trois voix, trois guitares : le blues rock des Delta Sisters en version électrique.', 'https://example.com/videos/spectacle-5.mp4', '21:45'),
(6, 3, 'Forge Tour', 'Iron Lorraine martèle son heavy metal sidérurgique, hommage aux hauts fourneaux lorrains.', 'https://example.com/videos/spectacle-6.mp4', '19:30'),
(7, 3, 'Necrofest', 'Un set brutal et rapide, réservé aux amateurs de métal extrême.', 'https://example.com/videos/spectacle-7.mp4', '20:45'),
(8, 3, 'Hurlement', 'Le groupe nancéien mêle métal et hard rock dans un show pyrotechnique.', 'https://example.com/videos/spectacle-8.mp4', '22:00'),
(9, 4, 'Piano-voix', 'Léo Marchal, seul au piano, chante ses textes tendres et ironiques.', 'https://example.com/videos/spectacle-9.mp4', '20:00'),
(10, 4, 'Duo d''ici', 'Camille Doré et Marie Laroche croisent chanson française et folk acoustique.', 'https://example.com/videos/spectacle-10.mp4', '21:15'),
(11, 5, 'Roots Kingdom', 'Du reggae roots aux cuivres chaleureux, pour danser jusqu''au bout de la nuit.', 'https://example.com/videos/spectacle-11.mp4', '20:00'),
(12, 5, 'Steppers Sound Clash', 'Lion Steppers et Kaya Sound System se répondent sur un mur de basses.', 'https://example.com/videos/spectacle-12.mp4', '21:30'),
(13, 6, 'Les Pavés Sauvages', 'Punk rock en français, refrains à hurler en chœur.', 'https://example.com/videos/spectacle-13.mp4', '20:30'),
(14, 6, 'No Futur Club', 'Trois accords, beaucoup d''énergie et quelques incursions hard rock.', 'https://example.com/videos/spectacle-14.mp4', '21:45'),
(15, 7, 'Midnight Ride', 'The Midnight Riders reprennent la route avec leur rock américain classique.', 'https://example.com/videos/spectacle-15.mp4', '19:00'),
(16, 7, 'Sarah Blackwood', 'Une voix soul posée sur un blues rock incandescent.', 'https://example.com/videos/spectacle-16.mp4', '20:15'),
(17, 7, 'Grand final', 'Rusty Pistons, Velvet Thunder et Thunderhawk réunis sur scène pour clôturer le festival.', 'https://example.com/videos/spectacle-17.mp4', '21:45');

INSERT INTO "spectacle_image" ("spectacle_id", "url") VALUES
(1, 'https://picsum.photos/seed/nrv-1/800/600'),
(1, 'https://picsum.photos/seed/nrv-1b/800/600'),
(2, 'https://picsum.photos/seed/nrv-2/800/600'),
(3, 'https://picsum.photos/seed/nrv-3/800/600'),
(4, 'https://picsum.photos/seed/nrv-4/800/600'),
(5, 'https://picsum.photos/seed/nrv-5/800/600'),
(6, 'https://picsum.photos/seed/nrv-6/800/600'),
(6, 'https://picsum.photos/seed/nrv-6b/800/600'),
(7, 'https://picsum.photos/seed/nrv-7/800/600'),
(8, 'https://picsum.photos/seed/nrv-8/800/600'),
(9, 'https://picsum.photos/seed/nrv-9/800/600'),
(10, 'https://picsum.photos/seed/nrv-10/800/600'),
(11, 'https://picsum.photos/seed/nrv-11/800/600'),
(12, 'https://picsum.photos/seed/nrv-12/800/600'),
(13, 'https://picsum.photos/seed/nrv-13/800/600'),
(14, 'https://picsum.photos/seed/nrv-14/800/600'),
(15, 'https://picsum.photos/seed/nrv-15/800/600'),
(16, 'https://picsum.photos/seed/nrv-16/800/600'),
(17, 'https://picsum.photos/seed/nrv-17/800/600'),
(17, 'https://picsum.photos/seed/nrv-17b/800/600');

INSERT INTO "spectacle_artiste" ("spectacle_id", "artiste_id") VALUES
(1, 1), (2, 2), (3, 15),
(4, 4), (5, 5),
(6, 6), (7, 7), (8, 8),
(9, 9), (10, 10), (10, 3),
(11, 11), (12, 12), (12, 18),
(13, 13), (14, 14),
(15, 16), (16, 17), (17, 1), (17, 2), (17, 15);

INSERT INTO "spectacle_style" ("spectacle_id", "style_id") VALUES
(1, 1), (2, 1), (2, 7), (3, 7),
(4, 9), (4, 2), (5, 2),
(6, 3), (7, 3), (8, 3), (8, 7),
(9, 4), (10, 4), (10, 8),
(11, 5), (12, 5),
(13, 6), (14, 6), (14, 7),
(15, 1), (16, 2), (16, 1), (17, 1), (17, 7);


-- ── Utilisateurs et tarifs ─

INSERT INTO "utilisateur" ("id", "nom", "email", "mot_de_passe_hash", "role") VALUES
(1, 'Gestionnaire NRV', 'gestionnaire@nrv.net', '$2y$10$EUdvzLUzjUrI1f1Nk4P4SOJu1MByX7RbW1pk329PWLc3K5sIYgVxq', 'gestionnaire'),
(2, 'Alice Martin', 'alice@example.com', '$2y$10$EUdvzLUzjUrI1f1Nk4P4SOJu1MByX7RbW1pk329PWLc3K5sIYgVxq', 'utilisateur'),
(3, 'Bob Durand', 'bob@example.com', '$2y$10$EUdvzLUzjUrI1f1Nk4P4SOJu1MByX7RbW1pk329PWLc3K5sIYgVxq', 'utilisateur'),
(4, 'Claire Petit', 'claire@example.com', '$2y$10$EUdvzLUzjUrI1f1Nk4P4SOJu1MByX7RbW1pk329PWLc3K5sIYgVxq', 'utilisateur');

INSERT INTO "tarif" ("id", "categorie", "prix") VALUES
(1, 'normal', 35.00),
(2, 'reduit', 25.00);


-- ── Paniers ─
-- Bob : panier non validé (billets + pass 3 jours), pour tester le panier et sa validation.
-- Claire : panier validé, commande en attente sur la soirée 4 complète (paiement refusé).

INSERT INTO "panier" ("id", "utilisateur_id") VALUES
(1, 3),
(2, 4);

INSERT INTO "ligne_panier" ("panier_id", "soiree_id", "tarif_id", "quantite") VALUES
(1, 2, 1, 2),
(1, 5, 2, 1),
(2, 4, 1, 2);


-- ── Commandes et billets ─
-- Alice : commande payée (panier supprimé après paiement), avec un pass 3 jours.

INSERT INTO "commande" ("id", "utilisateur_id", "panier_id", "reference", "date_commande", "statut", "montant_total") VALUES
(1, 2, NULL, 'CMD-2026-0001', '2026-10-15 10:12:00', 'payee', 256.85),
(2, 4, 2, 'CMD-2026-0002', '2026-10-19 18:40:00', 'en_attente', 70.00);

-- Pass 1 : payé par Alice (soirées 1, 3, 7) ; pass 2 : dans le panier de Bob (soirées 3, 5, 6)
INSERT INTO "pass_trois_jours" ("id", "panier_id", "commande_id", "tarif_id", "taux_remise") VALUES
(1, NULL, 1, 1, 3.00),
(2, 1, NULL, 2, 3.00);

INSERT INTO "pass_soiree" ("pass_id", "soiree_id") VALUES
(1, 1), (1, 3), (1, 7),
(2, 3), (2, 5), (2, 6);

-- Prix figé à l'achat : 35.00 / 25.00, ou 35.00 - 3 % = 33.95 pour les billets du pass.
-- Total commande 1 : 2 × 35 + 2 × 25 (soirée 4) + 35 (soirée 2) + 3 × 33.95 (pass) = 256.85
INSERT INTO "billet" ("id", "commande_id", "soiree_id", "tarif_id", "pass_id", "reference", "token", "prix") VALUES
(1, 1, 4, 1, NULL, 'BIL-2026-0001', '94e5bee840008671d1de7d204a9160024e8256030f0b70bb37bfb6d7f1bd2856', 35.00),
(2, 1, 4, 1, NULL, 'BIL-2026-0002', '43a8f55c69fec850efb2cafcdbcb1dfbcedd563547c0cf7905b931c2db45f289', 35.00),
(3, 1, 4, 2, NULL, 'BIL-2026-0003', '9590e18ac925993bae12fc757d65589b69cc9fbbc5f10a91c875e42c2e81babc', 25.00),
(4, 1, 4, 2, NULL, 'BIL-2026-0004', 'cc8b0ddd4699cb5b5454230bef5e56079a3ad3b7b5ea7042c15e7dece33e94e9', 25.00),
(5, 1, 2, 1, NULL, 'BIL-2026-0005', 'f77bddce18c7877e4ebafcd9720641362ffbe52b3db68b1da2d4273f88502573', 35.00),
(6, 1, 1, 1, 1, 'BIL-2026-0006', 'f3327a8fcc9f8c4736d039d373a5da9ca23fd6b5ef50f3687e79649a8a42421a', 33.95),
(7, 1, 3, 1, 1, 'BIL-2026-0007', '1de5b3d73328c78bccb083bb8dab6907c552ae62328f91f42cd3ec5c0b45f69e', 33.95),
(8, 1, 7, 1, 1, 'BIL-2026-0008', '318862371e4abfe7b2c8eb20ece340df9b5052ec0c5c42a7bdf66150591c3af3', 33.95);


-- ── Compteurs d'identifiants ─
-- Les id ci-dessus sont fixés à la main : on recale les compteurs pour les prochains INSERT.

SELECT setval(pg_get_serial_sequence('festival', 'id'), (SELECT MAX("id") FROM "festival"));
SELECT setval(pg_get_serial_sequence('lieu', 'id'), (SELECT MAX("id") FROM "lieu"));
SELECT setval(pg_get_serial_sequence('soiree', 'id'), (SELECT MAX("id") FROM "soiree"));
SELECT setval(pg_get_serial_sequence('style_musical', 'id'), (SELECT MAX("id") FROM "style_musical"));
SELECT setval(pg_get_serial_sequence('artiste', 'id'), (SELECT MAX("id") FROM "artiste"));
SELECT setval(pg_get_serial_sequence('spectacle', 'id'), (SELECT MAX("id") FROM "spectacle"));
SELECT setval(pg_get_serial_sequence('utilisateur', 'id'), (SELECT MAX("id") FROM "utilisateur"));
SELECT setval(pg_get_serial_sequence('tarif', 'id'), (SELECT MAX("id") FROM "tarif"));
SELECT setval(pg_get_serial_sequence('panier', 'id'), (SELECT MAX("id") FROM "panier"));
SELECT setval(pg_get_serial_sequence('commande', 'id'), (SELECT MAX("id") FROM "commande"));
SELECT setval(pg_get_serial_sequence('pass_trois_jours', 'id'), (SELECT MAX("id") FROM "pass_trois_jours"));
SELECT setval(pg_get_serial_sequence('billet', 'id'), (SELECT MAX("id") FROM "billet"));
