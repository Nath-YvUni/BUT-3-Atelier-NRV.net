import {
    ErreurApi,
    lireJeton,
    requeteApi,
} from './auth.js';

const section = document.getElementById('ajout-panier');
const formulaire = document.getElementById('form-ajout-panier');
const selectTarif = document.getElementById('ajout-panier-tarif');
const champQuantite = document.getElementById('ajout-panier-quantite');
const message = document.getElementById('ajout-panier-message');

const libellesCategories = {
    normal: 'Tarif normal',
    reduit: 'Tarif réduit',
};

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

let soireeId = null;

function afficherMessage(texte, erreur = false) {
    message.textContent = texte;
    message.hidden = texte === '';
    message.classList.toggle('auth-message--erreur', erreur);
}

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;

    location.assign(`connexion.html?retour=${encodeURIComponent(retour)}`);
}

function remplirTarifs(tarifs) {
    selectTarif.replaceChildren(
        ...tarifs.map((tarif) => new Option(
            `${libellesCategories[tarif.categorie] ?? tarif.categorie} · ${euros.format(Number(tarif.prix))}`,
            tarif.id
        ))
    );
}

function texteConfirmation(quantite, panier) {
    const ajout = quantite > 1
        ? `${quantite} billets ajoutés au panier.`
        : '1 billet ajouté au panier.';

    const contenu = panier.nombre_billets > 1
        ? `${panier.nombre_billets} billets`
        : `${panier.nombre_billets} billet`;

    return `${ajout} Votre panier : ${contenu}, ${euros.format(Number(panier.montant_total))}.`;
}

document.addEventListener('nrv-soiree-chargee', async (evenement) => {
    soireeId = evenement.detail.id;
    section.hidden = false;

    try {
        const resultat = await requeteApi('/tarifs');

        remplirTarifs(resultat.data);
    } catch {
        formulaire.hidden = true;
        afficherMessage('Impossible de charger les tarifs.', true);
    }
});

formulaire.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    if (!lireJeton()) {
        redirigerVersConnexion();
        return;
    }

    const bouton = formulaire.querySelector('button[type="submit"]');
    const quantite = Number(champQuantite.value);

    afficherMessage('');
    bouton.disabled = true;

    try {
        const resultat = await requeteApi('/panier/lignes', {
            methode: 'POST',
            authentifie: true,
            donnees: {
                soiree_id: soireeId,
                tarif_id: Number(selectTarif.value),
                quantite,
            },
        });

        afficherMessage(texteConfirmation(quantite, resultat.data));
        champQuantite.value = '1';
    } catch (erreur) {
        if (erreur instanceof ErreurApi && erreur.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        afficherMessage(erreur.message, true);
    } finally {
        bouton.disabled = false;
    }
});
