<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1><i class="fa fa-euro"></i> FinanceApp</h1>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard"><i class="fa fa-dashboard"></i> Tableau de bord</a>
            <a href="/accounts"><i class="fa fa-bank"></i> Comptes</a>
            <a href="/transactions"><i class="fa fa-exchange"></i> Transactions</a>
            <a href="/subscriptions" class="active"><i class="fa fa-refresh"></i> Abonnements</a>
            <a href="/categories"><i class="fa fa-tags"></i> Catégories</a>
        </nav>
        <div class="sidebar-footer">
            <a href="/logout"><i class="fa fa-sign-out"></i> Déconnexion</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <div class="page-title">
                <h1><i class="fa fa-refresh"></i> Abonnements</h1>
                <p>Gérez vos paiements récurrents</p>
            </div>
            <div class="page-actions">
                <div class="view-toggle">
                    <button id="view-list" class="active" onclick="setView('list')">
                        <i class="fa fa-list"></i> Liste
                    </button>
                    <button id="view-calendar" onclick="setView('calendar')">
                        <i class="fa fa-calendar"></i> Calendrier
                    </button>
                </div>
                <button class="btn btn-primary" onclick="openModal('subscription-modal')">
                    <i class="fa fa-plus"></i> Nouvel abonnement
                </button>
            </div>
        </header>

        <!-- Stats -->
        <section class="subs-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--color-danger);">
                    <i class="fa fa-credit-card"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Charges mensuelles</span>
                    <span id="stat-monthly" class="stat-value">0,00 €</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--color-warning);">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">À venir (7 jours)</span>
                    <span id="stat-upcoming" class="stat-value">0</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--primary-color);">
                    <i class="fa fa-list"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Abonnements actifs</span>
                    <span id="stat-count" class="stat-value">0</span>
                </div>
            </div>
        </section>

        <!-- List View -->
        <section id="subscriptions-list" class="content-card">
            <div class="card-header">
                <h3>Abonnements actifs</h3>
                <div class="filter-group">
                    <select id="filter-frequency" onchange="loadSubscriptions()">
                        <option value="">Toutes les fréquences</option>
                        <option value="monthly">Mensuel</option>
                        <option value="yearly">Annuel</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="daily">Quotidien</option>
                    </select>
                </div>
            </div>
            
            <div class="subscriptions-grid" id="subscriptions-container">
                <!-- Subscriptions will be loaded here -->
            </div>
        </section>

        <!-- Calendar View -->
        <section id="subscriptions-calendar" class="content-card" style="display: none;">
            <div class="card-header">
                <h3>Calendrier des échéances</h3>
                <div class="calendar-nav">
                    <button id="prev-month" onclick="navigateMonth(-1)">
                        <i class="fa fa-chevron-left"></i>
                    </button>
                    <span id="calendar-month"></span>
                    <button id="next-month" onclick="navigateMonth(1)">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="calendar-container">
                <div class="calendar-header">
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
        </section>

        <!-- Upcoming Section -->
        <section class="content-card">
            <div class="card-header">
                <h3><i class="fa fa-bell"></i> Prochaines échéances</h3>
            </div>
            <div id="upcoming-list" class="upcoming-list">
                <!-- Upcoming subscriptions will be loaded here -->
            </div>
        </section>
    </main>
</div>

<!-- Subscription Modal -->
<div id="subscription-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="subscription-modal-title">Nouvel abonnement</h2>
            <button class="modal-close" onclick="closeModal('subscription-modal')">&times;</button>
        </div>
        <form id="subscription-form">
            <input type="hidden" id="subscription-id">
            
            <div class="form-group">
                <label for="form-name">Nom</label>
                <input type="text" id="form-name" name="name" required placeholder="Netflix, Spotify...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-amount">Montant</label>
                    <input type="number" id="form-amount" name="amount" step="0.01" required placeholder="9.99">
                </div>
                <div class="form-group">
                    <label for="form-frequency">Fréquence</label>
                    <select id="form-frequency" name="frequency" required>
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
                    <select id="form-account" name="account_id" required>
                        <!-- Accounts loaded dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="form-category">Catégorie</label>
                    <select id="form-category" name="category_id">
                        <!-- Categories loaded dynamically -->
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-next-due">Prochaine échéance</label>
                    <input type="date" id="form-next-due" name="next_due_date" required>
                </div>
                <div class="form-group">
                    <label for="form-status">Statut</label>
                    <select id="form-status" name="is_active">
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="form-icon">Icône</label>
                <select id="form-icon" name="icon">
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

<link rel="stylesheet" href="/src/css/finance/subscriptions.css">
<script src="/src/js/finance/subscriptions.js"></script>