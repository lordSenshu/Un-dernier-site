# ✅ Projet Web — "Prix des Carburants" (L2-I S4 2025-2026)

> Deadline livrables : **lundi 4 mai 2026**  
> Soutenances : **semaine 21** (18, 19 ou 21 mai 2026)  
> Vidéo démo : **avant le mardi 5 mai 2026**

---

## 🗂️ Organisation & Gestion de projet

- [ ] Choisir un nom de site + créer un logo + créer un favicon
- [ ] Définir la charte graphique (couleurs, typographies)
- [ ] Créer les mockups des pages principales (Figma / Mockitt / Canva)
- [ ] Répartir les tâches entre les membres du binôme
- [ ] Créer un diagramme de Gantt
- [ ] Créer un diagramme de cas d'utilisations (UML)
- [ ] Définir l'arborescence (plan du site)

---

## 🧪 Partie 1 — Prise en main des API Web (à valider en semaine 15 / TD #11)

### Page « tech » (page annexe dédiée, lien dans le footer)

- [ ] Appeler l'API Ghibli : `https://ghibliapi.vercel.app/films`
  - [ ] Choisir un film **aléatoirement** à chaque rafraîchissement
  - [ ] Afficher 1 ou 2 images du film (avec légendes)
  - [ ] Afficher le titre (en français et en japonais, avec `lang="ja"`)
  - [ ] Afficher l'année de sortie et la description
- [ ] Géolocalisation par adresse IP du visiteur
  - [ ] Appeler `https://ipinfo.io/{IP}/geo` ou l'API avec token
  - [ ] Afficher la position géographique estimée de l'internaute

---

## ⛽ Partie 2 — Prix des carburants

### Sélection géographique
- [ ] Carte interactive des régions (tags HTML `<map>`, `<usemap>`, `<area>`)
- [ ] Sélection du département (ergonomie au choix)
- [ ] Liste déroulante de sélection de la ville

### Données statiques (CSV)
- [ ] Intégrer les fichiers CSV région / département / ville (source data.gouv.fr)
- [ ] Lire et exploiter ces fichiers en PHP

### Données dynamiques (API carburants)
- [ ] Récupérer les prix via l'API officielle :
  - `https://www.prix-carburants.gouv.fr/rubrique/opendata/`
  - `https://data.economie.gouv.fr/explore/dataset/prix-des-carburants-en-france-flux-instantane-v2/api/`
- [ ] Traiter les réponses **JSON** (format principal)
- [ ] Traiter au moins une réponse en **XML** (obligatoire pour valider les 2 formats)
- [ ] Afficher la liste des stations-service avec leurs prix

### Fonctionnalités ergonomiques
- [ ] Afficher les stations d'un département entier
- [ ] Afficher les stations à proximité via géolocalisation IP
- [ ] Permettre de filtrer par type de carburant (SP95, SP98, Gazole, E10…)
- [ ] Option affichage simplifié vs détaillé
- [ ] (Bonus) Afficher les tendances de prix sur l'année écoulée

---

## 💾 Partie 3 — Stockage

### Côté serveur (PHP)
- [ ] Enregistrer dans un fichier **CSV** chaque ville consultée (avec horodatage)
- [ ] Générer un **histogramme** des villes les plus consultées (rubrique "Statistiques")
- [ ] Afficher le nombre total de visiteurs dans les stats

### Côté client (cookies)
- [ ] Sauvegarder la **dernière ville consultée** dans un cookie (ville + date)
- [ ] Relire le cookie au prochain chargement pour pré-remplir la ville

---

## 🍪 Partie 4 — Compléments TD (cookies)

- [ ] Mode jour / mode nuit via images cliquables
- [ ] Stocker le choix jour/nuit dans un **cookie** (`set_cookie`)
- [ ] Passer le mode en paramètre URL
- [ ] Relire et appliquer le cookie au chargement suivant
- [ ] Supprimer le cookie si la valeur est invalide
- [ ] Limiter le cookie à l'espace du projet (`/~login/`) et non tout le serveur

---

## 🌐 Technique & Qualité

- [ ] Architecture 3 tiers : navigateur / serveur PHP / APIs externes
- [ ] HTML5 valide (W3C validator)
- [ ] CSS3 valide (W3C validator)
- [ ] Validation accessibilité (TD #10)
- [ ] Validation éco-conception / performances (TD #10)
- [ ] Validation checklink (liens cassés)
- [ ] Toutes les requêtes vers les API passent **par le serveur PHP** (pas de fetch JS direct)
- [ ] Commentaires PHPDoc sur toutes les fonctions PHP
- [ ] Générer la documentation HTML avec **Doxygen**

---

## 📦 Livrables

- [ ] Site en ligne (même URL que les TD)
- [ ] Archive `grp_prj_###_X_Y.zip` avec arborescence fonctionnelle
- [ ] Documentation PHP au format HTML (générée par Doxygen)
- [ ] Mini-rapport (5–10 pages, format ODT ou DOCX + PDF)
  - [ ] Membres de l'équipe
  - [ ] Répartition des tâches + Diagramme de Gantt
  - [ ] Diagramme de cas d'utilisations
  - [ ] Choix techniques
  - [ ] Plan du site
  - [ ] Mockups des pages
  - [ ] Sections numérotées automatiquement
- [ ] Fichier `readme.md` (noms des auteurs, URLs du site)
- [ ] Vidéo de démo scénarisée (3 min max, temps de parole équilibré) → avant 5 mai 2026
- [ ] Support de soutenance (5 slides max) → soutenance semaine 21

---

## 🔗 APIs & Ressources utiles

| Usage | URL |
|---|---|
| Films Ghibli | https://ghibliapi.vercel.app/ |
| Géoloc IP | https://ipinfo.io/{IP}/geo |
| Géoloc IP (alt) | https://api.ip2location.io/?ip=... |
| Prix carburants | https://www.prix-carburants.gouv.fr/rubrique/opendata/ |
| Prix carburants API | https://data.economie.gouv.fr/explore/dataset/prix-des-carburants-en-france-flux-instantane-v2/api/ |
| Communes / régions | https://www.data.gouv.fr/fr/datasets/regions-departements-villes-et-villages-de-france-et-doutremer/ |
| Hébergement | https://www.alwaysdata.com/fr/ |
| Mockups | https://www.figma.com / https://www.canva.com |
| Fonds de cartes | http://education.ign.fr/ressources/fonds-de-cartes |
