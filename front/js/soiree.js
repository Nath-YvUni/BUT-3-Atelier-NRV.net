const API_URL = '/api';

const message = document.getElementById('soiree-message');
const soireeTemplate = document.getElementById('soiree-template');
const boite = document.getElementById('soiree');
const spectacleTemplate = document.getElementById('soiree-spectacle-template');

function creerSoiree(soiree) {
    const element = soireeTemplate.content.cloneNode(true);

    element.querySelector('.nom').textContent = soiree.nom;
    element.querySelector('.thematique').textContent = 'Thématique : ' + soiree.thematique;
    element.querySelector('.date').textContent = 'Date : ' + soiree.date_heure_debut;
    element.querySelector('.nom-lieu').textContent = soiree.nom_lieu;
    element.querySelector('.capacite-valeur').textContent = soiree.capacite;
    element.querySelector('.tarif-valeur').textContent = soiree.tarif;
    element.querySelector('.artistes-soiree').textContent =
        (soiree.artistes ?? []).map((a) => a.nom).join(', ');

    const ul = element.querySelector('.liste-spectacles');
    (soiree.liste ?? []).forEach((spectacle) => ul.appendChild(creerSpectacle(spectacle)));

    return element;
}

function creerSpectacle(spectacle) {
    const li = spectacleTemplate.content.cloneNode(true);

    li.querySelector('.titre').textContent += spectacle.titre;
    li.querySelector('.artistes').textContent += spectacle.artistes ?? 'non renseignés';
    li.querySelector('.styles').textContent += spectacle.styles ?? 'non renseignés';
    li.querySelector('.description').textContent += spectacle.description;

    const video = li.querySelector('.video');
    if (spectacle.url_video) {
        video.href = spectacle.url_video;
    } else {
        video.remove();
    }

    return li;
}

function lireIdentifiant() {
    const id = new URLSearchParams(window.location.search).get('id');
    return /^\d+$/.test(id ?? '') ? id : null;
}

async function chargerSoiree(id) {
    try {
        const response = await fetch(`${API_URL}/soirees/${encodeURIComponent(id)}`);

        if (response.status === 404) {
            message.textContent = "Cette soirée n'existe pas.";
            return;
        }

        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const contenu = await response.json();

        if (
            contenu.data === null
            || typeof contenu.data !== 'object'
            || Array.isArray(contenu.data)
        ) {
            throw new Error('Réponse invalide : détail de soirée absent.');
        }

        const soiree = contenu.data;

        message.remove();
        boite.replaceChildren(creerSoiree(soiree));
        document.dispatchEvent(new CustomEvent('nrv-soiree-chargee', { detail: soiree }));
    } catch (e) {
        message.textContent = 'Impossible de charger la soirée.';
        console.error(e);
    }
}

const id = lireIdentifiant();

if (id === null) {
    message.textContent = "Cette soirée n'existe pas.";
} else {
    chargerSoiree(id);
}
