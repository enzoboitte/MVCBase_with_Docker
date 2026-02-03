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
            <li class="nav-item active">
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

    <main class="main-content">
        <header class="top-bar">
            <h2>Transactions</h2>
            <button class="btn-primary" onclick="openModal('transaction-modal')">
                <i class="fa fa-plus"></i> Nouvelle transaction
            </button>
        </header>

        <!-- Filtres -->
        <section class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Mois</label>
                    <input type="month" id="filter-month">
                </div>
                <div class="filter-group">
                    <label>Compte</label>
                    <select id="filter-account">
                        <option value="">Tous les comptes</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Catégorie</label>
                    <select id="filter-category">
                        <option value="">Toutes les catégories</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Type</label>
                    <select id="filter-type">
                        <option value="">Tous</option>
                        <option value="income">Revenus</option>
                        <option value="expense">Dépenses</option>
                        <option value="transfer">Virements</option>
                    </select>
                </div>
                <button class="btn-secondary" onclick="applyFilters()">
                    <i class="fa fa-filter"></i> Filtrer
                </button>
            </div>
        </section>

        <!-- Stats du mois -->
        <div class="month-stats">
            <div class="stat-card income">
                <div class="stat-icon"><i class="fa fa-arrow-down"></i></div>
                <div class="stat-content">
                    <span class="stat-label">Revenus</span>
                    <span class="stat-value" id="stat-income">--</span>
                </div>
            </div>
            <div class="stat-card expense">
                <div class="stat-icon"><i class="fa fa-arrow-up"></i></div>
                <div class="stat-content">
                    <span class="stat-label">Dépenses</span>
                    <span class="stat-value" id="stat-expense">--</span>
                </div>
            </div>
            <div class="stat-card balance">
                <div class="stat-icon"><i class="fa fa-balance-scale"></i></div>
                <div class="stat-content">
                    <span class="stat-label">Balance</span>
                    <span class="stat-value" id="stat-balance">--</span>
                </div>
            </div>
        </div>

        <!-- Liste des transactions -->
        <section class="transactions-section">
            <div class="transactions-header">
                <span class="transactions-count" id="transactions-count">-- transactions</span>
            </div>
            <div class="transactions-table-container">
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Catégorie</th>
                            <th>Compte</th>
                            <th>Montant</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-body">
                    </tbody>
                </table>
            </div>
            <div class="pagination" id="pagination"></div>
        </section>
    </main>
</div>

<!-- Modal Transaction -->
<div class="modal" id="transaction-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="transaction-modal-title">Nouvelle transaction</h3>
            <button class="modal-close" onclick="closeModal('transaction-modal')">&times;</button>
        </div>
        <form id="transaction-form">
            <input type="hidden" name="id" id="transaction-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="form-type" required>
                        <option value="expense">Dépense</option>
                        <option value="income">Revenu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Montant</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" required placeholder="Ex: Courses Carrefour">
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
            <div class="form-group">
                <label>Notes (optionnel)</label>
                <textarea name="notes" rows="2" placeholder="Notes additionnelles..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('transaction-modal')">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>