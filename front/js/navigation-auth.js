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

    const menu = navigation.querySelector('nav');
    const invite = navigation.querySelector('[data-auth-invite]');
    const connecte = navigation.querySelector('[data-auth-connecte]');
    const nom = navigation.querySelector('[data-auth-nom]');
    const message = navigation.querySelector('[data-auth-message]');
    const boutonDeconnexion = navigation.querySelector(
        '[data-auth-deconnexion]'
    );
    const lienConnexion = navigation.querySelector('[data-lien-connexion]');

    const ecranMobile = window.matchMedia('(max-width: 639px)');

    menu.id = 'navigation-principale';

    const actionsMobiles = document.createElement('div');
    actionsMobiles.className = 'auth-actions-mobiles';

    actionsMobiles.innerHTML = `
        <a
            class="auth-icone auth-panier-mobile"
            href="panier.html"
            aria-label="Panier"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M3 3h2l2.5 12h11l2-8H6"></path>
                <circle cx="9" cy="20" r="1"></circle>
                <circle cx="18" cy="20" r="1"></circle>
            </svg>

            <span class="auth-compteur-panier" aria-hidden="true">0</span>
        </a>

        <button
            class="auth-icone"
            type="button"
            data-compte-mobile
            aria-label="Connexion et inscription"
            aria-controls="navigation-principale"
            aria-expanded="false"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <circle cx="12" cy="8" r="4"></circle>
                <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
            </svg>
        </button>

        <button
            class="auth-icone"
            type="button"
            data-menu-mobile
            aria-label="Ouvrir le menu"
            aria-controls="navigation-principale"
            aria-expanded="false"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    `;

    navigation.insertBefore(actionsMobiles, menu);

    const boutonMenu = actionsMobiles.querySelector('[data-menu-mobile]');
    const boutonCompte = actionsMobiles.querySelector('[data-compte-mobile]');
    const lienPanier = actionsMobiles.querySelector('.auth-panier-mobile');
    const compteurPanier = actionsMobiles.querySelector(
        '.auth-compteur-panier'
    );

    const lienBackoffice = document.createElement('a');
    lienBackoffice.className = 'auth-lien-backoffice';
    lienBackoffice.href = 'backoffice.html';
    lienBackoffice.textContent = 'Back-office';
    lienBackoffice.hidden = true;

    connecte.prepend(lienBackoffice);

    const menuGestionnaire = document.createElement('div');
    menuGestionnaire.className = 'auth-gestionnaire-mobile';
    menuGestionnaire.hidden = true;

    menuGestionnaire.innerHTML = `
        <strong>Gestionnaire</strong>
        <a href="backoffice.html">Jauge des soirées</a>
    `;

    menu.append(menuGestionnaire);

    if (location.pathname.endsWith('/backoffice.html')) {
        lienBackoffice.setAttribute('aria-current', 'page');

        menuGestionnaire.querySelector('a').setAttribute(
            'aria-current',
            'page'
        );
    }

    const retour = location.pathname.endsWith('/connexion.html')
        ? 'index.html'
        : location.pathname + location.search;

    lienConnexion.href =
        `connexion.html?retour=${encodeURIComponent(retour)}`;

    let menuOuvert = false;
    let verificationCourante = 0;
    let verificationPanier = 0;

    function changerMenu(ouvert, rendreFocus = false) {
        menuOuvert = ecranMobile.matches && ouvert;
        menu.hidden = ecranMobile.matches && !menuOuvert;

        boutonMenu.setAttribute('aria-expanded', String(menuOuvert));
        boutonCompte.setAttribute('aria-expanded', String(menuOuvert));

        boutonMenu.setAttribute(
            'aria-label',
            menuOuvert ? 'Fermer le menu' : 'Ouvrir le menu'
        );

        if (rendreFocus && ecranMobile.matches) {
            boutonMenu.focus();
        }
    }

    function afficherNombreBillets(nombre) {
        const valide = Number.isInteger(nombre) && nombre >= 0;

        compteurPanier.textContent = valide ? String(nombre) : '…';

        lienPanier.setAttribute(
            'aria-label',
            valide
                ? `Panier : ${nombre} billet${nombre > 1 ? 's' : ''}`
                : 'Panier : nombre de billets indisponible'
        );
    }

    async function actualiserPanier() {
        const numeroVerification = ++verificationPanier;

        if (!ecranMobile.matches) {
            return;
        }

        const jeton = lireJeton();

        if (!jeton) {
            afficherNombreBillets(0);
            return;
        }

        afficherNombreBillets(null);

        try {
            const resultat = await requeteApi('/panier', {
                authentifie: true,
            });

            if (
                numeroVerification !== verificationPanier
                || jeton !== lireJeton()
            ) {
                return;
            }

            afficherNombreBillets(resultat.data?.nombre_billets);
        } catch {
            if (numeroVerification !== verificationPanier) {
                return;
            }

            afficherNombreBillets(lireJeton() ? null : 0);
        }
    }

    async function actualiserNavigation() {
        const numeroVerification = ++verificationCourante;

        invite.hidden = true;
        connecte.hidden = true;
        lienBackoffice.hidden = true;
        menuGestionnaire.hidden = true;
        message.hidden = true;
        nom.textContent = '';

        boutonCompte.setAttribute(
            'aria-label',
            'Connexion et inscription'
        );

        if (!lireJeton()) {
            invite.hidden = false;
            return;
        }

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

            const gestionnaire = profil.role === 'gestionnaire';

            nom.textContent = profil.nom;
            lienBackoffice.hidden = !gestionnaire;
            menuGestionnaire.hidden = !gestionnaire;
            connecte.hidden = false;
            message.hidden = true;

            boutonCompte.setAttribute(
                'aria-label',
                `Ouvrir les actions du compte de ${profil.nom}`
            );
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

    boutonMenu.addEventListener('click', () => {
        changerMenu(!menuOuvert);
    });

    boutonCompte.addEventListener('click', () => {
        changerMenu(true);

        if (!connecte.hidden) {
            boutonDeconnexion.focus();
        } else if (!invite.hidden) {
            lienConnexion.focus();
        }
    });

    menu.addEventListener('click', (evenement) => {
        const action = evenement.target.closest('a, button');

        if (action && ecranMobile.matches) {
            changerMenu(false, action.matches('button'));
        }
    });

    document.addEventListener('click', (evenement) => {
        if (menuOuvert && !navigation.contains(evenement.target)) {
            changerMenu(false);
        }
    });

    document.addEventListener('keydown', (evenement) => {
        if (evenement.key === 'Escape' && menuOuvert) {
            changerMenu(false, true);
        }
    });

    ecranMobile.addEventListener('change', () => {
        changerMenu(false);
        actualiserPanier();
    });

    boutonDeconnexion.addEventListener('click', deconnecter);

    window.addEventListener('nrv-auth-change', () => {
        changerMenu(false);
        actualiserNavigation();
        actualiserPanier();
    });

    window.addEventListener('nrv-panier-modifie', actualiserPanier);
    window.addEventListener('focus', actualiserPanier);

    window.addEventListener('pageshow', (evenement) => {
        if (evenement.persisted) {
            changerMenu(false);
            actualiserNavigation();
            actualiserPanier();
        }
    });

    changerMenu(false);
    actualiserNavigation();
    actualiserPanier();
}

initialiserNavigation();