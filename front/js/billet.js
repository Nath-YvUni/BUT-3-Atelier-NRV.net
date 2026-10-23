
import { requeteApi } from './auth.js';

const message = document.getElementById('billet-message');
const detail = document.getElementById('billet-detail');
const boutonImprimer = document.getElementById('billet-imprimer');

const euros = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
});

function formaterDate(soiree) {
    return new Date(
        soiree.date + 'T' + soiree.horaire
    ).toLocaleString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

async function chargerBillet() {
    const parametres = new URLSearchParams(location.search);
    const token = parametres.get('token');

    if (!token || !/^[0-9a-f]{64}$/.test(token)) {
        message.textContent = 'Token de billet invalide.';
        return;
    }

    try {
        const resultat = await requeteApi(`/billets/${encodeURIComponent(token)}`, {
            authentifie: true,
        });

        const billet = resultat.data;

        if (!billet || !billet.soiree || !billet.tarif) {
            throw new Error('Réponse invalide.');
        }

        document.getElementById('billet-nom').textContent =
            billet.soiree.nom;

        document.getElementById('billet-date').textContent =
            formaterDate(billet.soiree);

        document.getElementById('billet-acheteur').textContent =
            `Acheteur : ${billet.acheteur}`;

        document.getElementById('billet-tarif').textContent =
            `Tarif : ${billet.tarif.categorie}`;

        document.getElementById('billet-prix').textContent =
            `Prix : ${euros.format(Number(billet.prix))}`;

        document.getElementById('billet-reference').textContent =
            `Référence : ${billet.reference}`;

        new QRCode(document.getElementById('billet-qrcode'), {
            text: billet.token,
            width: 180,
            height: 180,
            correctLevel: QRCode.CorrectLevel.M,
        });

        message.hidden = true;
        detail.hidden = false;
        boutonImprimer.hidden = false;

    } catch (erreur) {
        message.textContent = 'Impossible de charger ce billet.';
        console.error(erreur);
    }
}

boutonImprimer.addEventListener('click', function () {
    window.print();
});

chargerBillet();
