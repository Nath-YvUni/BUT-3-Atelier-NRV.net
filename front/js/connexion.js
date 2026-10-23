import {
    enregistrerJeton,
    requeteApi,
} from './auth.js';

const formulaireConnexion = document.getElementById('form-connexion');
const formulaireInscription = document.getElementById('form-inscription');

const messageConnexion = document.getElementById('connexion-message');
const messageInscription = document.getElementById('inscription-message');

function afficherMessage(element, texte, erreur = false) {
    element.textContent = texte;
    element.hidden = texte === '';
    element.classList.toggle('auth-message--erreur', erreur);
}

function destinationRetour() {
    const parametres = new URLSearchParams(location.search);
    const retour = parametres.get('retour') || 'index.html';

    try {
        const destination = new URL(retour, location.href);

        const pagesAutorisees = [
            '/',
            '/index.html',
            '/soiree.html',
            '/panier.html',
            '/paiement.html',
            '/mes-billets.html',
            '/billet.html',
        ];

        if (
            destination.origin === location.origin
            && pagesAutorisees.includes(destination.pathname)
        ) {
            return destination.href;
        }
    } catch {
    }

    return 'index.html';
}

async function executerFormulaire(formulaire, message, operation) {
    const bouton = formulaire.querySelector('button[type="submit"]');
    const texteInitial = bouton.textContent;

    afficherMessage(message, '');
    bouton.disabled = true;
    bouton.textContent = 'Veuillez patienter…';

    try {
        await operation();
    } catch (erreur) {
        afficherMessage(message, erreur.message, true);
    } finally {
        bouton.disabled = false;
        bouton.textContent = texteInitial;
    }
}

formulaireConnexion.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    await executerFormulaire(
        formulaireConnexion,
        messageConnexion,
        async () => {
            const donnees = {
                email: document.getElementById('connexion-email').value,
                mot_de_passe: document.getElementById(
                    'connexion-mot-de-passe'
                ).value,
            };

            const resultat = await requeteApi('/auth/connexion', {
                methode: 'POST',
                donnees,
            });

            enregistrerJeton(resultat.data.access_token);
            formulaireConnexion.reset();

            location.assign(destinationRetour());
        }
    );
});

formulaireInscription.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    const motDePasse = document.getElementById(
        'inscription-mot-de-passe'
    ).value;

    const confirmation = document.getElementById(
        'inscription-confirmation'
    );

    if (motDePasse !== confirmation.value) {
        afficherMessage(
            messageInscription,
            'Les deux mots de passe doivent être identiques.',
            true
        );

        confirmation.focus();
        return;
    }

    await executerFormulaire(
        formulaireInscription,
        messageInscription,
        async () => {
            const donnees = {
                nom: document.getElementById('inscription-nom').value,
                email: document.getElementById('inscription-email').value,
                mot_de_passe: motDePasse,
            };

            await requeteApi('/utilisateurs', {
                methode: 'POST',
                donnees,
            });

            formulaireInscription.reset();

            const emailConnexion = document.getElementById(
                'connexion-email'
            );

            emailConnexion.value = donnees.email.trim();

            afficherMessage(
                messageInscription,
                'Votre compte est créé. Vous pouvez vous connecter.'
            );

            emailConnexion.focus();
        }
    );
});