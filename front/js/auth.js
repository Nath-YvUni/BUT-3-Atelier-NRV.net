const CLE_JETON = 'nrv.access_token';

export class ErreurApi extends Error {
    constructor(message, statut) {
        super(message);
        this.name = 'ErreurApi';
        this.statut = statut;
    }
}

export function lireJeton() {
    return sessionStorage.getItem(CLE_JETON);
}

export function enregistrerJeton(jeton) {
    if (typeof jeton !== 'string' || jeton === '') {
        throw new Error('Le serveur n’a pas renvoyé de jeton valide.');
    }

    sessionStorage.setItem(CLE_JETON, jeton);
    window.dispatchEvent(new Event('nrv-auth-change'));
}

export function deconnecter() {
    sessionStorage.removeItem(CLE_JETON);
    window.dispatchEvent(new Event('nrv-auth-change'));
}

export async function requeteApi(
    chemin,
    {
        methode = 'GET',
        donnees,
        authentifie = false,
    } = {}
) {
    const headers = {
        Accept: 'application/json',
    };

    if (donnees !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    if (authentifie) {
        const jeton = lireJeton();

        if (!jeton) {
            throw new ErreurApi('Connectez-vous pour continuer.', 401);
        }

        headers.Authorization = `Bearer ${jeton}`;
    }

    let reponse;

    try {
        reponse = await fetch(`/api${chemin}`, {
            method: methode,
            headers,
            body: donnees === undefined
                ? undefined
                : JSON.stringify(donnees),
        });
    } catch {
        throw new ErreurApi(
            'Impossible de joindre le serveur. Réessayez.',
            0
        );
    }

    let contenu = null;

    try {
        contenu = await reponse.json();
    } catch {
    }

    if (!reponse.ok) {
        if (authentifie && reponse.status === 401) {
            deconnecter();
        }

        let message = reponse.status >= 500
            ? 'Le service est momentanément indisponible.'
            : contenu?.error?.message
                ?? 'La demande n’a pas pu être traitée.';

        const details = contenu?.error?.details;

        if (reponse.status < 500 && Array.isArray(details)) {
            const precisions = details
                .map((detail) => detail.message)
                .filter((texte) => typeof texte === 'string');

            message = [message, ...precisions].join(' ');
        }

        throw new ErreurApi(message, reponse.status);
    }

    if (contenu === null) {
        throw new ErreurApi(
            'Le serveur a renvoyé une réponse invalide.',
            reponse.status
        );
    }

    return contenu;
}