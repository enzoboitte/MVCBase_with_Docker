<?php
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>

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
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>