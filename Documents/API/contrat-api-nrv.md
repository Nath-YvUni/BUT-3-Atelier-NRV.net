# Contrat API NRV.net — proposé, festival unique

## 1. Routes globales

| Méthode | Route                          | Fonction                 | Accès              |
| ------- | ------------------------------ | ------------------------ | ------------------ |
| GET     | `/api/spectacles`              | Liste des spectacles     | Public             |
| GET     | `/api/spectacles/{id}`         | Détail d’un spectacle    | Public             |
| GET     | `/api/soirees`                 | Liste des soirées        | Public             |
| GET     | `/api/soirees/{id}`            | Détail d’une soirée      | Public             |
| GET     | `/api/styles`                  | Liste des styles         | Public             |
| GET     | `/api/tarifs`                  | Liste des tarifs         | Public             |
| GET     | `/api/lieux`                   | Liste des lieux          | Public             |
| GET     | `/api/artistes`                | Liste des artistes       | Public             |
| POST    | `/api/utilisateurs`            | Inscription              | Public             |
| POST    | `/api/auth/connexion`          | Connexion                | Public             |
| GET     | `/api/me`                      | Profil connecté          | Connecté           |
| GET     | `/api/panier`                  | Consultation du panier   | Connecté           |
| POST    | `/api/panier/lignes`           | Ajout de billets         | Connecté           |
| PATCH   | `/api/panier/lignes/{id}`      | Modification de quantité | Connecté           |
| DELETE  | `/api/panier/lignes/{id}`      | Suppression d’une ligne  | Connecté           |
| POST    | `/api/panier/pass`             | Ajout d’un pass          | Connecté           |
| DELETE  | `/api/panier/pass/{id}`        | Suppression d’un pass    | Connecté           |
| DELETE  | `/api/panier`                  | Vidage du panier         | Connecté           |
| POST    | `/api/commandes`               | Création d’une commande  | Connecté           |
| GET     | `/api/commandes`               | Liste de mes commandes   | Connecté           |
| GET     | `/api/commandes/{id}`          | Détail d’une commande    | Propriétaire       |
| POST    | `/api/commandes/{id}/paiement` | Paiement simulé          | Propriétaire       |
| GET     | `/api/commandes/{id}/billets`  | Billets d’une commande   | Propriétaire       |
| GET     | `/api/billets/{token}`         | Consultation d’un billet | Détenteur du token |
| GET     | `/api/admin/statistiques`      | Statistiques des ventes  | Gestionnaire       |
| POST    | `/api/admin/soirees`           | Création d’une soirée    | Gestionnaire       |
| POST    | `/api/admin/spectacles`        | Création d’un spectacle  | Gestionnaire       |

Routes protégées : `Authorization: Bearer <jeton>`. Corps JSON : `Content-Type: application/json`.

## 2. Toutes les routes

### 2.1. GET /api/spectacles — Liste des spectacles

**Accès :** Public.

**Exemple de requête :** `GET /api/spectacles?date=2027-06-04&style_id=1&page=1&limit=12`.

**Données envoyées à l’API — paramètres d’URL, aucun corps JSON**

Format des paramètres, représenté en JSON :

```json
{
  "date": "AAAA-MM-JJ",
  "style_id": "entier",
  "page": "entier",
  "limit": "entier"
}
```

Exemple des paramètres :

```json
{
  "date": "2027-06-04",
  "style_id": 1,
  "page": 1,
  "limit": 12
}
```

Filtres facultatifs et cumulables. Pagination : page ≥ 1 ; limit entre 1 et 100, défaut 12. Résultat vide : data = []. Liens next/prev présents si une page correspondante existe.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "titre": "chaîne",
      "date": "AAAA-MM-JJ",
      "horaire_previsionnel": "HH:MM:SS",
      "image": "URL",
      "soiree_id": "entier",
      "_links": {
        "self": {
          "href": "chaîne",
          "method": "GET"
        },
        "soiree": {
          "href": "chaîne",
          "method": "GET"
        }
      }
    }
  ],
  "meta": {
    "page": "entier",
    "limit": "entier",
    "total": "entier",
    "total_pages": "entier"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "titre": "Pistons & Chrome",
      "date": "2027-06-04",
      "horaire_previsionnel": "20:00:00",
      "image": "https://picsum.photos/seed/nrv-1/800/600",
      "soiree_id": 1,
      "_links": {
        "self": {
          "href": "/api/spectacles/1",
          "method": "GET"
        },
        "soiree": {
          "href": "/api/soirees/1",
          "method": "GET"
        }
      }
    },
    {
      "id": 2,
      "titre": "Electric Tour",
      "date": "2027-06-04",
      "horaire_previsionnel": "21:15:00",
      "image": "https://picsum.photos/seed/nrv-2/800/600",
      "soiree_id": 1,
      "_links": {
        "self": {
          "href": "/api/spectacles/2",
          "method": "GET"
        },
        "soiree": {
          "href": "/api/soirees/1",
          "method": "GET"
        }
      }
    }
  ],
  "meta": {
    "page": 1,
    "limit": 12,
    "total": 2,
    "total_pages": 1
  },
  "_links": {
    "self": {
      "href": "/api/spectacles?date=2027-06-04&style_id=1&page=1&limit=12",
      "method": "GET"
    }
  }
}
```

### 2.2. GET /api/spectacles/{id} — Détail d’un spectacle

**Accès :** Public.

**Exemple de requête :** `GET /api/spectacles/1`.

**Données envoyées à l’API :** `id` entier dans l’URL ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "titre": "chaîne",
    "description": "chaîne",
    "date": "AAAA-MM-JJ",
    "horaire_previsionnel": "HH:MM:SS",
    "url_video": "URL",
    "images": [
      {
        "id": "entier",
        "url": "URL"
      }
    ],
    "artistes": [
      {
        "id": "entier",
        "nom": "chaîne"
      }
    ],
    "styles": [
      {
        "id": "entier",
        "libelle": "chaîne"
      }
    ],
    "soiree_id": "entier"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "soiree": {
      "href": "chaîne",
      "method": "GET"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "titre": "Pistons & Chrome",
    "description": "Les Rusty Pistons ouvrent le festival avec leurs riffs seventies et leurs solos interminables.",
    "date": "2027-06-04",
    "horaire_previsionnel": "20:00:00",
    "url_video": "https://example.com/videos/spectacle-1.mp4",
    "images": [
      {
        "id": 1,
        "url": "https://picsum.photos/seed/nrv-1/800/600"
      },
      {
        "id": 2,
        "url": "https://picsum.photos/seed/nrv-1b/800/600"
      }
    ],
    "artistes": [
      {
        "id": 1,
        "nom": "The Rusty Pistons"
      }
    ],
    "styles": [
      {
        "id": 1,
        "libelle": "Classic rock"
      }
    ],
    "soiree_id": 1
  },
  "_links": {
    "self": {
      "href": "/api/spectacles/1",
      "method": "GET"
    },
    "soiree": {
      "href": "/api/soirees/1",
      "method": "GET"
    }
  }
}
```

### 2.3. GET /api/soirees — Liste des soirées

**Accès :** Public.

**Exemple de requête :** `GET /api/soirees?date=2027-06-05`.

**Données envoyées à l’API — paramètres d’URL, aucun corps JSON**

Format des paramètres, représenté en JSON :

```json
{
  "date": "AAAA-MM-JJ"
}
```

Exemple des paramètres :

```json
{
  "date": "2027-06-05"
}
```

Le filtre date est facultatif.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "nom": "chaîne",
      "thematique": "chaîne",
      "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
      "lieu": {
        "id": "entier",
        "nom": "chaîne",
        "adresse": "chaîne"
      },
      "capacite": "entier",
      "places_vendues": "entier",
      "places_disponibles": "entier",
      "complete": "booléen",
      "_links": {
        "self": {
          "href": "chaîne",
          "method": "GET"
        }
      }
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 2,
      "nom": "Blues Night",
      "thematique": "Soirée blues",
      "date_heure_debut": "2027-06-05T20:30:00",
      "lieu": {
        "id": 1,
        "nom": "L'Autre Canal",
        "adresse": "45 boulevard d'Austrasie, 54000 Nancy"
      },
      "capacite": 800,
      "places_vendues": 1,
      "places_disponibles": 799,
      "complete": false,
      "_links": {
        "self": {
          "href": "/api/soirees/2",
          "method": "GET"
        }
      }
    }
  ]
}
```

### 2.4. GET /api/soirees/{id} — Détail d’une soirée

**Accès :** Public.

**Exemple de requête :** `GET /api/soirees/2`.

**Données envoyées à l’API :** `id` entier dans l’URL ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "nom": "chaîne",
    "thematique": "chaîne",
    "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
    "lieu": {
      "id": "entier",
      "nom": "chaîne",
      "adresse": "chaîne"
    },
    "capacite": "entier",
    "places_vendues": "entier",
    "places_disponibles": "entier",
    "complete": "booléen",
    "tarifs": [
      {
        "id": "entier",
        "categorie": "normal | reduit",
        "prix": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "spectacles": [
      {
        "id": "entier",
        "titre": "chaîne",
        "description": "chaîne",
        "date": "AAAA-MM-JJ",
        "horaire_previsionnel": "HH:MM:SS",
        "url_video": "URL",
        "images": [
          {
            "id": "entier",
            "url": "URL"
          }
        ],
        "artistes": [
          {
            "id": "entier",
            "nom": "chaîne"
          }
        ],
        "styles": [
          {
            "id": "entier",
            "libelle": "chaîne"
          }
        ],
        "soiree_id": "entier"
      }
    ]
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "ajouter_billets": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 2,
    "nom": "Blues Night",
    "thematique": "Soirée blues",
    "date_heure_debut": "2027-06-05T20:30:00",
    "lieu": {
      "id": 1,
      "nom": "L'Autre Canal",
      "adresse": "45 boulevard d'Austrasie, 54000 Nancy"
    },
    "capacite": 800,
    "places_vendues": 1,
    "places_disponibles": 799,
    "complete": false,
    "tarifs": [
      {
        "id": 1,
        "categorie": "normal",
        "prix": "35.00"
      },
      {
        "id": 2,
        "categorie": "reduit",
        "prix": "25.00"
      }
    ],
    "spectacles": [
      {
        "id": 4,
        "titre": "Mississippi to Moselle",
        "description": "Big Joe Mulligan revisite les standards du delta blues, guitare slide à la main.",
        "date": "2027-06-05",
        "horaire_previsionnel": "20:30:00",
        "url_video": "https://example.com/videos/spectacle-4.mp4",
        "images": [
          {
            "id": 5,
            "url": "https://picsum.photos/seed/nrv-4/800/600"
          }
        ],
        "artistes": [
          {
            "id": 4,
            "nom": "Big Joe Mulligan"
          }
        ],
        "styles": [
          {
            "id": 9,
            "libelle": "Blues"
          },
          {
            "id": 2,
            "libelle": "Blues rock"
          }
        ],
        "soiree_id": 2
      },
      {
        "id": 5,
        "titre": "Delta Sisters",
        "description": "Trois voix, trois guitares : le blues rock des Delta Sisters en version électrique.",
        "date": "2027-06-05",
        "horaire_previsionnel": "21:45:00",
        "url_video": "https://example.com/videos/spectacle-5.mp4",
        "images": [
          {
            "id": 6,
            "url": "https://picsum.photos/seed/nrv-5/800/600"
          }
        ],
        "artistes": [
          {
            "id": 5,
            "nom": "Delta Sisters"
          }
        ],
        "styles": [
          {
            "id": 2,
            "libelle": "Blues rock"
          }
        ],
        "soiree_id": 2
      }
    ]
  },
  "_links": {
    "self": {
      "href": "/api/soirees/2",
      "method": "GET"
    },
    "ajouter_billets": {
      "href": "/api/panier/lignes",
      "method": "POST"
    }
  }
}
```

### 2.5. GET /api/styles — Liste des styles

**Accès :** Public.

**Exemple de requête :** `GET /api/styles`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "libelle": "chaîne"
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "libelle": "Classic rock"
    },
    {
      "id": 2,
      "libelle": "Blues rock"
    },
    {
      "id": 3,
      "libelle": "Métal"
    },
    {
      "id": 4,
      "libelle": "Chanson"
    },
    {
      "id": 5,
      "libelle": "Reggae"
    },
    {
      "id": 6,
      "libelle": "Punk"
    },
    {
      "id": 7,
      "libelle": "Hard rock"
    },
    {
      "id": 8,
      "libelle": "Folk"
    },
    {
      "id": 9,
      "libelle": "Blues"
    }
  ]
}
```

### 2.6. GET /api/tarifs — Liste des tarifs

**Accès :** Public.

**Exemple de requête :** `GET /api/tarifs`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "categorie": "normal | reduit",
      "prix": "chaîne décimale à 2 chiffres après le point"
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "categorie": "normal",
      "prix": "35.00"
    },
    {
      "id": 2,
      "categorie": "reduit",
      "prix": "25.00"
    }
  ]
}
```

### 2.7. GET /api/lieux — Liste des lieux

**Accès :** Public.

**Exemple de requête :** `GET /api/lieux`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "nom": "chaîne",
      "adresse": "chaîne"
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "nom": "L'Autre Canal",
      "adresse": "45 boulevard d'Austrasie, 54000 Nancy"
    },
    {
      "id": 2,
      "nom": "Zénith de Nancy",
      "adresse": "Rue du Zénith, 54320 Maxéville"
    },
    {
      "id": 3,
      "nom": "Salle Poirel",
      "adresse": "3 rue Victor Poirel, 54000 Nancy"
    }
  ]
}
```

### 2.8. GET /api/artistes — Liste des artistes

**Accès :** Public.

**Exemple de requête :** `GET /api/artistes`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "nom": "chaîne"
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "nom": "The Rusty Pistons"
    },
    {
      "id": 2,
      "nom": "Velvet Thunder"
    },
    {
      "id": 3,
      "nom": "Marie Laroche"
    },
    {
      "id": 4,
      "nom": "Big Joe Mulligan"
    },
    {
      "id": 5,
      "nom": "Delta Sisters"
    },
    {
      "id": 6,
      "nom": "Iron Lorraine"
    },
    {
      "id": 7,
      "nom": "Necrofest"
    },
    {
      "id": 8,
      "nom": "Hurlement"
    },
    {
      "id": 9,
      "nom": "Léo Marchal"
    },
    {
      "id": 10,
      "nom": "Camille Doré"
    },
    {
      "id": 11,
      "nom": "Roots Kingdom"
    },
    {
      "id": 12,
      "nom": "Lion Steppers"
    },
    {
      "id": 13,
      "nom": "Les Pavés Sauvages"
    },
    {
      "id": 14,
      "nom": "No Futur Club"
    },
    {
      "id": 15,
      "nom": "Thunderhawk"
    },
    {
      "id": 16,
      "nom": "The Midnight Riders"
    },
    {
      "id": 17,
      "nom": "Sarah Blackwood"
    },
    {
      "id": 18,
      "nom": "Kaya Sound System"
    }
  ]
}
```

### 2.9. POST /api/utilisateurs — Inscription

**Accès :** Public.

**Exemple de requête :** `POST /api/utilisateurs`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "nom": "chaîne",
  "email": "chaîne",
  "mot_de_passe": "chaîne"
}
```

Exemple envoyé :

```json
{
  "nom": "Lou",
  "email": "lou@example.com",
  "mot_de_passe": "UnePhraseDePassePourNRV!"
}
```

Champs obligatoires. Nom : 1 à 100 caractères ; e-mail valide, unique et normalisé en minuscules ; mot de passe : au moins 12 caractères. Le rôle est fixé par le backend.

**Données renvoyées par l’API — HTTP 201**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "nom": "chaîne",
    "email": "chaîne",
    "role": "utilisateur | gestionnaire"
  },
  "_links": {
    "connexion": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 5,
    "nom": "Lou",
    "email": "lou@example.com",
    "role": "utilisateur"
  },
  "_links": {
    "connexion": {
      "href": "/api/auth/connexion",
      "method": "POST"
    }
  }
}
```

### 2.10. POST /api/auth/connexion — Connexion

**Accès :** Public.

**Exemple de requête :** `POST /api/auth/connexion`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "email": "chaîne",
  "mot_de_passe": "chaîne"
}
```

Exemple envoyé :

```json
{
  "email": "bob@example.com",
  "mot_de_passe": "password"
}
```

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "access_token": "chaîne",
    "token_type": "chaîne",
    "expires_in": "entier",
    "utilisateur": {
      "id": "entier",
      "nom": "chaîne",
      "email": "chaîne",
      "role": "utilisateur | gestionnaire"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "access_token": "<jeton-genere-par-le-backend>",
    "token_type": "Bearer",
    "expires_in": 3600,
    "utilisateur": {
      "id": 3,
      "nom": "Bob Durand",
      "email": "bob@example.com",
      "role": "utilisateur"
    }
  }
}
```

### 2.11. GET /api/me — Profil connecté

**Accès :** Connecté.

**Exemple de requête :** `GET /api/me`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "nom": "chaîne",
    "email": "chaîne",
    "role": "utilisateur | gestionnaire"
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 3,
    "nom": "Bob Durand",
    "email": "bob@example.com",
    "role": "utilisateur"
  }
}
```

### 2.12. GET /api/panier — Consultation du panier

**Accès :** Connecté.

**Exemple de requête :** `GET /api/panier`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

Sans panier : id = null, lignes = [], pass = [], nombre_billets = 0, montant_total = "0.00". Un pass compte pour trois billets. Un panier avec commande en attente a modifiable = false et commande_en_attente_id renseigné.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "lignes": [
      {
        "id": "entier",
        "soiree": {
          "id": "entier",
          "nom": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
        },
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "quantite": "entier",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "pass": [
      {
        "id": "entier",
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "taux_remise": "chaîne décimale en pourcentage",
        "soirees": [
          {
            "id": "entier",
            "nom": "chaîne",
            "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
          }
        ],
        "montant_avant_remise": "chaîne décimale à 2 chiffres après le point",
        "montant_remise": "chaîne décimale à 2 chiffres après le point",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "commander": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "lignes": [
      {
        "id": 1,
        "soiree": {
          "id": 2,
          "nom": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00"
        },
        "tarif": {
          "id": 1,
          "categorie": "normal",
          "prix": "35.00"
        },
        "quantite": 2,
        "sous_total": "70.00"
      },
      {
        "id": 2,
        "soiree": {
          "id": 5,
          "nom": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00"
        },
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "quantite": 1,
        "sous_total": "25.00"
      }
    ],
    "pass": [
      {
        "id": 2,
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "taux_remise": "3.00",
        "soirees": [
          {
            "id": 3,
            "nom": "Metal Storm",
            "date_heure_debut": "2027-06-08T19:30:00"
          },
          {
            "id": 5,
            "nom": "Reggae Vibration",
            "date_heure_debut": "2027-06-12T20:00:00"
          },
          {
            "id": 6,
            "nom": "Punk Attitude",
            "date_heure_debut": "2027-06-15T20:30:00"
          }
        ],
        "montant_avant_remise": "75.00",
        "montant_remise": "2.25",
        "sous_total": "72.75"
      }
    ],
    "nombre_billets": 6,
    "montant_total": "167.75",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    },
    "commander": {
      "href": "/api/commandes",
      "method": "POST"
    }
  }
}
```

### 2.13. POST /api/panier/lignes — Ajout de billets

**Accès :** Connecté.

**Exemple de requête :** `POST /api/panier/lignes`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "soiree_id": "entier",
  "tarif_id": "entier",
  "quantite": "entier"
}
```

Exemple envoyé :

```json
{
  "soiree_id": 2,
  "tarif_id": 1,
  "quantite": 2
}
```

Entiers obligatoires, quantité > 0. Si la ligne soirée/tarif existe, la quantité est ajoutée : Bob passe ici de 2 à 4 billets pour cette ligne. Réponse 201 si création, 200 si augmentation. Prix calculés par le backend.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "lignes": [
      {
        "id": "entier",
        "soiree": {
          "id": "entier",
          "nom": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
        },
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "quantite": "entier",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "pass": [
      {
        "id": "entier",
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "taux_remise": "chaîne décimale en pourcentage",
        "soirees": [
          {
            "id": "entier",
            "nom": "chaîne",
            "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
          }
        ],
        "montant_avant_remise": "chaîne décimale à 2 chiffres après le point",
        "montant_remise": "chaîne décimale à 2 chiffres après le point",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "commander": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "lignes": [
      {
        "id": 1,
        "soiree": {
          "id": 2,
          "nom": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00"
        },
        "tarif": {
          "id": 1,
          "categorie": "normal",
          "prix": "35.00"
        },
        "quantite": 4,
        "sous_total": "140.00"
      },
      {
        "id": 2,
        "soiree": {
          "id": 5,
          "nom": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00"
        },
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "quantite": 1,
        "sous_total": "25.00"
      }
    ],
    "pass": [
      {
        "id": 2,
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "taux_remise": "3.00",
        "soirees": [
          {
            "id": 3,
            "nom": "Metal Storm",
            "date_heure_debut": "2027-06-08T19:30:00"
          },
          {
            "id": 5,
            "nom": "Reggae Vibration",
            "date_heure_debut": "2027-06-12T20:00:00"
          },
          {
            "id": 6,
            "nom": "Punk Attitude",
            "date_heure_debut": "2027-06-15T20:30:00"
          }
        ],
        "montant_avant_remise": "75.00",
        "montant_remise": "2.25",
        "sous_total": "72.75"
      }
    ],
    "nombre_billets": 8,
    "montant_total": "237.75",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    },
    "commander": {
      "href": "/api/commandes",
      "method": "POST"
    }
  }
}
```

### 2.14. PATCH /api/panier/lignes/{id} — Modification de quantité

**Accès :** Connecté.

**Exemple de requête :** `PATCH /api/panier/lignes/1`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "quantite": "entier"
}
```

Exemple envoyé :

```json
{
  "quantite": 4
}
```

Paramètre d’URL : `id` entier.

Quantité finale entière > 0. La ligne doit appartenir au panier connecté.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "lignes": [
      {
        "id": "entier",
        "soiree": {
          "id": "entier",
          "nom": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
        },
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "quantite": "entier",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "pass": [
      {
        "id": "entier",
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "taux_remise": "chaîne décimale en pourcentage",
        "soirees": [
          {
            "id": "entier",
            "nom": "chaîne",
            "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
          }
        ],
        "montant_avant_remise": "chaîne décimale à 2 chiffres après le point",
        "montant_remise": "chaîne décimale à 2 chiffres après le point",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "commander": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "lignes": [
      {
        "id": 1,
        "soiree": {
          "id": 2,
          "nom": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00"
        },
        "tarif": {
          "id": 1,
          "categorie": "normal",
          "prix": "35.00"
        },
        "quantite": 4,
        "sous_total": "140.00"
      },
      {
        "id": 2,
        "soiree": {
          "id": 5,
          "nom": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00"
        },
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "quantite": 1,
        "sous_total": "25.00"
      }
    ],
    "pass": [
      {
        "id": 2,
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "taux_remise": "3.00",
        "soirees": [
          {
            "id": 3,
            "nom": "Metal Storm",
            "date_heure_debut": "2027-06-08T19:30:00"
          },
          {
            "id": 5,
            "nom": "Reggae Vibration",
            "date_heure_debut": "2027-06-12T20:00:00"
          },
          {
            "id": 6,
            "nom": "Punk Attitude",
            "date_heure_debut": "2027-06-15T20:30:00"
          }
        ],
        "montant_avant_remise": "75.00",
        "montant_remise": "2.25",
        "sous_total": "72.75"
      }
    ],
    "nombre_billets": 8,
    "montant_total": "237.75",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    },
    "commander": {
      "href": "/api/commandes",
      "method": "POST"
    }
  }
}
```

### 2.15. DELETE /api/panier/lignes/{id} — Suppression d’une ligne

**Accès :** Connecté.

**Exemple de requête :** `DELETE /api/panier/lignes/1`.

**Données envoyées à l’API :** `id` entier dans l’URL ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "lignes": [
      {
        "id": "entier",
        "soiree": {
          "id": "entier",
          "nom": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
        },
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "quantite": "entier",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "pass": [
      {
        "id": "entier",
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "taux_remise": "chaîne décimale en pourcentage",
        "soirees": [
          {
            "id": "entier",
            "nom": "chaîne",
            "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
          }
        ],
        "montant_avant_remise": "chaîne décimale à 2 chiffres après le point",
        "montant_remise": "chaîne décimale à 2 chiffres après le point",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "commander": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "lignes": [
      {
        "id": 2,
        "soiree": {
          "id": 5,
          "nom": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00"
        },
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "quantite": 1,
        "sous_total": "25.00"
      }
    ],
    "pass": [
      {
        "id": 2,
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "taux_remise": "3.00",
        "soirees": [
          {
            "id": 3,
            "nom": "Metal Storm",
            "date_heure_debut": "2027-06-08T19:30:00"
          },
          {
            "id": 5,
            "nom": "Reggae Vibration",
            "date_heure_debut": "2027-06-12T20:00:00"
          },
          {
            "id": 6,
            "nom": "Punk Attitude",
            "date_heure_debut": "2027-06-15T20:30:00"
          }
        ],
        "montant_avant_remise": "75.00",
        "montant_remise": "2.25",
        "sous_total": "72.75"
      }
    ],
    "nombre_billets": 4,
    "montant_total": "97.75",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    },
    "commander": {
      "href": "/api/commandes",
      "method": "POST"
    }
  }
}
```

### 2.16. POST /api/panier/pass — Ajout d’un pass

**Accès :** Connecté.

**Exemple de requête :** `POST /api/panier/pass`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "soiree_ids": ["entier"],
  "tarif_id": "entier"
}
```

Exemple envoyé :

```json
{
  "soiree_ids": [1, 3, 7],
  "tarif_id": 1
}
```

Exactement trois soirées distinctes. Tarif existant. La remise de 3 % est déterminée par le backend.

**Données renvoyées par l’API — HTTP 201**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "lignes": [
      {
        "id": "entier",
        "soiree": {
          "id": "entier",
          "nom": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
        },
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "quantite": "entier",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "pass": [
      {
        "id": "entier",
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "taux_remise": "chaîne décimale en pourcentage",
        "soirees": [
          {
            "id": "entier",
            "nom": "chaîne",
            "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
          }
        ],
        "montant_avant_remise": "chaîne décimale à 2 chiffres après le point",
        "montant_remise": "chaîne décimale à 2 chiffres après le point",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "commander": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "lignes": [
      {
        "id": 1,
        "soiree": {
          "id": 2,
          "nom": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00"
        },
        "tarif": {
          "id": 1,
          "categorie": "normal",
          "prix": "35.00"
        },
        "quantite": 2,
        "sous_total": "70.00"
      },
      {
        "id": 2,
        "soiree": {
          "id": 5,
          "nom": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00"
        },
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "quantite": 1,
        "sous_total": "25.00"
      }
    ],
    "pass": [
      {
        "id": 2,
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "taux_remise": "3.00",
        "soirees": [
          {
            "id": 3,
            "nom": "Metal Storm",
            "date_heure_debut": "2027-06-08T19:30:00"
          },
          {
            "id": 5,
            "nom": "Reggae Vibration",
            "date_heure_debut": "2027-06-12T20:00:00"
          },
          {
            "id": 6,
            "nom": "Punk Attitude",
            "date_heure_debut": "2027-06-15T20:30:00"
          }
        ],
        "montant_avant_remise": "75.00",
        "montant_remise": "2.25",
        "sous_total": "72.75"
      },
      {
        "id": 3,
        "tarif": {
          "id": 1,
          "categorie": "normal",
          "prix": "35.00"
        },
        "taux_remise": "3.00",
        "soirees": [
          {
            "id": 1,
            "nom": "Rock'n'Nancy",
            "date_heure_debut": "2027-06-04T20:00:00"
          },
          {
            "id": 3,
            "nom": "Metal Storm",
            "date_heure_debut": "2027-06-08T19:30:00"
          },
          {
            "id": 7,
            "nom": "Rock Legends",
            "date_heure_debut": "2027-06-17T19:00:00"
          }
        ],
        "montant_avant_remise": "105.00",
        "montant_remise": "3.15",
        "sous_total": "101.85"
      }
    ],
    "nombre_billets": 9,
    "montant_total": "269.60",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    },
    "commander": {
      "href": "/api/commandes",
      "method": "POST"
    }
  }
}
```

### 2.17. DELETE /api/panier/pass/{id} — Suppression d’un pass

**Accès :** Connecté.

**Exemple de requête :** `DELETE /api/panier/pass/2`.

**Données envoyées à l’API :** `id` entier dans l’URL ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "lignes": [
      {
        "id": "entier",
        "soiree": {
          "id": "entier",
          "nom": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris"
        },
        "tarif": {
          "id": "entier",
          "categorie": "normal | reduit",
          "prix": "chaîne décimale à 2 chiffres après le point"
        },
        "quantite": "entier",
        "sous_total": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "pass": [],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "commander": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 1,
    "lignes": [
      {
        "id": 1,
        "soiree": {
          "id": 2,
          "nom": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00"
        },
        "tarif": {
          "id": 1,
          "categorie": "normal",
          "prix": "35.00"
        },
        "quantite": 2,
        "sous_total": "70.00"
      },
      {
        "id": 2,
        "soiree": {
          "id": 5,
          "nom": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00"
        },
        "tarif": {
          "id": 2,
          "categorie": "reduit",
          "prix": "25.00"
        },
        "quantite": 1,
        "sous_total": "25.00"
      }
    ],
    "pass": [],
    "nombre_billets": 3,
    "montant_total": "95.00",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    },
    "commander": {
      "href": "/api/commandes",
      "method": "POST"
    }
  }
}
```

### 2.18. DELETE /api/panier — Vidage du panier

**Accès :** Connecté.

**Exemple de requête :** `DELETE /api/panier`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier ou null",
    "lignes": [],
    "pass": [],
    "nombre_billets": "entier",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "commande_en_attente_id": "entier ou null",
    "modifiable": "booléen"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": null,
    "lignes": [],
    "pass": [],
    "nombre_billets": 0,
    "montant_total": "0.00",
    "devise": "EUR",
    "commande_en_attente_id": null,
    "modifiable": true
  },
  "_links": {
    "self": {
      "href": "/api/panier",
      "method": "GET"
    }
  }
}
```

### 2.19. POST /api/commandes — Création d’une commande

**Accès :** Connecté.

**Exemple de requête :** `POST /api/commandes`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

Utilise le panier connecté. Le backend fixe la référence, les prix et le montant. Le contenu de la commande doit être figé ; ce stockage reste à ajouter au schéma actuel. Le panier devient non modifiable.

**Données renvoyées par l’API — HTTP 201**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "reference": "chaîne",
    "date_commande": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
    "statut": "en_attente | payee | refusee",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "contenu": {
      "lignes": [
        {
          "soiree_id": "entier",
          "nom_soiree": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
          "tarif_id": "entier",
          "categorie": "normal | reduit",
          "prix_unitaire": "chaîne décimale à 2 chiffres après le point",
          "quantite": "entier",
          "sous_total": "chaîne décimale à 2 chiffres après le point"
        }
      ],
      "pass": [
        {
          "id": "entier",
          "soiree_ids": ["entier"],
          "tarif_id": "entier",
          "categorie": "normal | reduit",
          "prix_unitaire_avant_remise": "chaîne décimale à 2 chiffres après le point",
          "taux_remise": "chaîne décimale en pourcentage",
          "sous_total": "chaîne décimale à 2 chiffres après le point"
        }
      ]
    }
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "paiement": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 3,
    "reference": "CMD-2026-0003",
    "date_commande": "2026-10-20T15:00:00",
    "statut": "en_attente",
    "montant_total": "167.75",
    "devise": "EUR",
    "contenu": {
      "lignes": [
        {
          "soiree_id": 2,
          "nom_soiree": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00",
          "tarif_id": 1,
          "categorie": "normal",
          "prix_unitaire": "35.00",
          "quantite": 2,
          "sous_total": "70.00"
        },
        {
          "soiree_id": 5,
          "nom_soiree": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00",
          "tarif_id": 2,
          "categorie": "reduit",
          "prix_unitaire": "25.00",
          "quantite": 1,
          "sous_total": "25.00"
        }
      ],
      "pass": [
        {
          "id": 2,
          "soiree_ids": [3, 5, 6],
          "tarif_id": 2,
          "categorie": "reduit",
          "prix_unitaire_avant_remise": "25.00",
          "taux_remise": "3.00",
          "sous_total": "72.75"
        }
      ]
    }
  },
  "_links": {
    "self": {
      "href": "/api/commandes/3",
      "method": "GET"
    },
    "paiement": {
      "href": "/api/commandes/3/paiement",
      "method": "POST"
    }
  }
}
```

### 2.20. GET /api/commandes — Liste de mes commandes

**Accès :** Connecté.

**Exemple de requête :** `GET /api/commandes`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

Exemple : Alice connectée. Seules les commandes du compte connecté sont retournées.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "reference": "chaîne",
      "date_commande": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
      "statut": "en_attente | payee | refusee",
      "montant_total": "chaîne décimale à 2 chiffres après le point",
      "devise": "chaîne",
      "_links": {
        "self": {
          "href": "chaîne",
          "method": "GET"
        },
        "billets": {
          "href": "chaîne",
          "method": "GET"
        }
      }
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "reference": "CMD-2026-0001",
      "date_commande": "2026-10-15T10:12:00",
      "statut": "payee",
      "montant_total": "256.85",
      "devise": "EUR",
      "_links": {
        "self": {
          "href": "/api/commandes/1",
          "method": "GET"
        },
        "billets": {
          "href": "/api/commandes/1/billets",
          "method": "GET"
        }
      }
    }
  ]
}
```

### 2.21. GET /api/commandes/{id} — Détail d’une commande

**Accès :** Propriétaire.

**Exemple de requête :** `GET /api/commandes/3`.

**Données envoyées à l’API :** `id` entier dans l’URL ; aucun corps JSON.

Exemple : commande créée depuis le panier de Bob. Lien paiement si en_attente ; lien billets si payee ; lien panier si refusee.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "reference": "chaîne",
    "date_commande": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
    "statut": "en_attente | payee | refusee",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "contenu": {
      "lignes": [
        {
          "soiree_id": "entier",
          "nom_soiree": "chaîne",
          "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
          "tarif_id": "entier",
          "categorie": "normal | reduit",
          "prix_unitaire": "chaîne décimale à 2 chiffres après le point",
          "quantite": "entier",
          "sous_total": "chaîne décimale à 2 chiffres après le point"
        }
      ],
      "pass": [
        {
          "id": "entier",
          "soiree_ids": ["entier"],
          "tarif_id": "entier",
          "categorie": "normal | reduit",
          "prix_unitaire_avant_remise": "chaîne décimale à 2 chiffres après le point",
          "taux_remise": "chaîne décimale en pourcentage",
          "sous_total": "chaîne décimale à 2 chiffres après le point"
        }
      ]
    }
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "paiement": {
      "href": "chaîne",
      "method": "POST"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 3,
    "reference": "CMD-2026-0003",
    "date_commande": "2026-10-20T15:00:00",
    "statut": "en_attente",
    "montant_total": "167.75",
    "devise": "EUR",
    "contenu": {
      "lignes": [
        {
          "soiree_id": 2,
          "nom_soiree": "Blues Night",
          "date_heure_debut": "2027-06-05T20:30:00",
          "tarif_id": 1,
          "categorie": "normal",
          "prix_unitaire": "35.00",
          "quantite": 2,
          "sous_total": "70.00"
        },
        {
          "soiree_id": 5,
          "nom_soiree": "Reggae Vibration",
          "date_heure_debut": "2027-06-12T20:00:00",
          "tarif_id": 2,
          "categorie": "reduit",
          "prix_unitaire": "25.00",
          "quantite": 1,
          "sous_total": "25.00"
        }
      ],
      "pass": [
        {
          "id": 2,
          "soiree_ids": [3, 5, 6],
          "tarif_id": 2,
          "categorie": "reduit",
          "prix_unitaire_avant_remise": "25.00",
          "taux_remise": "3.00",
          "sous_total": "72.75"
        }
      ]
    }
  },
  "_links": {
    "self": {
      "href": "/api/commandes/3",
      "method": "GET"
    },
    "paiement": {
      "href": "/api/commandes/3/paiement",
      "method": "POST"
    }
  }
}
```

### 2.22. POST /api/commandes/{id}/paiement — Paiement simulé

**Accès :** Propriétaire.

**Exemple de requête :** `POST /api/commandes/3/paiement`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "numero_carte": "chaîne",
  "expiration": "AAAA-MM",
  "cryptogramme": "chaîne"
}
```

Exemple envoyé :

```json
{
  "numero_carte": "4242424242424242",
  "expiration": "2028-12",
  "cryptogramme": "123"
}
```

Paramètre d’URL : `id` entier.

Données fictives de simulation uniquement. Numéro : 16 chiffres ; expiration non passée ; cryptogramme : 3 chiffres. Aucune donnée de carte stockée. Places vérifiées au paiement. Répéter un paiement réussi ne crée pas de nouveaux billets.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "reference": "chaîne",
    "statut": "en_attente | payee | refusee",
    "montant_total": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "nombre_billets": "entier"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "billets": {
      "href": "chaîne",
      "method": "GET"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 3,
    "reference": "CMD-2026-0003",
    "statut": "payee",
    "montant_total": "167.75",
    "devise": "EUR",
    "nombre_billets": 6
  },
  "_links": {
    "self": {
      "href": "/api/commandes/3",
      "method": "GET"
    },
    "billets": {
      "href": "/api/commandes/3/billets",
      "method": "GET"
    }
  }
}
```

**Réponse d’erreur — HTTP 409**

Exemple : paiement de la commande 2 de Claire. Commande refusée, aucun billet créé, panier rendu modifiable.

Format JSON attendu :

```json
{
  "error": {
    "code": "chaîne",
    "message": "chaîne",
    "details": [
      {
        "soiree_id": "entier",
        "nom": "chaîne",
        "quantite_demandee": "entier",
        "places_disponibles": "entier"
      }
    ]
  }
}
```

Exemple reçu :

```json
{
  "error": {
    "code": "PLACES_INSUFFISANTES",
    "message": "La commande ne peut pas être payée : certaines soirées n'ont pas assez de places.",
    "details": [
      {
        "soiree_id": 4,
        "nom": "Chansons d'ici",
        "quantite_demandee": 2,
        "places_disponibles": 0
      }
    ]
  }
}
```

### 2.23. GET /api/commandes/{id}/billets — Billets d’une commande

**Accès :** Propriétaire.

**Exemple de requête :** `GET /api/commandes/1/billets`.

**Données envoyées à l’API :** `id` entier dans l’URL ; aucun corps JSON.

Commande payée uniquement. Exemple : les huit billets de la commande d’Alice.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": [
    {
      "id": "entier",
      "reference": "chaîne",
      "nom_acheteur": "chaîne",
      "soiree": {
        "id": "entier",
        "nom": "chaîne",
        "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
        "lieu": {
          "id": "entier",
          "nom": "chaîne",
          "adresse": "chaîne"
        }
      },
      "tarif": {
        "id": "entier",
        "categorie": "normal | reduit"
      },
      "prix": "chaîne décimale à 2 chiffres après le point",
      "devise": "chaîne",
      "pass_id": "entier ou null",
      "_links": {
        "self": {
          "href": "chaîne",
          "method": "GET"
        }
      }
    }
  ]
}
```

Exemple reçu :

```json
{
  "data": [
    {
      "id": 1,
      "reference": "BIL-2026-0001",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 4,
        "nom": "Chansons d'ici",
        "date_heure_debut": "2027-06-10T20:00:00",
        "lieu": {
          "id": 3,
          "nom": "Salle Poirel",
          "adresse": "3 rue Victor Poirel, 54000 Nancy"
        }
      },
      "tarif": {
        "id": 1,
        "categorie": "normal"
      },
      "prix": "35.00",
      "devise": "EUR",
      "pass_id": null,
      "_links": {
        "self": {
          "href": "/api/billets/94e5bee840008671d1de7d204a9160024e8256030f0b70bb37bfb6d7f1bd2856",
          "method": "GET"
        }
      }
    },
    {
      "id": 2,
      "reference": "BIL-2026-0002",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 4,
        "nom": "Chansons d'ici",
        "date_heure_debut": "2027-06-10T20:00:00",
        "lieu": {
          "id": 3,
          "nom": "Salle Poirel",
          "adresse": "3 rue Victor Poirel, 54000 Nancy"
        }
      },
      "tarif": {
        "id": 1,
        "categorie": "normal"
      },
      "prix": "35.00",
      "devise": "EUR",
      "pass_id": null,
      "_links": {
        "self": {
          "href": "/api/billets/43a8f55c69fec850efb2cafcdbcb1dfbcedd563547c0cf7905b931c2db45f289",
          "method": "GET"
        }
      }
    },
    {
      "id": 3,
      "reference": "BIL-2026-0003",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 4,
        "nom": "Chansons d'ici",
        "date_heure_debut": "2027-06-10T20:00:00",
        "lieu": {
          "id": 3,
          "nom": "Salle Poirel",
          "adresse": "3 rue Victor Poirel, 54000 Nancy"
        }
      },
      "tarif": {
        "id": 2,
        "categorie": "reduit"
      },
      "prix": "25.00",
      "devise": "EUR",
      "pass_id": null,
      "_links": {
        "self": {
          "href": "/api/billets/9590e18ac925993bae12fc757d65589b69cc9fbbc5f10a91c875e42c2e81babc",
          "method": "GET"
        }
      }
    },
    {
      "id": 4,
      "reference": "BIL-2026-0004",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 4,
        "nom": "Chansons d'ici",
        "date_heure_debut": "2027-06-10T20:00:00",
        "lieu": {
          "id": 3,
          "nom": "Salle Poirel",
          "adresse": "3 rue Victor Poirel, 54000 Nancy"
        }
      },
      "tarif": {
        "id": 2,
        "categorie": "reduit"
      },
      "prix": "25.00",
      "devise": "EUR",
      "pass_id": null,
      "_links": {
        "self": {
          "href": "/api/billets/cc8b0ddd4699cb5b5454230bef5e56079a3ad3b7b5ea7042c15e7dece33e94e9",
          "method": "GET"
        }
      }
    },
    {
      "id": 5,
      "reference": "BIL-2026-0005",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 2,
        "nom": "Blues Night",
        "date_heure_debut": "2027-06-05T20:30:00",
        "lieu": {
          "id": 1,
          "nom": "L'Autre Canal",
          "adresse": "45 boulevard d'Austrasie, 54000 Nancy"
        }
      },
      "tarif": {
        "id": 1,
        "categorie": "normal"
      },
      "prix": "35.00",
      "devise": "EUR",
      "pass_id": null,
      "_links": {
        "self": {
          "href": "/api/billets/f77bddce18c7877e4ebafcd9720641362ffbe52b3db68b1da2d4273f88502573",
          "method": "GET"
        }
      }
    },
    {
      "id": 6,
      "reference": "BIL-2026-0006",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 1,
        "nom": "Rock'n'Nancy",
        "date_heure_debut": "2027-06-04T20:00:00",
        "lieu": {
          "id": 2,
          "nom": "Zénith de Nancy",
          "adresse": "Rue du Zénith, 54320 Maxéville"
        }
      },
      "tarif": {
        "id": 1,
        "categorie": "normal"
      },
      "prix": "33.95",
      "devise": "EUR",
      "pass_id": 1,
      "_links": {
        "self": {
          "href": "/api/billets/f3327a8fcc9f8c4736d039d373a5da9ca23fd6b5ef50f3687e79649a8a42421a",
          "method": "GET"
        }
      }
    },
    {
      "id": 7,
      "reference": "BIL-2026-0007",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 3,
        "nom": "Metal Storm",
        "date_heure_debut": "2027-06-08T19:30:00",
        "lieu": {
          "id": 2,
          "nom": "Zénith de Nancy",
          "adresse": "Rue du Zénith, 54320 Maxéville"
        }
      },
      "tarif": {
        "id": 1,
        "categorie": "normal"
      },
      "prix": "33.95",
      "devise": "EUR",
      "pass_id": 1,
      "_links": {
        "self": {
          "href": "/api/billets/1de5b3d73328c78bccb083bb8dab6907c552ae62328f91f42cd3ec5c0b45f69e",
          "method": "GET"
        }
      }
    },
    {
      "id": 8,
      "reference": "BIL-2026-0008",
      "nom_acheteur": "Alice Martin",
      "soiree": {
        "id": 7,
        "nom": "Rock Legends",
        "date_heure_debut": "2027-06-17T19:00:00",
        "lieu": {
          "id": 2,
          "nom": "Zénith de Nancy",
          "adresse": "Rue du Zénith, 54320 Maxéville"
        }
      },
      "tarif": {
        "id": 1,
        "categorie": "normal"
      },
      "prix": "33.95",
      "devise": "EUR",
      "pass_id": 1,
      "_links": {
        "self": {
          "href": "/api/billets/318862371e4abfe7b2c8eb20ece340df9b5052ec0c5c42a7bdf66150591c3af3",
          "method": "GET"
        }
      }
    }
  ]
}
```

### 2.24. GET /api/billets/{token} — Consultation d’un billet

**Accès :** Détenteur du token.

**Exemple de requête :** `GET /api/billets/f3327a8fcc9f8c4736d039d373a5da9ca23fd6b5ef50f3687e79649a8a42421a`.

**Données envoyées à l’API :** `token` chaîne dans l’URL ; aucun corps JSON.

Aucune connexion exigée. Billet appartenant à une commande payée uniquement. Token invalide : 404. Le frontend utilise ces données pour la page imprimable.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "reference": "chaîne",
    "nom_acheteur": "chaîne",
    "soiree": {
      "id": "entier",
      "nom": "chaîne",
      "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
      "lieu": {
        "id": "entier",
        "nom": "chaîne",
        "adresse": "chaîne"
      }
    },
    "tarif": {
      "id": "entier",
      "categorie": "normal | reduit"
    },
    "prix": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne",
    "pass_id": "entier ou null",
    "_links": {
      "self": {
        "href": "chaîne",
        "method": "GET"
      }
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 6,
    "reference": "BIL-2026-0006",
    "nom_acheteur": "Alice Martin",
    "soiree": {
      "id": 1,
      "nom": "Rock'n'Nancy",
      "date_heure_debut": "2027-06-04T20:00:00",
      "lieu": {
        "id": 2,
        "nom": "Zénith de Nancy",
        "adresse": "Rue du Zénith, 54320 Maxéville"
      }
    },
    "tarif": {
      "id": 1,
      "categorie": "normal"
    },
    "prix": "33.95",
    "devise": "EUR",
    "pass_id": 1,
    "_links": {
      "self": {
        "href": "/api/billets/f3327a8fcc9f8c4736d039d373a5da9ca23fd6b5ef50f3687e79649a8a42421a",
        "method": "GET"
      }
    }
  }
}
```

### 2.25. GET /api/admin/statistiques — Statistiques des ventes

**Accès :** Gestionnaire.

**Exemple de requête :** `GET /api/admin/statistiques`.

**Données envoyées à l’API :** aucune ; aucun corps JSON.

Seules les ventes payées sont comptées. Recettes calculées à partir du prix figé des billets.

**Données renvoyées par l’API — HTTP 200**

Format JSON attendu :

```json
{
  "data": {
    "soirees": [
      {
        "id": "entier",
        "nom": "chaîne",
        "capacite": "entier",
        "places_vendues": "entier",
        "places_disponibles": "entier",
        "recette": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "total_billets_vendus": "entier",
    "recette_totale": "chaîne décimale à 2 chiffres après le point",
    "devise": "chaîne"
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "soirees": [
      {
        "id": 1,
        "nom": "Rock'n'Nancy",
        "capacite": 5000,
        "places_vendues": 1,
        "places_disponibles": 4999,
        "recette": "33.95"
      },
      {
        "id": 2,
        "nom": "Blues Night",
        "capacite": 800,
        "places_vendues": 1,
        "places_disponibles": 799,
        "recette": "35.00"
      },
      {
        "id": 3,
        "nom": "Metal Storm",
        "capacite": 5000,
        "places_vendues": 1,
        "places_disponibles": 4999,
        "recette": "33.95"
      },
      {
        "id": 4,
        "nom": "Chansons d'ici",
        "capacite": 4,
        "places_vendues": 4,
        "places_disponibles": 0,
        "recette": "120.00"
      },
      {
        "id": 5,
        "nom": "Reggae Vibration",
        "capacite": 800,
        "places_vendues": 0,
        "places_disponibles": 800,
        "recette": "0.00"
      },
      {
        "id": 6,
        "nom": "Punk Attitude",
        "capacite": 800,
        "places_vendues": 0,
        "places_disponibles": 800,
        "recette": "0.00"
      },
      {
        "id": 7,
        "nom": "Rock Legends",
        "capacite": 5000,
        "places_vendues": 1,
        "places_disponibles": 4999,
        "recette": "33.95"
      }
    ],
    "total_billets_vendus": 8,
    "recette_totale": "256.85",
    "devise": "EUR"
  }
}
```

### 2.26. POST /api/admin/soirees — Création d’une soirée

**Accès :** Gestionnaire.

**Exemple de requête :** `POST /api/admin/soirees`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "lieu_id": "entier",
  "nom": "chaîne",
  "thematique": "chaîne",
  "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
  "capacite": "entier",
  "spectacles": [
    {
      "titre": "chaîne",
      "description": "chaîne",
      "url_video": "URL",
      "horaire_previsionnel": "HH:MM:SS",
      "artiste_ids": ["entier"],
      "style_ids": ["entier"],
      "images": ["URL"]
    }
  ]
}
```

Exemple envoyé :

```json
{
  "lieu_id": 1,
  "nom": "Nouvelle soirée",
  "thematique": "Soirée blues",
  "date_heure_debut": "2027-06-13T20:00:00",
  "capacite": 800,
  "spectacles": [
    {
      "titre": "Premier concert",
      "description": "Description du premier concert.",
      "url_video": "https://example.com/videos/premier.mp4",
      "horaire_previsionnel": "20:00:00",
      "artiste_ids": [4],
      "style_ids": [9],
      "images": ["https://example.com/images/premier.jpg"]
    },
    {
      "titre": "Deuxième concert",
      "description": "Description du deuxième concert.",
      "url_video": "https://example.com/videos/deuxieme.mp4",
      "horaire_previsionnel": "21:30:00",
      "artiste_ids": [5],
      "style_ids": [2],
      "images": ["https://example.com/images/deuxieme.jpg"]
    }
  ]
}
```

Extension. Tous les champs sont obligatoires. Date dans la période du festival ; capacité entière ≥ 0 ; deux à trois spectacles ; au moins un artiste, un style et une image par spectacle. Références existantes. Exemples de créations indépendants.

**Données renvoyées par l’API — HTTP 201**

Format JSON attendu :

```json
{
  "data": {
    "id": "entier",
    "nom": "chaîne",
    "thematique": "chaîne",
    "date_heure_debut": "AAAA-MM-JJTHH:MM:SS — Europe/Paris",
    "lieu": {
      "id": "entier",
      "nom": "chaîne",
      "adresse": "chaîne"
    },
    "capacite": "entier",
    "places_vendues": "entier",
    "places_disponibles": "entier",
    "complete": "booléen",
    "tarifs": [
      {
        "id": "entier",
        "categorie": "normal | reduit",
        "prix": "chaîne décimale à 2 chiffres après le point"
      }
    ],
    "spectacles": [
      {
        "titre": "chaîne",
        "description": "chaîne",
        "url_video": "URL",
        "horaire_previsionnel": "HH:MM:SS",
        "id": "entier",
        "date": "AAAA-MM-JJ",
        "images": [
          {
            "id": "entier",
            "url": "URL"
          }
        ],
        "artistes": [
          {
            "id": "entier",
            "nom": "chaîne"
          }
        ],
        "styles": [
          {
            "id": "entier",
            "libelle": "chaîne"
          }
        ],
        "soiree_id": "entier"
      }
    ]
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "id": 8,
    "nom": "Nouvelle soirée",
    "thematique": "Soirée blues",
    "date_heure_debut": "2027-06-13T20:00:00",
    "lieu": {
      "id": 1,
      "nom": "L'Autre Canal",
      "adresse": "45 boulevard d'Austrasie, 54000 Nancy"
    },
    "capacite": 800,
    "places_vendues": 0,
    "places_disponibles": 800,
    "complete": false,
    "tarifs": [
      {
        "id": 1,
        "categorie": "normal",
        "prix": "35.00"
      },
      {
        "id": 2,
        "categorie": "reduit",
        "prix": "25.00"
      }
    ],
    "spectacles": [
      {
        "titre": "Premier concert",
        "description": "Description du premier concert.",
        "url_video": "https://example.com/videos/premier.mp4",
        "horaire_previsionnel": "20:00:00",
        "id": 18,
        "date": "2027-06-13",
        "images": [
          {
            "id": 21,
            "url": "https://example.com/images/premier.jpg"
          }
        ],
        "artistes": [
          {
            "id": 4,
            "nom": "Big Joe Mulligan"
          }
        ],
        "styles": [
          {
            "id": 9,
            "libelle": "Blues"
          }
        ],
        "soiree_id": 8
      },
      {
        "titre": "Deuxième concert",
        "description": "Description du deuxième concert.",
        "url_video": "https://example.com/videos/deuxieme.mp4",
        "horaire_previsionnel": "21:30:00",
        "id": 19,
        "date": "2027-06-13",
        "images": [
          {
            "id": 22,
            "url": "https://example.com/images/deuxieme.jpg"
          }
        ],
        "artistes": [
          {
            "id": 5,
            "nom": "Delta Sisters"
          }
        ],
        "styles": [
          {
            "id": 2,
            "libelle": "Blues rock"
          }
        ],
        "soiree_id": 8
      }
    ]
  },
  "_links": {
    "self": {
      "href": "/api/soirees/8",
      "method": "GET"
    }
  }
}
```

### 2.27. POST /api/admin/spectacles — Création d’un spectacle

**Accès :** Gestionnaire.

**Exemple de requête :** `POST /api/admin/spectacles`.

**Données envoyées à l’API — corps JSON**

Format JSON attendu :

```json
{
  "soiree_id": "entier",
  "titre": "chaîne",
  "description": "chaîne",
  "url_video": "URL",
  "horaire_previsionnel": "HH:MM:SS",
  "artiste_ids": ["entier"],
  "style_ids": ["entier"],
  "images": ["URL"]
}
```

Exemple envoyé :

```json
{
  "soiree_id": 2,
  "titre": "Dernier blues",
  "description": "Un concert pour terminer la soirée.",
  "url_video": "https://example.com/videos/dernier-blues.mp4",
  "horaire_previsionnel": "23:00:00",
  "artiste_ids": [4, 5],
  "style_ids": [2, 9],
  "images": ["https://example.com/images/dernier-blues.jpg"]
}
```

Extension. Tous les champs sont obligatoires. Au moins un artiste, un style et une image ; références existantes ; maximum trois spectacles dans la soirée. Exemples de créations indépendants.

**Données renvoyées par l’API — HTTP 201**

Format JSON attendu :

```json
{
  "data": {
    "titre": "chaîne",
    "description": "chaîne",
    "url_video": "URL",
    "horaire_previsionnel": "HH:MM:SS",
    "id": "entier",
    "date": "AAAA-MM-JJ",
    "images": [
      {
        "id": "entier",
        "url": "URL"
      }
    ],
    "artistes": [
      {
        "id": "entier",
        "nom": "chaîne"
      }
    ],
    "styles": [
      {
        "id": "entier",
        "libelle": "chaîne"
      }
    ],
    "soiree_id": "entier"
  },
  "_links": {
    "self": {
      "href": "chaîne",
      "method": "GET"
    },
    "soiree": {
      "href": "chaîne",
      "method": "GET"
    }
  }
}
```

Exemple reçu :

```json
{
  "data": {
    "titre": "Dernier blues",
    "description": "Un concert pour terminer la soirée.",
    "url_video": "https://example.com/videos/dernier-blues.mp4",
    "horaire_previsionnel": "23:00:00",
    "id": 18,
    "date": "2027-06-05",
    "images": [
      {
        "id": 21,
        "url": "https://example.com/images/dernier-blues.jpg"
      }
    ],
    "artistes": [
      {
        "id": 4,
        "nom": "Big Joe Mulligan"
      },
      {
        "id": 5,
        "nom": "Delta Sisters"
      }
    ],
    "styles": [
      {
        "id": 2,
        "libelle": "Blues rock"
      },
      {
        "id": 9,
        "libelle": "Blues"
      }
    ],
    "soiree_id": 2
  },
  "_links": {
    "self": {
      "href": "/api/spectacles/18",
      "method": "GET"
    },
    "soiree": {
      "href": "/api/soirees/2",
      "method": "GET"
    }
  }
}
```

### 2.28. Réponses d’erreur communes

**Données renvoyées par l’API**

| HTTP | Cas                                                                                          |
| ---- | -------------------------------------------------------------------------------------------- |
| 400  | JSON mal formé                                                                               |
| 401  | Authentification absente, invalide ou expirée                                                |
| 403  | Rôle gestionnaire requis                                                                     |
| 404  | Ressource absente, privée pour un autre compte, ou token inconnu                             |
| 409  | E-mail déjà utilisé, panier verrouillé, commande en attente ou refusée, places insuffisantes |
| 415  | Format de corps non accepté                                                                  |
| 422  | Champ inconnu ou invalide, panier vide, règle métier non respectée                           |
| 500  | Erreur interne                                                                               |

Format JSON attendu :

```json
{
  "error": {
    "code": "chaîne",
    "message": "chaîne",
    "details": [
      {
        "champ": "chaîne",
        "message": "chaîne"
      }
    ]
  }
}
```

Exemple reçu — HTTP 422 :

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Certaines données sont invalides.",
    "details": [
      {
        "champ": "quantite",
        "message": "La quantité doit être un entier strictement positif."
      }
    ]
  }
}
```
