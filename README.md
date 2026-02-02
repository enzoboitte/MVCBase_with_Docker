# MVC Base PHP avec Docker

Un framework MVC léger en PHP inspiré de **Symfony**, avec un système de routing par attributs et une configuration Docker prête à l'emploi.

---

## Structure du projet

```
├── app/
│   ├── controllers/    # Contrôleurs (logique métier)
│   ├── models/         # Modèles (accès BDD)
│   └── views/          # Vues (templates PHP)
├── core/
│   ├── Controller.php  # Classe Controller de base
│   ├── Router.php      # Routeur + Attributs de route
│   └── template/       # Templates de base
├── public/
│   └── src/            # Assets (CSS, JS, images)
├── sql/                # Scripts SQL
├── docker/             # Configuration Docker
├── docker-compose.yml  # Orchestration Docker
└── index.php           # Point d'entrée
```

---

## Démarrage rapide

### Avec Docker (recommandé)

```bash
# Cloner le projet
git clone https://github.com/enzoboitte/MVCBase_with_Docker.git mon-projet
cd mon-projet

# Lancer les conteneurs
docker-compose up -d

# Ou utiliser le script de démarrage
./start.sh
```

**Accès :**
- Application : http://localhost:8080
- phpMyAdmin : http://localhost:8081

### Sans Docker

Configurer un serveur Apache/Nginx avec PHP 8.1+ pointant vers la racine du projet.

---

## Système de Routing (style Symfony)

Le routing utilise des **attributs PHP 8** comme Symfony :

### Définir une route

```php
<?php

class HomeController extends Controller
{
    #[CRoute('/', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'Accueil'
        ]);
    }

    #[CRoute('/about', CHTTPMethod::GET)]
    public function about(): void
    {
        $this->view('home/about');
    }
}
```

### Routes avec paramètres

```php
#[CRoute('/user/{id}', CHTTPMethod::GET)]
public function show(string $id): void
{
    $this->view('user/show', ['id' => $id]);
}

#[CRoute('/user/{id}/edit', CHTTPMethod::POST)]
public function update(string $id): void
{
    // Traitement...
}
```

### Méthodes HTTP disponibles

- `CHTTPMethod::GET`
- `CHTTPMethod::POST`
- `CHTTPMethod::PUT`
- `CHTTPMethod::DELETE`

---

## Le Controller

Tous les contrôleurs héritent de la classe `Controller` :

```php
class MonController extends Controller
{
    // Afficher une vue
    $this->view('dossier/fichier', ['data' => $value]);
    
    // Redirection
    $this->redirect('/autre-page');
    
    // Réponse JSON (API)
    $this->json(['status' => 'ok']);
}
```

---

## Les Vues

Les vues sont dans `app/views/` et utilisent un layout :

```php
<!-- app/views/home/index.php -->
<h1><?= $title ?></h1>
<p>Contenu de la page</p>
```

---

## Configuration Docker

### Services inclus

| Service    | Port  | Description         |
|------------|-------|---------------------|
| web        | 8080  | Apache + PHP 8      |
| db         | 3306  | MySQL 8.0           |
| phpmyadmin | 8081  | Interface BDD       |

### Variables d'environnement

```env
DB_HOST=db
DB_PORT=3306
DB_DATABASE=portfolio
DB_USERNAME=userenzo
DB_PASSWORD=123456789
```

### Commandes utiles

```bash
# Démarrer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f web

# Accéder au conteneur PHP
docker exec -it webgestion_php bash

# Reconstruire les images
docker-compose build --no-cache
```

---

## Créer un nouveau contrôleur

1. Créer le fichier dans `app/controllers/` :

```php
<?php
// app/controllers/ArticleController.php

class ArticleController extends Controller
{
    #[CRoute('/articles', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('article/index', [
            'title' => 'Articles'
        ]);
    }

    #[CRoute('/article/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $this->view('article/show', ['id' => $id]);
    }
}
```

2. Créer la vue dans `app/views/article/index.php`

3. C'est tout ! Les routes sont auto-découvertes

---

## Système de Formulaires & Tableaux API

Le framework inclut un système JavaScript automatique pour connecter formulaires et tableaux à une API REST via des **attributs HTML data-api-***.

### Tableaux dynamiques (GET)

Affichez des données depuis une API automatiquement :

```html
<table id="diplomaList" data-api-endpoint="/diploma" data-api-method="GET">
    <thead>
        <tr></tr> <!-- Headers générés automatiquement -->
    </thead>
    <tbody></tbody> <!-- Données injectées ici -->
</table>
```

**Fonctionnalités automatiques :**
- Génération des colonnes depuis les clés JSON
- Boutons **Update** et **Delete** ajoutés automatiquement
- Suppression via API avec confirmation

### Formulaires API (POST/PUT)

#### Créer un élément (POST)

```html
<form data-api-endpoint="/diploma" data-api-method="POST" data-api-action="reloadTable">
    <input type="text" name="name" required>
    <input type="text" name="school" required>
    <textarea name="description"></textarea>
    <button type="submit">Ajouter</button>
</form>

<script>
    // Callback appelé après soumission réussie
    async function reloadTable(form, data) {
        await handleTable(document.getElementById('diplomaList'));
    }
</script>
```

#### Modifier un élément (PUT)

Le formulaire se pré-remplit automatiquement avec les données existantes :

```html
<form data-api-endpoint="/diploma/<?= $id ?>" data-api-method="PUT">
    <input type="text" name="name" required>
    <input type="text" name="school" required>
    <button type="submit">Modifier</button>
</form>
```

### Attributs disponibles

| Attribut | Description |
|----------|-------------|
| `data-api-endpoint` | URL de l'API (ex: `/diploma`, `/user/5`) |
| `data-api-method` | Méthode HTTP : `GET`, `POST`, `PUT`, `DELETE` |
| `data-api-action` | Nom de la fonction callback après soumission |

### API côté serveur

Le contrôleur doit retourner du JSON :

```php
class DiplomaController extends Controller
{
    #[CRoute('/diploma', CHTTPMethod::GET)]
    public function list(): void
    {
        $diplomas = Diploma::all();
        $this->json(['data' => $diplomas]);
    }

    #[CRoute('/diploma', CHTTPMethod::POST)]
    public function create(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $diploma = Diploma::create($data);
        $this->json(['success' => true, 'data' => $diploma]);
    }

    #[CRoute('/diploma/{id}', CHTTPMethod::PUT)]
    public function update(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        Diploma::update($id, $data);
        $this->json(['success' => true]);
    }

    #[CRoute('/diploma/{id}', CHTTPMethod::DELETE)]
    public function delete(string $id): void
    {
        Diploma::delete($id);
        $this->json(['success' => true]);
    }
}
```

---

## License

MIT