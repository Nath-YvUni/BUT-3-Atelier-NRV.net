
import {
    lireJeton,
    requeteApi,
} from './auth.js';

const message = document.getElementById('billets-message');
const sectionBillets = document.getElementById('billets-section');
const listeBillets = document.getElementById('billets-liste');
const billetTemplate = document.getElementById('billet-template');

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

const libellesCategories = {
    normal: 'Tarif normal',
    reduit: 'Tarif réduit',
};

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

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;
    location.assign(`connexion.html?retour=${encodeURIComponent(retour)}`);
}


function creerBillet(billet) {
    const element = billetTemplate.content.cloneNode(true);
    const carte = element.querySelector('.billet-lien');

    element.querySelector('.billet-nom').textContent =
        billet.soiree.nom;

    element.querySelector('.billet-date').textContent =
        formaterDate(billet.soiree.date + 'T' + billet.soiree.horaire);

    element.querySelector('.billet-tarif').textContent =
        libellesCategories[billet.tarif.categorie]
        ?? billet.tarif.categorie;

    element.querySelector('.billet-quantite').textContent =
        billet.acheteur;

    element.querySelector('.billet-prix').textContent =
        formaterMontant(billet.prix);

    element.querySelector('.billet-reference').textContent =
        `Billet : ${billet.reference}`;

    carte.href = `billet.html?token=${encodeURIComponent(billet.token)}`;

    return element;
}

async function chargerBillets() {
    if (!lireJeton()) {
        redirigerVersConnexion();
        return;
    }

    message.hidden = false;
    message.textContent = 'Chargement de vos billets...';
    sectionBillets.hidden = true;
    listeBillets.replaceChildren();

    try {
        const resultat = await requeteApi('/billets', {
            authentifie: true,
        });

        console.log('Billets récupérés :', resultat);
        

        const billets = resultat.data;

        if (!Array.isArray(billets)) {
            throw new Error('Format de réponse invalide.');
        }

        if (billets.length === 0) {
            message.textContent = 'Vous ne possédez aucun billet.';
            return;
        }

        listeBillets.replaceChildren(
            ...billets.map(creerBillet)
        );

        message.hidden = true;
        sectionBillets.hidden = false;


    } catch (erreur) {
        if (erreur.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        message.hidden = false;
        message.textContent = 'Impossible de charger vos billets.';
        console.error(erreur);
    }
}

window.addEventListener('nrv-auth-change', () => {
    if (lireJeton()) {
        chargerBillets();
    } else {
        redirigerVersConnexion();
    }
});

chargerBillets();
