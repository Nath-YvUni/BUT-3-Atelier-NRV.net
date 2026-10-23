import {
    lireJeton,
    requeteApi,
} from './auth.js';

const message = document.getElementById('backoffice-message');
const contenu = document.getElementById('backoffice-contenu');
const tableau = document.getElementById('backoffice-tableau');
const listeSoirees = document.getElementById('backoffice-soirees');
const template = document.getElementById('backoffice-soiree-template');
const boutonActualiser = document.getElementById('backoffice-actualiser');

const totalBillets = document.getElementById('backoffice-billets');
const recetteTotale = document.getElementById('backoffice-recette');
const remplissageGlobal = document.getElementById('backoffice-remplissage');

const nombres = new Intl.NumberFormat('fr-FR');

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

const pourcentages = new Intl.NumberFormat('fr-FR', {
    style: 'percent',
    maximumFractionDigits: 1,
});

const dates = new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

let requeteCourante = 0;

function redirigerVersConnexion() {
    const retour = location.pathname + location.search;

    location.replace(
        `connexion.html?retour=${encodeURIComponent(retour)}`
    );
}

function masquerContenu() {
    contenu.hidden = true;
    listeSoirees.replaceChildren();

    totalBillets.textContent = '';
    recetteTotale.textContent = '';
    remplissageGlobal.textContent = '';
}

function creerLigne(soiree, datesParId) {
    const ligne = template.content.cloneNode(true);

    const lien = ligne.querySelector('.backoffice-soiree-nom');
    lien.textContent = soiree.nom;
    lien.href = `soiree.html?id=${encodeURIComponent(soiree.id)}`;

    const dateHeure = datesParId.get(soiree.id);

    ligne.querySelector('.backoffice-date').textContent =
        dateHeure
            ? dates.format(new Date(dateHeure))
            : 'Non renseignée';

    const jauge = ligne.querySelector('progress');

    jauge.max = Math.max(1, soiree.capacite);
    jauge.value = Math.min(
        soiree.places_vendues,
        soiree.capacite
    );

    jauge.setAttribute(
        'aria-label',
        `Places vendues pour ${soiree.nom}`
    );

    ligne.querySelector('.backoffice-places').textContent =
        `${nombres.format(soiree.places_vendues)} / ${
            nombres.format(soiree.capacite)
        }`;

    const complet = ligne.querySelector('.backoffice-complet');

    complet.hidden = soiree.places_disponibles > 0;
    complet.textContent = soiree.capacite === 0
        ? 'Indisponible'
        : 'Complet';

    ligne.querySelector('.backoffice-disponibles').textContent =
        nombres.format(soiree.places_disponibles);

    ligne.querySelector('.backoffice-soiree-recette').textContent =
        euros.format(Number(soiree.recette));

    return ligne;
}

function afficherStatistiques(statistiques, soireesProgramme) {
    const datesParId = new Map(
        soireesProgramme.map((soiree) => [
            soiree.id,
            soiree.date_heure_debut,
        ])
    );

    totalBillets.textContent = nombres.format(
        statistiques.total_billets_vendus
    );

    recetteTotale.textContent = euros.format(
        Number(statistiques.recette_totale)
    );

    const capaciteTotale = statistiques.soirees.reduce(
        (total, soiree) => total + soiree.capacite,
        0
    );

    remplissageGlobal.textContent = capaciteTotale > 0
        ? pourcentages.format(
            statistiques.total_billets_vendus / capaciteTotale
        )
        : 'Non applicable';

    listeSoirees.replaceChildren(
        ...statistiques.soirees.map(
            (soiree) => creerLigne(soiree, datesParId)
        )
    );

    const listeVide = statistiques.soirees.length === 0;

    tableau.hidden = listeVide;
    contenu.hidden = false;

    message.hidden = !listeVide;
    message.textContent = listeVide
        ? 'Aucune soirée n’est programmée.'
        : '';
}

async function chargerStatistiques() {
    const numeroRequete = ++requeteCourante;

    masquerContenu();

    message.hidden = false;
    message.textContent = 'Chargement des statistiques...';

    if (!lireJeton()) {
        redirigerVersConnexion();
        return;
    }

    boutonActualiser.disabled = true;
    boutonActualiser.textContent = 'Chargement...';

    try {
        const [resultatStatistiques, resultatSoirees] = await Promise.all([
            requeteApi('/admin/statistiques', {
                authentifie: true,
            }),
            requeteApi('/soirees'),
        ]);

        if (numeroRequete !== requeteCourante) {
            return;
        }

        if (
            !resultatStatistiques.data
            || !Array.isArray(resultatStatistiques.data.soirees)
            || !Array.isArray(resultatSoirees.data)
        ) {
            throw new Error('Réponse invalide du serveur.');
        }

        afficherStatistiques(
            resultatStatistiques.data,
            resultatSoirees.data
        );
    } catch (erreur) {
        if (numeroRequete !== requeteCourante) {
            return;
        }

        if (erreur.statut === 401) {
            redirigerVersConnexion();
            return;
        }

        masquerContenu();

        message.hidden = false;
        message.textContent = erreur.statut === 403
            ? 'Cet espace est réservé aux gestionnaires.'
            : erreur.message;
    } finally {
        if (numeroRequete === requeteCourante) {
            boutonActualiser.disabled = false;
            boutonActualiser.textContent = 'Actualiser';
        }
    }
}

boutonActualiser.addEventListener('click', chargerStatistiques);

window.addEventListener('nrv-auth-change', chargerStatistiques);

window.addEventListener('pagehide', () => {
    ++requeteCourante;
    masquerContenu();
});

window.addEventListener('pageshow', (evenement) => {
    if (evenement.persisted) {
        chargerStatistiques();
    }
});

chargerStatistiques();