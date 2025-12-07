Apptuteur - README

Dorian DOBRANOWSKI
Ibrahima Kalil BAH

1. INSTALLATION DU PROJET

Prérequis :

Docker & Docker Compose installés

Se placer dans le dossier apptuteur dans un terminal

Lancer le projet :
docker-compose up -d --build

Installer les dépendances :
docker exec -it apptuteur-php composer install

Créer la base de données :
docker exec -it apptuteur-php php bin/console doctrine:database:create

Lancer les migrations :
docker exec -it apptuteur-php php bin/console doctrine:migrations:migrate

Créer un tuteur via API Platform :
Aller sur : http://localhost:8000/api

Endpoint : POST /api/tuteurs

Exemple de JSON :
{
"nom": "Durand",
"prenom": "Alice",
"email": "alice@example.com
",
"telephone": "0601020304",
"motDePasse": "test"
}

/!\ La base de données Docker :
La base MySQL est créée automatiquement avec ces identifiants :
host : db — database : symfony_db — user : user — password : password

2. CONNEXION

Page de connexion :
http://localhost:8000/login

Identifiants :

Email créé dans Swagger

Mot de passe quelconque

En cas de succès → redirection vers /dashboard.
Sinon → message d'erreur

3. FONCTIONNALITÉS DISPONIBLES

CONNEXION / DÉCONNEXION :
- Stockage du tuteur en session
- Vérification sur toutes les routes protégées

CRUD ÉTUDIANTS :
- Ajouter, modifier, supprimer un étudiant
- Confirmation de suppression via SweetAlert
- Affichage des étudiants du tuteur connecté

CRUD VISITES :
- Liste des visites d’un étudiant
- Ajout d’une visite (statut prérempli à "prévue")
- Modification du contenu
- Suppression avec confirmation
- Filtre par statut : prévue / réalisée / annulée
- Tri des visites par date (ascendant/descendant)
- Page de compte-rendu
- Export PDF via Dompdf

4. SÉCURITÉ :
   Le projet inclut plusieurs mesures de sécurité :

- Vérifications d’accès
- Un tuteur ne peut voir/modifier que ses étudiants
- Idem pour les visites
- Protection contre XSS
- HtmlSanitizer appliqué sur les contenus texte sensibles
- CSRF Protection
- Activé pour les formulaires Symfony
- Sessions sécurisées
- Cookies SameSite=lax, configuration Symfony par défaut

À propos du fichier .env
Le fichier .env versionné contient uniquement :

- un APP_SECRET de développement
- une DATABASE_URL locale utilisée par Docker

5. Structure du projet

apptuteur/
├── bin/
├── config/
├── migrations/
├── public/
├── src/
├── templates/
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── symfony.lock
└── README.md
