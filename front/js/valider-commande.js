import {
    ErreurApi,
    requeteApi,
} from './auth.js';

const validation = document.getElementById('panier-validation');
const bouton = document.getElementById('panier-valider');
const message = document.getElementById('panier-valider-message');

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

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
});

bouton.addEventListener('click', async () => {
    afficherMessage('');
    bouton.disabled = true;

    try {
        const resultat = await requeteApi('/commandes', {
            methode: 'POST',
            authentifie: true,
        });

        const commande = resultat.data;

        afficherMessage(
            `Commande ${commande.reference} créée : `
            + `${euros.format(Number(commande.montant_total))} en attente de paiement.`
        );

        document.dispatchEvent(new CustomEvent('nrv-commande-creee', { detail: commande }));
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
