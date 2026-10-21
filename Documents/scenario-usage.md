# NRV.net - Scénarios d’usage

## 1. Objectif et périmètre

Ce document décrit les actions des utilisateurs et les réponses attendues de NRV.net, à partir des maquettes du projet. Il sert à expliquer les parcours, à guider le développement et à préparer la démonstration.

Le scénario **SU-01** correspond au périmètre de l’itération 1 : consulter le programme, ouvrir le détail d’une soirée depuis un spectacle et revenir au programme (**F1, F2 et F3**).

Les autres scénarios décrivent le fonctionnement cible des itérations suivantes. Leur présence dans ce document ne signifie pas que les fonctionnalités sont déjà développées.

### Maquettes de référence

- [Prototype Figma - NRV.net](https://www.figma.com/proto/vG1k2KVTXAAhAbSLOdG5Ev/SAE-NRV.net-%C2%B7-Wireframes?node-id=2-6&page-id=0%3A1&starting-point-node-id=2%3A6&scaling=min-zoom&content-scaling=fixed&show-proto-sidebar=1).
- [Export des wireframes](Wireframes.pdf), avec les versions ordinateur et mobile.

Les noms, dates et montants des exemples ci-dessous reprennent les maquettes : festival en juillet 2027, soirée « Nuit du Blues » et acheteuse « Camille Martin ». Ils sont illustratifs et diffèrent du jeu de données SQL actuel, qui utilise notamment des dates en juin 2027. Pour une démonstration de l’application, les libellés et dates doivent être adaptés aux données effectivement chargées.

## 2. Acteurs

| Acteur                 | Besoin                                                             |
| ---------------------- | ------------------------------------------------------------------ |
| Visiteur               | Découvrir les spectacles et les soirées sans se connecter.         |
| Utilisateur enregistré | Acheter des billets, consulter ses achats et imprimer ses billets. |
| Gestionnaire           | Consulter les places vendues et les recettes de chaque soirée.     |

## 3. SU-01 - Découvrir une soirée depuis le programme

**Périmètre :** itération 1, fonctionnalités F1, F2 et F3.

**Actrice :** Camille, visiteuse non connectée.

**Objectif :** identifier une soirée qui l’intéresse et connaître ses spectacles, son lieu, ses horaires et ses tarifs.

**Situation de départ :** Camille ouvre NRV.net. Le programme et les soirées sont renseignés. Aucun compte n’est nécessaire.

| Étape | Action de Camille                                   | Réponse attendue de l’application                                                                                                                            | Écran de référence             |
| ----- | --------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------ |
| 1     | Elle accède au programme du festival.               | La liste des spectacles s’affiche. Chaque carte présente une image, un titre, une date et un horaire.                                                        | Programme : PDF p. 2 et 3.     |
| 2     | Elle parcourt les cartes et repère « Blue Harbor ». | Elle peut identifier la date et l’horaire du spectacle avant de l’ouvrir.                                                                                    | Programme.                     |
| 3     | Elle sélectionne la carte « Blue Harbor ».          | Le détail de la soirée correspondante, « Nuit du Blues », s’affiche.                                                                                         | Détail soirée : PDF p. 4 et 5. |
| 4     | Elle consulte les informations de la soirée.        | Le nom, la thématique, la date, l’horaire de début, le lieu et les tarifs sont visibles.                                                                     | Détail soirée.                 |
| 5     | Elle consulte les spectacles de la soirée.          | Elle retrouve « Blue Harbor », « Moonshine Ramblers » et « Delta Sisters », avec leurs horaires, artistes, descriptions, styles, images et accès aux vidéos. | Détail soirée.                 |
| 6     | Elle utilise « Retour au programme ».               | La liste des spectacles est de nouveau accessible.                                                                                                           | Programme.                     |

**Résultat attendu :** Camille a trouvé une soirée et dispose des informations nécessaires pour décider si elle souhaite acheter des billets.

**Critères de réussite :**

- Le programme est consultable sans authentification.
- La carte ouvre la soirée associée au spectacle sélectionné.
- Le détail contient tous les spectacles de cette soirée et leurs informations.
- Le retour au programme fonctionne sur ordinateur et sur mobile.

Les filtres, la pagination, l’authentification et l’achat ne sont pas nécessaires pour démontrer ce scénario à l’itération 1.

## 4. SU-02 - Acheter des billets et les imprimer

**Périmètre :** parcours cible des itérations suivantes, incluant l’authentification, F6 à F10 et le pass trois jours.

**Actrice :** Camille Martin, utilisatrice possédant un compte mais initialement déconnectée.

**Objectif :** acheter un billet normal et un billet réduit pour « Nuit du Blues », ainsi qu’un pass trois jours au tarif normal.

**Situation de départ :** Camille a ouvert le détail de « Nuit du Blues ». Les soirées choisies disposent d’assez de places au moment du paiement. La personne qui utilisera le billet réduit relève d’une catégorie prévue pour ce tarif.

| Étape | Action de Camille                                                                                                                                                    | Réponse attendue de l’application                                                                                                                                                      | Écran de référence                                                         |
| ----- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| 1     | Dans le bloc « Billets », elle choisit un billet normal et un billet réduit.                                                                                         | Les quantités sont visibles et le total de cette sélection est de 60,00 €.                                                                                                             | Détail soirée : PDF p. 4 et 5.                                             |
| 2     | Elle clique sur « Ajouter au panier ».                                                                                                                               | L’application lui demande de se connecter et conserve sa sélection pendant cette identification, conformément au message de la maquette.                                               | Connexion : PDF p. 6 et 7.                                                 |
| 3     | Elle saisit son adresse e-mail et son mot de passe, puis clique sur « Se connecter ».                                                                                | Après identification réussie, elle retrouve sa sélection et peut poursuivre son achat sans ressaisir les quantités.                                                                    | Connexion, puis reprise du parcours d’achat.                               |
| 4     | Elle choisit aussi un « Pass 3 jours » au tarif normal pour « Soirée Classic Rock » le 3 juillet, « Nuit du Métal » le 6 juillet et « Soirée Chanson » le 7 juillet. | Le pass comprend trois soirées différentes. Sa remise de 3 % est appliquée et son montant est de 101,85 €.                                                                             | Entrée « Choisir » sur le détail ; contenu du pass visible dans le panier. |
| 5     | Elle ouvre « Panier ».                                                                                                                                               | Le panier présente trois articles représentant cinq billets, avec le détail des catégories et des montants. Le total est de 161,85 €.                                                  | Panier : PDF p. 8 et 9.                                                    |
| 6     | Elle vérifie le récapitulatif et clique sur « Valider la commande ».                                                                                                 | Une commande en attente de paiement est créée. L’écran de paiement présente sa référence, son contenu et son montant.                                                                  | Paiement : PDF p. 10 et 11.                                                |
| 7     | Elle renseigne les données fictives de carte demandées, puis clique sur « Payer 161,85 € ».                                                                          | L’application vérifie les données saisies et la disponibilité de toutes les places demandées. Le paiement reste une simulation.                                                        | Paiement.                                                                  |
| 8     | Elle attend le résultat du paiement.                                                                                                                                 | Si l’ensemble de l’achat peut être validé, la commande est confirmée et cinq billets sont créés. La page affiche « Commande validée ! ».                                               | Confirmation : PDF p. 12 et 13.                                            |
| 9     | Elle choisit « Voir / imprimer » sur un billet.                                                                                                                      | Le billet correspondant s’ouvre grâce à son lien contenant un token. Il affiche le nom de l’acheteuse, sa référence, la soirée, la date, l’horaire, le lieu et la catégorie tarifaire. | Billet : PDF p. 14 et 15.                                                  |
| 10    | Elle clique sur « Imprimer ».                                                                                                                                        | La fenêtre d’impression s’ouvre. La version imprimée contient le billet ; les boutons et la navigation sont masqués.                                                                   | Billet, version d’impression.                                              |

### Montant de l’exemple

| Élément                            | Calcul                                                    | Montant      |
| ---------------------------------- | --------------------------------------------------------- | ------------ |
| Un billet normal « Nuit du Blues » | 1 × 35,00 €                                               | 35,00 €      |
| Un billet réduit « Nuit du Blues » | 1 × 25,00 €                                               | 25,00 €      |
| Un pass trois jours normal         | 3 × 35,00 € = 105,00 €, moins 3,15 € de remise            | 101,85 €     |
| **Total**                          | **Deux billets individuels et les trois billets du pass** | **161,85 €** |

**Résultat attendu :** la commande est payée et Camille dispose de cinq billets consultables et imprimables. Chaque billet possède son propre lien. Les billets restent non nominatifs : le nom affiché est celui de l’acheteuse.

### Variantes et cas d’échec attendus

Les comportements ci-dessous décrivent les états à prévoir ; leurs écrans ne sont pas tous représentés dans les wireframes.

| Situation                                                    | Réponse attendue                                                                                                                                                          |
| ------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Camille ne possède pas de compte.                            | Elle utilise « Créer un compte », renseigne les champs du formulaire puis reprend son achat après identification.                                                         |
| Les identifiants sont incorrects.                            | Un message explique que la connexion a échoué et permet une nouvelle tentative.                                                                                           |
| Une donnée obligatoire du formulaire manque ou est invalide. | Le champ concerné est signalé et l’utilisateur peut corriger sa saisie.                                                                                                   |
| Camille tente de valider un panier vide.                     | La validation est empêchée et elle est invitée à choisir des billets.                                                                                                     |
| Les données du paiement simulé sont invalides.               | Le paiement n’est pas validé et un message permet de corriger les champs. Aucun billet n’est créé.                                                                        |
| Une soirée manque de places au moment du paiement.           | Toute la commande est refusée. Aucune partie de l’achat n’est validée et aucun billet n’est créé. Les soirées concernées sont indiquées pour permettre de revoir l’achat. |
| Le lien d’un billet est inconnu.                             | Le billet n’est pas affiché ; un message indique qu’il est introuvable.                                                                                                   |

## 5. SU-03 - Affiner le programme

**Périmètre :** F4 et F5 pour les filtres ; E1 pour la pagination, à réaliser après les fonctionnalités de base.

**Actrice :** Camille, connectée ou non.

**Objectif :** trouver les spectacles correspondant à ses disponibilités et à ses goûts.

**Situation de départ :** Camille consulte le programme. Les maquettes du programme présentent les champs « Date » et « Style de musique », ainsi que des commandes de pagination.

| Étape | Action de Camille                                                             | Réponse attendue                                                              |
| ----- | ----------------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| 1     | Elle sélectionne le 4 juillet dans « Date ».                                  | Seuls les spectacles dont la soirée a lieu le 4 juillet restent affichés.     |
| 2     | Elle sélectionne aussi « Blues rock ».                                        | La liste ne contient plus que les spectacles qui répondent aux deux critères. |
| 3     | Elle choisit « Toutes les dates » et « Tous les styles ».                     | Le programme complet redevient accessible.                                    |
| 4     | Si la pagination est réalisée, elle utilise « Suivant » ou un numéro de page. | La page demandée s’affiche en conservant les filtres sélectionnés.            |

**Variante :** si aucun spectacle ne correspond, l’application affiche un message explicite et laisse les filtres accessibles pour modifier la recherche.

**Résultat attendu :** Camille peut trouver un spectacle pertinent, puis poursuivre vers le détail de sa soirée comme dans SU-01.

## 6. SU-04 - Consulter les ventes dans le backoffice

**Périmètre :** authentification et F11.

**Acteur :** un gestionnaire du festival.

**Objectif :** connaître le remplissage et les recettes de chaque soirée.

**Situation de départ :** le gestionnaire possède un compte autorisé. Des ventes payées sont disponibles ; certaines soirées peuvent n’avoir vendu aucun billet.

| Étape | Action du gestionnaire                          | Réponse attendue                                                                           | Écran de référence                          |
| ----- | ----------------------------------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------- |
| 1     | Il se connecte avec son compte gestionnaire.    | L’application identifie son rôle et autorise l’accès au backoffice.                        | Connexion ; accès au backoffice à préciser. |
| 2     | Il ouvre « Jauge des soirées ».                 | Le tableau de bord présente les indicateurs de ventes et la liste des soirées.             | Backoffice : PDF p. 16 et 17.               |
| 3     | Il consulte une soirée.                         | Il lit son nom, sa date, le nombre de places vendues rapporté à sa capacité et sa recette. | Backoffice.                                 |
| 4     | Il repère une soirée ayant atteint sa capacité. | La soirée porte l’indication « Complet ».                                                  | Backoffice.                                 |
| 5     | Il se déconnecte.                               | Son accès authentifié prend fin.                                                           | Commande « Déconnexion » du backoffice.     |

**Résultat attendu :** le gestionnaire peut identifier les soirées complètes et suivre les recettes issues des commandes payées, en tenant compte des tarifs réduits et des remises des pass.

**Variante :** un visiteur non connecté ou un utilisateur sans rôle gestionnaire ne peut pas accéder aux données du backoffice.

## 7. Adaptation aux supports

Les objectifs et les résultats attendus restent les mêmes sur ordinateur et sur mobile. Les interactions doivent rester accessibles dans les dispositions prévues par les maquettes.

| Écran               | Ordinateur                                                           | Mobile                                                            |
| ------------------- | -------------------------------------------------------------------- | ----------------------------------------------------------------- |
| Programme           | Cartes disposées en grille.                                          | Cartes disposées verticalement.                                   |
| Détail d’une soirée | Spectacles et bloc de billets visibles dans des colonnes distinctes. | Bloc de billets et spectacles se succèdent verticalement.         |
| Connexion           | Formulaires de connexion et d’inscription côte à côte.               | Connexion et accès à l’inscription adaptés à une largeur réduite. |
| Panier              | Articles présentés dans un tableau avec récapitulatif latéral.       | Articles présentés en blocs, suivis du récapitulatif.             |
| Paiement            | Formulaire et récapitulatif côte à côte.                             | Récapitulatif et formulaire disposés verticalement.               |
| Billets             | Liste et billet individuel adaptés à l’écran.                        | Même contenu, avec boutons accessibles et informations lisibles.  |
| Backoffice          | Tableau des soirées.                                                 | Blocs par soirée et navigation adaptée.                           |
