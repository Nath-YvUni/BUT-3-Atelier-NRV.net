import {
    ErreurApi,
    lireJeton,
    requeteApi,
} from './auth.js';

const NOMBRE_SOIREES = 3;

const section = document.getElementById('ajout-pass');
const boutonChoisir = document.getElementById('ajout-pass-choisir');
const formulaire = document.getElementById('form-ajout-pass');
const liste = document.getElementById('ajout-pass-liste');
const compteur = document.getElementById('ajout-pass-compteur');
const selectTarif = document.getElementById('ajout-pass-tarif');
const boutonAjouter = formulaire.querySelector('button[type="submit"]');
const message = document.getElementById('ajout-pass-message');
const optionTemplate = document.getElementById('ajout-pass-option-template');

const libellesCategories = {
    normal: 'Tarif normal',
    reduit: 'Tarif réduit',
};

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

let soireeCourante = null;
let chargement = null;

function afficherMessage(texte, erreur = false) {
    message.textContent = texte;
    message.hidden = texte === '';
    message.classList.toggle('auth-message--erreur', erreur);
}

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;

    location.assign(`connexion.html?retour=${encodeURIComponent(retour)}`);
}

function formaterDate(dateHeure) {
    return new Date(dateHeure).toLocaleString('fr-FR', {
        weekday: 'short',
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function casesCochees() {
    return [...liste.querySelectorAll('input:checked')];
}

function mettreAJourSelection() {
    const nombre = casesCochees().length;

    compteur.textContent = `${nombre} / ${NOMBRE_SOIREES}`;

    liste.querySelectorAll('input').forEach((caseSoiree) => {
        caseSoiree.disabled = caseSoiree.dataset.complete === 'true'
            || (!caseSoiree.checked && nombre >= NOMBRE_SOIREES);
    });

    boutonAjouter.disabled = nombre !== NOMBRE_SOIREES;
}

function creerOption(soiree) {
    const element = optionTemplate.content.cloneNode(true);
    const caseSoiree = element.querySelector('input');

    caseSoiree.value = soiree.id;
    caseSoiree.dataset.complete = String(soiree.complete);
    caseSoiree.checked = soiree.id === soireeCourante && !soiree.complete;

    element.querySelector('.ajout-pass-nom').textContent = soiree.nom;
    element.querySelector('.ajout-pass-date').textContent = formaterDate(soiree.date_heure_debut);

    if (!soiree.complete) {
        element.querySelector('.badge').remove();
    }

    return element;
}

function remplirTarifs(tarifs) {
    selectTarif.replaceChildren(
        ...tarifs.map((tarif) => new Option(
            `${libellesCategories[tarif.categorie] ?? tarif.categorie} · ${euros.format(Number(tarif.prix))} par soirée`,
            tarif.id
        ))
    );
}

function texteConfirmation(panier) {
    const pass = panier.pass.at(-1);
    const billets = panier.nombre_billets > 1
        ? `${panier.nombre_billets} billets`
        : `${panier.nombre_billets} billet`;

    return `Pass 3 jours ajouté au panier (${euros.format(Number(pass.sous_total))}). `
        + `Votre panier : ${billets}, ${euros.format(Number(panier.montant_total))}.`;
}

function reinitialiserSelection() {
    liste.querySelectorAll('input').forEach((caseSoiree) => {
        caseSoiree.checked = Number(caseSoiree.value) === soireeCourante
            && caseSoiree.dataset.complete !== 'true';
    });

    mettreAJourSelection();
}

async function chargerChoix() {
    const [soirees, tarifs] = await Promise.all([
        requeteApi('/soirees'),
        requeteApi('/tarifs'),
    ]);

    liste.replaceChildren(...soirees.data.map(creerOption));
    remplirTarifs(tarifs.data);
    mettreAJourSelection();
}

document.addEventListener('nrv-soiree-chargee', (evenement) => {
    soireeCourante = evenement.detail.id;
    section.hidden = false;
});

boutonChoisir.addEventListener('click', () => {
    const ouvrir = formulaire.hidden;

    formulaire.hidden = !ouvrir;
    boutonChoisir.setAttribute('aria-expanded', String(ouvrir));

    if (!ouvrir || chargement !== null) {
        return;
    }

    afficherMessage('');

    chargement = chargerChoix().catch(() => {
        chargement = null;
        formulaire.hidden = true;
        boutonChoisir.setAttribute('aria-expanded', 'false');
        afficherMessage('Impossible de charger les soirées du pass.', true);
    });
});

liste.addEventListener('change', mettreAJourSelection);

formulaire.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    if (!lireJeton()) {
        redirigerVersConnexion();
        return;
    }

    afficherMessage('');
    boutonAjouter.disabled = true;

    try {
        const resultat = await requeteApi('/panier/pass', {
            methode: 'POST',
            authentifie: true,
            donnees: {
                soiree_ids: casesCochees().map((caseSoiree) => Number(caseSoiree.value)),
                tarif_id: Number(selectTarif.value),
            },
        });

        window.dispatchEvent(new Event('nrv-panier-modifie'));
        afficherMessage(texteConfirmation(resultat.data));
        reinitialiserSelection();
    } catch (erreur) {
        if (erreur instanceof ErreurApi && erreur.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        afficherMessage(erreur.message, true);
        mettreAJourSelection();
    }
});
