<?php
$customCss = $customCss ?? '/public/src/css/finance/dashboard.css';
ob_start();
?>

<div class="finance-app">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h1 class="logo"><i class="fa fa-line-chart"></i> Finance</h1>
        </div>
        <ul class="nav-menu">
            <li class="nav-item active">
                <a href="/dashboard"><i class="fa fa-dashboard"></i> Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a href="/transactions"><i class="fa fa-exchange"></i> Transactions</a>
            </li>
            <li class="nav-item">
                <a href="/accounts"><i class="fa fa-university"></i> Comptes</a>
            </li>
            <li class="nav-item">
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
            <h2>Tableau de bord</h2>
            <div class="user-info">
                <span id="current-date"></span>
            </div>
        </header>

        <!-- KPI Cards -->
        <section class="kpi-grid">
            <div class="kpi-card net-worth">
                <div class="kpi-icon"><i class="fa fa-briefcase"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">Patrimoine Net</span>
                    <span class="kpi-value" id="kpi-net-worth">--</span>
                </div>
            </div>
            <div class="kpi-card income">
                <div class="kpi-icon"><i class="fa fa-arrow-down"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">Revenus du mois</span>
                    <span class="kpi-value" id="kpi-income">--</span>
                </div>
            </div>
            <div class="kpi-card expense">
                <div class="kpi-icon"><i class="fa fa-arrow-up"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">Dépenses du mois</span>
                    <span class="kpi-value" id="kpi-expense">--</span>
                </div>
            </div>
            <div class="kpi-card balance">
                <div class="kpi-icon"><i class="fa fa-balance-scale"></i></div>
                <div class="kpi-content">
                    <span class="kpi-label">Balance</span>
                    <span class="kpi-value" id="kpi-balance">--</span>
                </div>
            </div>
        </section>

        <!-- Météo Financière -->
        <section class="forecast-section">
            <div class="section-header">
                <h3><i class="fa fa-cloud"></i> Météo Financière</h3>
                <span class="forecast-status" id="forecast-status">--</span>
            </div>
            <div class="forecast-content">
                <div class="forecast-chart">
                    <canvas id="forecast-chart"></canvas>
                </div>
                <div class="forecast-summary">
                    <div class="forecast-item">
                        <span class="label">Solde actuel</span>
                        <span class="value" id="forecast-current">--</span>
                    </div>
                    <div class="forecast-item">
                        <span class="label">Revenus à venir</span>
                        <span class="value positive" id="forecast-income">+--</span>
                    </div>
                    <div class="forecast-item">
                        <span class="label">Factures fixes</span>
                        <span class="value negative" id="forecast-fixed">---</span>
                    </div>
                    <div class="forecast-item">
                        <span class="label">Dépenses variables (est.)</span>
                        <span class="value negative" id="forecast-variable">---</span>
                    </div>
                    <div class="forecast-item total">
                        <span class="label">Solde fin de mois</span>
                        <span class="value" id="forecast-end">--</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            <!-- Comptes -->
            <section class="accounts-section">
                <div class="section-header">
                    <h3><i class="fa fa-university"></i> Mes Comptes</h3>
                    <a href="/accounts" class="btn-link">Voir tout</a>
                </div>
                <div class="accounts-list" id="accounts-list">
                    <!-- Loaded by JS -->
                </div>
            </section>

            <!-- Abonnements à venir -->
            <section class="upcoming-section">
                <div class="section-header">
                    <h3><i class="fa fa-calendar"></i> Prochaines échéances</h3>
                    <a href="/subscriptions" class="btn-link">Voir tout</a>
                </div>
                <div class="upcoming-list" id="upcoming-list">
                    <!-- Loaded by JS -->
                </div>
            </section>
        </div>

        <!-- Dernières transactions -->
        <section class="recent-transactions">
            <div class="section-header">
                <h3><i class="fa fa-list"></i> Dernières transactions</h3>
                <a href="/transactions" class="btn-link">Voir tout</a>
            </div>
            <div class="transactions-list" id="recent-transactions">
                <!-- Loaded by JS -->
            </div>
        </section>

        <!-- Budget par catégorie -->
        <section class="budget-section">
            <div class="section-header">
                <h3><i class="fa fa-pie-chart"></i> Budgets du mois</h3>
                <a href="/categories" class="btn-link">Gérer</a>
            </div>
            <div class="budget-list" id="budget-list">
                <!-- Loaded by JS -->
            </div>
        </section>
    </main>
</div>

<!-- Modal pour ajouter une transaction rapide -->
<div class="modal" id="quick-transaction-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nouvelle transaction</h3>
            <button class="modal-close" onclick="closeModal('quick-transaction-modal')">&times;</button>
        </div>
        <form id="quick-transaction-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="expense">Dépense</option>
                        <option value="income">Revenu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Montant</label>
                    <input type="number" name="amount" step="0.01" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Compte</label>
                    <select name="account_id" id="form-account" required></select>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category_id" id="form-category"></select>
                </div>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('quick-transaction-modal')">Annuler</button>
                <button type="submit" class="btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Action Button -->
<button class="fab" onclick="openModal('quick-transaction-modal')" title="Nouvelle transaction">
    <i class="fa fa-plus"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>
