   const API_URL = 'http://localhost:7080';

const message = document.getElementById('spectacles-message');
const soireeTemplate = document.getElementById('soiree-template');
const boite = document.getElementById('soiree');
const spectacleTemplate = document.getElementById('soiree-spectacle-template');

function creerSoiree(soiree){
    const element = soireeTemplate.content.cloneNode(true);

    element.querySelector(".nom").textContent = soiree.nom;
    element.querySelector(".thematique").textContent = "Thématique : " + soiree.thematique;
    element.querySelector(".date").textContent = "Date : " + soiree.date_heure_debut;
    element.querySelector('.nom-lieu').textContent = soiree.nom_lieu;
    element.querySelector('.capacite-valeur').textContent = soiree.capacite;
    element.querySelector('.tarif-valeur').textContent = soiree.tarif;
    element.querySelector('.artistes-soiree').textContent =
        soiree.artistes.map(a => a.nom).join(', ');
    
     const ul = element.querySelector('.liste-spectacles');
    (soiree.liste ?? []).forEach(spectacle => ul.appendChild(creerSpectacle(spectacle)));


    return element;

}

function creerSpectacle(spectacle) {
    const li = spectacleTemplate.content.cloneNode(true);

    li.querySelector('.titre').textContent = spectacle.titre;
    li.querySelector('.artistes').textContent = spectacle.artistes;
    li.querySelector('.styles').textContent = spectacle.styles;
    li.querySelector('.description').textContent = spectacle.description;
    li.querySelector('.video').href = spectacle.url_video;

    return li;
}

async function chargerSoiree(id) {
    try {
        const response = await fetch(`${API_URL}/soirees/${id}`);
        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const soiree = await response.json();
        
        message.remove();
        boite.appendChild(creerSoiree(soiree));
        


    } catch(e) {
        console.error(e);
    }
    

}

chargerSoiree(1);

