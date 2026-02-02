<?php
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>

<a id="btn-logout" href="/logout"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160zM566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L438.6 169.3C426.1 156.8 405.8 156.8 393.3 169.3C380.8 181.8 380.8 202.1 393.3 214.6L466.7 288L256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L466.7 352L393.3 425.4C380.8 437.9 380.8 458.2 393.3 470.7C405.8 483.2 426.1 483.2 438.6 470.7L566.6 342.7z"/></svg></a>
<div class="dashboard">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p>Gérez votre portfolio depuis cet espace d'administration</p>
        </header>


        <ul class="dashboard-grid">
            <li class="dashboard-card">
                <a href="/dashboard/diploma">
                    <div class="card-icon diploma">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <div class="card-content">
                        <h2>Diplômes</h2>
                        <p>Gérez vos formations et certifications</p>
                    </div>
                </a>
            </li>
            <li class="dashboard-card">
                <a href="/dashboard/projects">
                    <div class="card-icon projects">
                        <i class="fa fa-folder-open"></i>
                    </div>
                    <div class="card-content">
                        <h2>Projets</h2>
                        <p>Ajoutez et modifiez vos réalisations</p>
                    </div>
                </a>
            </li>
            <li class="dashboard-card">
                <a href="/dashboard/technologies">
                    <div class="card-icon technologies">
                        <i class="fa fa-code"></i>
                    </div>
                    <div class="card-content">
                        <h2>Technologies</h2>
                        <p>Listez vos compétences techniques</p>
                    </div>
                </a>
            </li>
            <li class="dashboard-card">
                <a href="/dashboard/competences">
                    <div class="card-icon competences">
                        <i class="fa fa-star"></i>
                    </div>
                    <div class="card-content">
                        <h2>Compétences</h2>
                        <p>Mettez en avant vos savoir-faire</p>
                    </div>
                </a>
            </li>
            <li class="dashboard-card">
                <a href="/dashboard/experiences">
                    <div class="card-icon experiences">
                        <i class="fa fa-briefcase"></i>
                    </div>
                    <div class="card-content">
                        <h2>Expériences</h2>
                        <p>Décrivez votre parcours professionnel</p>
                    </div>
                </a>
            </li>
            <li class="dashboard-card">
                <a href="/dashboard/contact">
                    <div class="card-icon contact">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div class="card-content">
                        <h2>Contact</h2>
                        <p>Consultez les messages reçus</p>
                    </div>
                </a>
            </li>
            <li class="dashboard-card">
                <a href="/dashboard/users">
                    <div class="card-icon users">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h2>Utilisateurs</h2>
                        <p>Gérez les comptes utilisateurs</p>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>