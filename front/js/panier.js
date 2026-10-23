import {
    lireJeton,
    requeteApi,
} from './auth.js';

const message = document.getElementById('panier-message');
const messageVerrouille = document.getElementById('panier-verrouille');
const sectionBillets = document.getElementById('panier-billets');
const listeBillets = document.getElementById('panier-billets-liste');
const sectionPass = document.getElementById('panier-pass');
const listePass = document.getElementById('panier-pass-liste');
const sectionTotal = document.getElementById('panier-total');
const nombreBillets = document.getElementById('panier-nombre-billets');
const montantTotal = document.getElementById('panier-montant-total');
const ligneTemplate = document.getElementById('panier-ligne-template');
const passTemplate = document.getElementById('panier-pass-template');

const libellesCategories = {
    normal: 'Tarif normal',
    reduit: 'Tarif réduit',
};

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

let requeteCourante = 0;

function formaterMontant(valeur) {
    return euros.format(Number(valeur));
}

function formaterDate(dateHeure) {
    return new Date(dateHeure).toLocaleString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function libelleTarif(tarif) {
    return libellesCategories[tarif.categorie] ?? tarif.categorie;
}

function texteBillets(nombre) {
    return nombre > 1 ? `${nombre} billets` : `${nombre} billet`;
}

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;

    location.assign(`connexion.html?retour=${encodeURIComponent(retour)}`);
}

function creerLigne(ligne) {
    const element = ligneTemplate.content.cloneNode(true);

    element.querySelector('.panier-element-nom').textContent =
        ligne.soiree.nom;

    element.querySelector('.panier-element-date').textContent =
        formaterDate(ligne.soiree.date_heure_debut);

    element.querySelector('.panier-element-detail').textContent =
        `${libelleTarif(ligne.tarif)} · ${formaterMontant(ligne.tarif.prix)} × ${ligne.quantite}`;

    element.querySelector('.panier-element-montant').textContent =
        formaterMontant(ligne.sous_total);

    const input = element.querySelector('.panier-quantite-input');
    const bouton = element.querySelector('.panier-quantite-enregistrer');

    input.value = ligne.quantite;

    bouton.addEventListener('click', async () => {
        const quantite = Number(input.value);

        bouton.disabled = true;

        try {
            await modifierQuantite(ligne.id, quantite);
        } catch (erreur) {
            message.hidden = false;
            message.textContent = erreur.message;

            if (erreur.statut === 401) {
                redirigerVersConnexion();
            }

            console.error(erreur);
        } finally {
            bouton.disabled = false;
        }
    });

    return element;
}

function creerPass(pass) {
    const element = passTemplate.content.cloneNode(true);

    element.querySelector('.panier-pass-tarif').textContent =
        `${libelleTarif(pass.tarif)} · ${formaterMontant(pass.tarif.prix)} par soirée`;

    const soirees = element.querySelector('.panier-pass-soirees');

    soirees.replaceChildren(...pass.soirees.map((soiree) => {
        const li = document.createElement('li');

        li.textContent = `${soiree.nom} · ${formaterDate(soiree.date_heure_debut)}`;

        return li;
    }));

    element.querySelector('.panier-pass-avant').textContent =
        `Avant remise : ${formaterMontant(pass.montant_avant_remise)}`;
    element.querySelector('.panier-pass-remise').textContent =
        `Remise de ${Number(pass.taux_remise)} % : −${formaterMontant(pass.montant_remise)}`;
    element.querySelector('.panier-element-montant').textContent =
        formaterMontant(pass.sous_total);

    return element;
}

function afficherPanier(panier) {
    const lignes = panier.lignes ?? [];
    const pass = panier.pass ?? [];

    if (lignes.length === 0 && pass.length === 0) {
        message.hidden = false;
        message.replaceChildren(
            'Votre panier est vide. ',
            Object.assign(document.createElement('a'), {
                href: 'index.html',
                textContent: 'Découvrir le programme',
            })
        );

        return;
    }

    message.hidden = true;
    messageVerrouille.hidden = panier.modifiable !== false;

    sectionBillets.hidden = lignes.length === 0;
    listeBillets.replaceChildren(...lignes.map(creerLigne));

    listeBillets.querySelectorAll('.panier-quantite').forEach((element) => {
        element.hidden = panier.modifiable === false;
    });

    sectionPass.hidden = pass.length === 0;
    listePass.replaceChildren(...pass.map(creerPass));

    nombreBillets.textContent = String(panier.nombre_billets);
    montantTotal.textContent = formaterMontant(panier.montant_total);
    sectionTotal.hidden = false;
}

function masquerContenu() {
    messageVerrouille.hidden = true;
    sectionBillets.hidden = true;
    sectionPass.hidden = true;
    sectionTotal.hidden = true;
}

async function chargerPanier() {
    const numeroRequete = ++requeteCourante;

    if (!lireJeton()) {
        redirigerVersConnexion();
        return;
    }

    masquerContenu();
    message.hidden = false;
    message.textContent = 'Chargement du panier...';

    try {
        const resultat = await requeteApi('/panier', {
            authentifie: true,
        });
        

        if (numeroRequete !== requeteCourante) {
            return;
        }

        if (
            resultat.data === null
            || typeof resultat.data !== 'object'
            || Array.isArray(resultat.data)
        ) {
            throw new Error('Réponse invalide : panier absent.');
        }

        afficherPanier(resultat.data);
        document.dispatchEvent(new CustomEvent('nrv-panier-charge', { detail: resultat.data }));
    } catch (erreur) {
        if (numeroRequete !== requeteCourante) {
            return;
        }

        if (erreur.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        message.hidden = false;
        message.textContent = 'Impossible de charger le panier.';
        console.error(erreur);
    }
}

async function modifierQuantite(ligneId, quantite) {
    if (!Number.isInteger(quantite) || quantite < 1) {
        throw new Error('La quantité doit être un entier supérieur à 0.');
    }

    await requeteApi(`/panier/lignes/${ligneId}`, {
        methode: 'PATCH',
        authentifie: true,
        donnees: {
            quantite: quantite,
        },
    });

    await chargerPanier();
}

window.addEventListener('nrv-auth-change', () => {
    if (lireJeton()) {
        chargerPanier();
    } else {
        redirigerVersConnexion();
    }
});


chargerPanier();
