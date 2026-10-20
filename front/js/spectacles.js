const API_URL = '/api';

const liste = document.getElementById('spectacles-liste');
const message = document.getElementById('spectacles-message');
const compteur = document.getElementById('spectacles-compteur');
const resume = document.getElementById('programme-resume');
const template = document.getElementById('spectacle-template');
const filtreDate = document.getElementById('filtre-date');

let datesInitialisees = false;
let requeteCourante = 0;

function versDate(date) {
    return new Date(`${date}T00:00`);
}




function afficherResume(spectacles) {
    const dates = [...new Set(spectacles.map((spectacle) => spectacle.date))].sort();
    const debut = versDate(dates[0]);
    const fin = versDate(dates[dates.length - 1]);

    const memeMois = debut.getMonth() === fin.getMonth() && debut.getFullYear() === fin.getFullYear();
    const debutTexte = memeMois
        ? debut.getDate()
        : debut.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' });
    const finTexte = fin.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });

    const soirees = dates.length > 1 ? `${dates.length} soirées` : '1 soirée';
    resume.textContent = `Du ${debutTexte} au ${finTexte} · ${soirees} · Nancy`;
}

function creerSpectacle(spectacle) {
    const element = template.content.cloneNode(true);

    const img = element.querySelector('img');
    if (spectacle.image) {
        img.src = spectacle.image;
        img.alt = spectacle.titre;
    } else {
        img.remove();
    }

    const style = element.querySelector('.badge');
    if (spectacle.style) {
        style.textContent = spectacle.style;
    } else {
        style.remove();
    }

    element.querySelector('.nom').textContent = spectacle.titre;

    const date = element.querySelector('.date');
    date.dateTime = spectacle.date;
    date.textContent = spectacle.date;

    const horaire = element.querySelector('.horaire');
    horaire.dateTime = spectacle.horaire;
    horaire.textContent = spectacle.horaire;

    return element;
}

function remplirDates(spectacles) {
    const dates = [...new Set(
        spectacles.map((spectacle) => spectacle.date)
    )].sort();

    const options = dates.map((date) => {
        const option = document.createElement('option');

        option.value = date;
        option.textContent = versDate(date).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });

        return option;
    });

    filtreDate.replaceChildren(
        new Option('Toutes les dates', ''),
        ...options
    );
}

async function chargerSpectacles(date = '') {
    const numeroRequete = ++requeteCourante;

    message.hidden = false;
    message.textContent = 'Chargement des spectacles...';
    compteur.textContent = '';
    liste.replaceChildren();

    const parametres = new URLSearchParams();

    if (date !== '') {
        parametres.set('date', date);
    }

    const recherche = parametres.toString();
    const url = `${API_URL}/spectacles${recherche ? `?${recherche}` : ''}`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const spectacles = await response.json();

        if (numeroRequete !== requeteCourante) {
            return;
        }

        if (!datesInitialisees && date === '') {
            remplirDates(spectacles);

            if (spectacles.length > 0) {
                afficherResume(spectacles);
            }

            datesInitialisees = true;
        }

        compteur.textContent =
            `${spectacles.length} spectacle${spectacles.length > 1 ? 's' : ''}`;

        if (spectacles.length === 0) {
            message.textContent = date
                ? 'Aucun spectacle pour cette date.'
                : 'Aucun spectacle programmé pour le moment.';

            return;
        }

        message.hidden = true;
        liste.replaceChildren(...spectacles.map(creerSpectacle));
    } catch (e) {
        if (numeroRequete !== requeteCourante) {
            return;
        }

        message.hidden = false;
        message.textContent = 'Impossible de charger les spectacles.';
        console.error(e);
    }
}

filtreDate.addEventListener('change', () => {
    chargerSpectacles(filtreDate.value);
});

chargerSpectacles();
