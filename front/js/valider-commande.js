import {
    ErreurApi,
    requeteApi,
} from './auth.js';

const validation = document.getElementById('panier-validation');
const bouton = document.getElementById('panier-valider');
const message = document.getElementById('panier-valider-message');
const lienPaiement = document.getElementById('panier-payer');

function afficherMessage(texte, erreur = false) {
    message.textContent = texte;
    message.hidden = texte === '';
    message.classList.toggle('auth-message--erreur', erreur);
}

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;

    location.assign(`connexion.html?retour=${encodeURIComponent(retour)}`);
}

document.addEventListener('nrv-panier-charge', (evenement) => {
    const panier = evenement.detail;

    validation.hidden = panier.modifiable === false || panier.nombre_billets === 0;

    if (panier.commande_en_attente_id !== null) {
        lienPaiement.href = `paiement.html?commande=${panier.commande_en_attente_id}`;
    }
});

bouton.addEventListener('click', async () => {
    afficherMessage('');
    bouton.disabled = true;

    try {
        const resultat = await requeteApi('/commandes', {
            methode: 'POST',
            authentifie: true,
        });

        location.assign(`paiement.html?commande=${resultat.data.id}`);
    } catch (erreur) {
        if (erreur instanceof ErreurApi && erreur.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        afficherMessage(erreur.message, true);
        bouton.disabled = false;
    }
});
