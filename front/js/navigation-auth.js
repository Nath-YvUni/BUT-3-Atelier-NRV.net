import {
    deconnecter,
    lireJeton,
    requeteApi,
} from './auth.js';

function initialiserNavigation() {
    const navigation = document.querySelector('[data-navigation-auth]');

    if (!navigation) {
        return;
    }

    const invite = navigation.querySelector('[data-auth-invite]');
    const connecte = navigation.querySelector('[data-auth-connecte]');
    const nom = navigation.querySelector('[data-auth-nom]');
    const message = navigation.querySelector('[data-auth-message]');
    const boutonDeconnexion = navigation.querySelector(
        '[data-auth-deconnexion]'
    );
    const lienConnexion = navigation.querySelector(
        '[data-lien-connexion]'
    );

    const retour = location.pathname.endsWith('/connexion.html')
        ? 'index.html'
        : location.pathname + location.search;

    lienConnexion.href =
        `connexion.html?retour=${encodeURIComponent(retour)}`;

    let verificationCourante = 0;

    async function actualiserNavigation() {
        const numeroVerification = ++verificationCourante;

        invite.hidden = true;
        connecte.hidden = true;
        message.hidden = true;
        nom.textContent = '';

        if (!lireJeton()) {
            invite.hidden = false;
            return;
        }

        message.hidden = false;
        message.textContent = 'Vérification du compte…';

        try {
            const resultat = await requeteApi('/me', {
                authentifie: true,
            });

            if (numeroVerification !== verificationCourante) {
                return;
            }

            const profil = resultat.data;

            if (!profil || typeof profil.nom !== 'string') {
                throw new Error('Réponse du profil invalide.');
            }

            nom.textContent = profil.nom;
            connecte.hidden = false;
            message.hidden = true;
        } catch (erreur) {
            if (numeroVerification !== verificationCourante) {
                return;
            }

            invite.hidden = false;
            message.hidden = false;
            message.textContent = erreur.statut === 401
                ? 'Votre session a expiré. Reconnectez-vous.'
                : erreur.message;
        }
    }

    boutonDeconnexion.addEventListener('click', deconnecter);

    window.addEventListener(
        'nrv-auth-change',
        actualiserNavigation
    );

    actualiserNavigation();
}

initialiserNavigation();