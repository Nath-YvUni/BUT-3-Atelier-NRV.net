import {
    ErreurApi,
    lireJeton,
    requeteApi,
} from './auth.js';

const message = document.getElementById('paiement-message');
const contenu = document.getElementById('paiement-contenu');
const formulaire = document.getElementById('form-paiement');
const champNumero = document.getElementById('paiement-numero');
const champExpiration = document.getElementById('paiement-expiration');
const champCryptogramme = document.getElementById('paiement-cryptogramme');
const bouton = document.getElementById('paiement-bouton');
const reference = document.getElementById('paiement-reference');
const liste = document.getElementById('paiement-liste');
const total = document.getElementById('paiement-total');
const erreur = document.getElementById('paiement-erreur');
const confirmation = document.getElementById('paiement-confirmation');
const texteConfirmation = document.getElementById('paiement-confirmation-texte');

const libellesCategories = {
    normal: 'normal',
    reduit: 'réduit',
};

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

const commandeId = new URLSearchParams(location.search).get('commande');

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;

    location.assign(`connexion.html?retour=${encodeURIComponent(retour)}`);
}

function creerElement(libelle, montant) {
    const li = document.createElement('li');
    const texte = document.createElement('span');
    const valeur = document.createElement('span');

    texte.textContent = libelle;
    valeur.textContent = euros.format(Number(montant));
    li.append(texte, valeur);

    return li;
}

function afficherCommande(commande) {
    reference.textContent = `Commande n° ${commande.reference}`;

    liste.replaceChildren(
        ...commande.contenu.lignes.map((ligne) => creerElement(
            `${ligne.nom_soiree} · ${libellesCategories[ligne.categorie]} ×${ligne.quantite}`,
            ligne.sous_total
        )),
        ...commande.contenu.pass.map((pass) => creerElement(
            `Pass 3 jours · ${libellesCategories[pass.categorie]} ×1`,
            pass.sous_total
        ))
    );

    total.textContent = euros.format(Number(commande.montant_total));
    bouton.textContent = `Payer ${euros.format(Number(commande.montant_total))}`;
}

function afficherErreur(texte) {
    erreur.textContent = texte;
    erreur.hidden = texte === '';
}

async function chargerCommande() {
    if (!lireJeton()) {
        redirigerVersConnexion();
        return;
    }

    try {
        const resultat = await requeteApi(`/commandes/${encodeURIComponent(commandeId ?? '')}`, {
            authentifie: true,
        });

        const commande = resultat.data;

        if (commande.statut !== 'en_attente') {
            message.textContent = commande.statut === 'payee'
                ? `La commande ${commande.reference} est déjà payée.`
                : `La commande ${commande.reference} a été refusée.`;
            return;
        }

        afficherCommande(commande);
        message.hidden = true;
        contenu.hidden = false;
    } catch (e) {
        if (e instanceof ErreurApi && e.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        message.textContent = e instanceof ErreurApi && e.statut === 404
            ? 'Cette commande n’existe pas.'
            : 'Impossible de charger la commande.';
    }
}

function expiration(valeur) {
    const correspondance = /^(\d{2})\/(\d{2})$/.exec(valeur.trim());

    return correspondance ? `20${correspondance[2]}-${correspondance[1]}` : valeur;
}

formulaire.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    afficherErreur('');
    bouton.disabled = true;

    try {
        const resultat = await requeteApi(`/commandes/${encodeURIComponent(commandeId)}/paiement`, {
            methode: 'POST',
            authentifie: true,
            donnees: {
                numero_carte: champNumero.value.replace(/\s/g, ''),
                expiration: expiration(champExpiration.value),
                cryptogramme: champCryptogramme.value.trim(),
            },
        });

        const paiement = resultat.data;

        contenu.hidden = true;
        texteConfirmation.textContent =
            `Ton paiement de ${euros.format(Number(paiement.montant_total))} est confirmé. `
            + `Commande n° ${paiement.reference}.`;
        confirmation.hidden = false;
    } catch (e) {
        if (e instanceof ErreurApi && e.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        if (e instanceof ErreurApi && e.statut === 409) {
            contenu.hidden = true;
        }

        afficherErreur(e.message);
    } finally {
        bouton.disabled = false;
    }
});

chargerCommande();
