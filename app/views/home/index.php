<?php ob_start(); ?>

<!-- Navigation -->
<nav class="navbar">
    <div class="navbar-content">
        <a href="/" class="navbar-logo">BOITTE Enzo<span>.</span></a>
        <ul class="navbar-links">
            <li><a href="#skills">Compétences</a></li>
            <li><a href="#education">Parcours</a></li>
            <li><a href="#experience">Expériences</a></li>
            <li><a href="#projects">Projets</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">
            Disponible pour de nouveaux projets
        </div>
        <h1 class="hero-title">
            Bonjour, je suis<br>
            <span class="highlight">Enzo BOITTE</span><br>
            <span style="font-size:40px;">Développeur FullStack &amp; DevOps.</span>
        </h1>
        <p class="hero-description">
            Passionné par le développement et les nouvelles technologies depuis l'age des mes 10ans. 
            Je crée des expériences digitales modernes, performantes et intuitives.
        </p>
        <div class="hero-buttons">
            <a href="#projects" class="btn btn-primary">
                <!--<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"></path><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"></polygon></svg>-->
                Voir mes projets
            </a>
            <a href="#contact" class="btn btn-secondary">
                <!--<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>-->
                Me contacter
            </a>
        </div>
    </div>
    <div class="hero-scroll">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
    </div>
</section>

<!-- Skills Section -->
<div class="skills-section">
    <section id="skills" class="section">
        <div class="section-header fade-in">
            <h2 class="section-title">Compétences</h2>
            <p class="section-subtitle">Les technologies que j'utilise au quotidien pour créer des applications web modernes.</p>
        </div>
        
        <div class="skills-grid" id="skills-grid">
            <!-- Les compétences seront chargées dynamiquement -->
        </div>
    </section>
</div>

<!-- Education Section -->
<section id="education" class="section education-section">
    <div class="section-header fade-in">
        <h2 class="section-title">Parcours Scolaire</h2>
        <p class="section-subtitle">Mon parcours académique et mes formations.</p>
    </div>
    
    <div class="education-timeline" id="education-timeline">
        <!-- Les diplômes seront chargés dynamiquement -->
    </div>
</section>

<!-- Experience Section -->
<div class="experience-section">
    <section id="experience" class="section">
        <div class="section-header fade-in">
            <h2 class="section-title">Expériences Professionnelles</h2>
            <p class="section-subtitle">Mon parcours professionnel et mes missions en entreprise.</p>
        </div>
        
        <div class="experience-list" id="experience-list">
            <!-- Les expériences seront chargées dynamiquement -->
        </div>
    </section>
</div>

<!-- Projects Section -->
<section id="projects" class="section">
    <div class="section-header fade-in">
        <h2 class="section-title">Projets</h2>
        <p class="section-subtitle">Découvrez quelques-uns des projets sur lesquels j'ai travaillé.</p>
    </div>
    
    <div class="projects-grid" id="projects-grid">
        <!-- Les projets seront chargés dynamiquement -->
    </div>
</section>

<!-- Popup Projet -->
<div id="project-popup" class="popup">
    <div class="popup_content">
        <div class="popup_header">
            <h2 class="popup_title" id="popup-project-title"></h2>
            <span class="popup_close">&times;</span>
        </div>
        <div class="popup_body">
            <div id="popup-project-images"></div>
            <div class="popup-images-content">
                <div id="popup-images-nav" class="popup-images-nav"></div>
            </div>
            <div class="popup-content-wrapper">
                <p id="popup-project-description"></p>
                <div id="popup-project-techs"></div>
                <div id="popup-project-link"></div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Section -->
<section id="contact" class="section contact-section">
    <div class="section-header fade-in">
        <h2 class="section-title">Contact</h2>
        <p class="section-subtitle">Vous avez un projet en tête ? N'hésitez pas à me contacter !</p>
    </div>
    
    <div class="contact-content fade-in">
        <div class="contact-info">
            <h3>Travaillons ensemble</h3>
            <p>Je suis toujours ouvert à discuter de nouveaux projets, d'idées créatives ou d'opportunités pour être partie prenante de vos visions.</p>
            <div class="contact-links">
                <a href="mailto:enzoboitte63000@gmail.com" class="contact-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    enzoboitte63000@gmail.com
                </a>
                <a href="https://github.com/enzoboitte" class="contact-link" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
                    GitHub
                </a>
                <a href="https://linkedin.com/in/enzo-boitte" class="contact-link" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                    LinkedIn
                </a>
            </div>
        </div>
        
        <form class="contact-form" data-api-endpoint="/contact" data-api-method="POST">
            <div class="form-group">
                <label for="name">Nom</label>
                <input type="text" id="name" name="name" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
                <label for="subject">Sujet</label>
                <input type="text" id="subject" name="subject" placeholder="Sujet du message" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Votre message..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Envoyer le message
            </button>
        </form>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-links">
            <a href="https://github.com/enzoboitte" target="_blank">GitHub</a>
            <a href="https://linkedin.com/in/enzo-boitte" target="_blank">LinkedIn</a>
            <a href="mailto:enzoboitte63000@gmail.com">Email</a>
        </div>
        <p>&copy; <?= date('Y') ?> Enzo BOITTE. Tous droits réservés.</p>
    </div>
</footer>

<?php $content = ob_get_clean(); ?>
<?php 
$customCss = ['/public/src/css/home.css', '/public/src/css/home-popup.css'];
$customJs = '/public/src/js/home.js';
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
