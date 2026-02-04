<?php
ob_start();
?>

<div class="finance-app">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h1 class="logo"><i class="fa fa-line-chart"></i> Finance</h1>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="/dashboard"><i class="fa fa-dashboard"></i> Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a href="/transactions"><i class="fa fa-exchange"></i> Transactions</a>
            </li>
            <li class="nav-item">
                <a href="/accounts"><i class="fa fa-university"></i> Comptes</a>
            </li>
            <li class="nav-item active">
                <a href="/subscriptions"><i class="fa fa-repeat"></i> Abonnements</a>
            </li>
            <li class="nav-item">
                <a href="/categories"><i class="fa fa-tags"></i> Catégories</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/logout" class="logout-btn"><i class="fa fa-sign-out"></i> Déconnexion</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-bar">
            <h2><i class="fa fa-repeat"></i> Abonnements</h2>
            <div class="top-bar-actions">
                <div class="view-toggle">
                    <button id="view-list" class="toggle-btn active" onclick="setView('list')">
                        <i class="fa fa-list"></i>
                    </button>
                    <button id="view-calendar" class="toggle-btn" onclick="setView('calendar')">
                        <i class="fa fa-calendar"></i>
                    </button>
                </div>
                <button class="btn btn-primary" onclick="openModal('subscription-modal')">
                    <i class="fa fa-plus"></i> Nouvel abonnement
                </button>
            </div>
        </header>

        <!-- Stats -->
        <section class="kpi-grid kpi-grid-3">
            <div class="kpi-card danger">
                <div class="kpi-icon"><i class="fa fa-credit-card"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">Charges mensuelles</span>
                    <span class="kpi-value" id="stat-monthly">0,00 €</span>
                </div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-icon"><i class="fa fa-calendar-check-o"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">À venir (7 jours)</span>
                    <span class="kpi-value" id="stat-upcoming">0</span>
                </div>
            </div>
            <div class="kpi-card primary">
                <div class="kpi-icon"><i class="fa fa-list"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">Abonnements actifs</span>
                    <span class="kpi-value" id="stat-count">0</span>
                </div>
            </div>
        </section>

        <!-- List View -->
        <section id="subscriptions-list" class="card">
            <div class="card-header">
                <h3>Abonnements actifs</h3>
                <select id="filter-frequency" class="select-input" onchange="loadSubscriptions()">
                    <option value="">Toutes les fréquences</option>
                    <option value="monthly">Mensuel</option>
                    <option value="yearly">Annuel</option>
                    <option value="weekly">Hebdomadaire</option>
                    <option value="daily">Quotidien</option>
                </select>
            </div>
            <div class="card-body">
                <div class="subscriptions-grid" id="subscriptions-container">
                    <!-- Subscriptions will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Calendar View -->
        <section id="subscriptions-calendar" class="card" style="display: none;">
            <div class="card-header">
                <h3>Calendrier des échéances</h3>
                <div class="calendar-nav">
                    <button class="nav-btn" onclick="navigateMonth(-1)">
                        <i class="fa fa-chevron-left"></i>
                    </button>
                    <span id="calendar-month"></span>
                    <button class="nav-btn" onclick="navigateMonth(1)">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="calendar-container">
                    <div class="calendar-header-row">
                        <div>Lun</div>
                        <div>Mar</div>
                        <div>Mer</div>
                        <div>Jeu</div>
                        <div>Ven</div>
                        <div>Sam</div>
                        <div>Dim</div>
                    </div>
                    <div id="calendar-grid" class="calendar-grid">
                        <!-- Calendar days will be rendered here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Upcoming Section -->
        <section class="card">
            <div class="card-header">
                <h3><i class="fa fa-bell"></i> Prochaines échéances</h3>
            </div>
            <div class="card-body">
                <div id="upcoming-list" class="upcoming-list">
                    <!-- Upcoming subscriptions will be loaded here -->
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Subscription Modal -->
<div id="subscription-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="subscription-modal-title">Nouvel abonnement</h3>
            <button class="modal-close" onclick="closeModal('subscription-modal')">&times;</button>
        </div>
        <form id="subscription-form">
            <input type="hidden" id="subscription-id">
            
            <div class="form-group">
                <label for="form-name">Nom</label>
                <input type="text" id="form-name" name="name" class="form-input" required placeholder="Netflix, Spotify...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-amount">Montant</label>
                    <input type="number" id="form-amount" name="amount" class="form-input" step="0.01" required placeholder="9.99">
                </div>
                <div class="form-group">
                    <label for="form-frequency">Fréquence</label>
                    <select id="form-frequency" name="frequency" class="form-input" required>
                        <option value="monthly">Mensuel</option>
                        <option value="yearly">Annuel</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="daily">Quotidien</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-account">Compte</label>
                    <select id="form-account" name="account_id" class="form-input" required>
                        <!-- Accounts loaded dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="form-category">Catégorie</label>
                    <select id="form-category" name="category_id" class="form-input">
                        <!-- Categories loaded dynamically -->
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-next-due">Prochaine échéance</label>
                    <input type="date" id="form-next-due" name="next_due_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="form-status">Statut</label>
                    <select id="form-status" name="is_active" class="form-input">
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="form-icon">Icône</label>
                <select id="form-icon" name="icon" class="form-input">
                    <option value="">-- Aucune --</option>
                    <option value="fa-film">🎬 Film / Streaming</option>
                    <option value="fa-music">🎵 Musique</option>
                    <option value="fa-gamepad">🎮 Jeux</option>
                    <option value="fa-cloud">☁️ Cloud</option>
                    <option value="fa-mobile">📱 Mobile</option>
                    <option value="fa-wifi">📶 Internet</option>
                    <option value="fa-bolt">⚡ Électricité</option>
                    <option value="fa-tint">💧 Eau</option>
                    <option value="fa-fire">🔥 Gaz</option>
                    <option value="fa-home">🏠 Logement</option>
                    <option value="fa-car">🚗 Transport</option>
                    <option value="fa-heart">❤️ Santé</option>
                    <option value="fa-shield">🛡️ Assurance</option>
                    <option value="fa-book">📚 Éducation</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('subscription-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>
