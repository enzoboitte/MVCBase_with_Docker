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
            <li class="nav-item">
                <a href="/subscriptions"><i class="fa fa-repeat"></i> Abonnements</a>
            </li>
            <li class="nav-item active">
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
            <h2><i class="fa fa-tags"></i> Catégories</h2>
            <button class="btn btn-primary" onclick="openModal('category-modal')">
                <i class="fa fa-plus"></i> Nouvelle catégorie
            </button>
        </header>

        <!-- Tabs -->
        <div class="tabs-container">
            <button id="tab-expense" class="tab-btn active" onclick="setTab('expense')">
                <i class="fa fa-arrow-down"></i> Dépenses
            </button>
            <button id="tab-income" class="tab-btn" onclick="setTab('income')">
                <i class="fa fa-arrow-up"></i> Revenus
            </button>
        </div>

        <!-- Budget Overview (Expense only) -->
        <section id="budget-overview" class="card">
            <div class="card-header">
                <h3><i class="fa fa-pie-chart"></i> Vue d'ensemble des budgets</h3>
                <select id="budget-month" class="select-input" onchange="loadBudgetStats()">
                    <!-- Months loaded dynamically -->
                </select>
            </div>
            <div class="card-body">
                <div class="budget-summary">
                    <div class="budget-stat">
                        <span class="budget-label">Budget total</span>
                        <span id="budget-total-value" class="budget-value">0,00 €</span>
                    </div>
                    <div class="budget-stat spent">
                        <span class="budget-label">Dépensé</span>
                        <span id="budget-spent-value" class="budget-value">0,00 €</span>
                    </div>
                    <div class="budget-stat remaining">
                        <span class="budget-label">Restant</span>
                        <span id="budget-remaining-value" class="budget-value">0,00 €</span>
                    </div>
                </div>
                <div id="budget-progress-container" class="budget-progress-list">
                    <!-- Budget progress bars loaded here -->
                </div>
            </div>
        </section>

        <!-- Categories Grid -->
        <section class="card">
            <div class="card-header">
                <h3 id="categories-title">Catégories de dépenses</h3>
            </div>
            <div class="card-body">
                <div id="categories-container" class="categories-grid">
                    <!-- Categories loaded here -->
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Category Modal -->
<div id="category-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="category-modal-title">Nouvelle catégorie</h3>
            <button class="modal-close" onclick="closeModal('category-modal')">&times;</button>
        </div>
        <form id="category-form">
            <input type="hidden" id="category-id">
            
            <div class="form-group">
                <label for="form-name">Nom</label>
                <input type="text" id="form-name" name="name" class="form-input" required placeholder="Alimentation, Salaire...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-type">Type</label>
                    <select id="form-type" name="type" class="form-input" required onchange="toggleBudgetField()">
                        <option value="expense">Dépense</option>
                        <option value="income">Revenu</option>
                    </select>
                </div>
                <div class="form-group" id="budget-field">
                    <label for="form-budget">Budget mensuel</label>
                    <input type="number" id="form-budget" name="budget" class="form-input" step="0.01" placeholder="500.00">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="form-icon">Icône</label>
                    <select id="form-icon" name="icon" class="form-input">
                        <option value="">-- Aucune --</option>
                        <option value="fa-shopping-cart">🛒 Courses</option>
                        <option value="fa-cutlery">🍽️ Restaurant</option>
                        <option value="fa-car">🚗 Transport</option>
                        <option value="fa-home">🏠 Logement</option>
                        <option value="fa-bolt">⚡ Énergie</option>
                        <option value="fa-heart">❤️ Santé</option>
                        <option value="fa-gamepad">🎮 Loisirs</option>
                        <option value="fa-plane">✈️ Voyages</option>
                        <option value="fa-gift">🎁 Cadeaux</option>
                        <option value="fa-book">📚 Éducation</option>
                        <option value="fa-briefcase">💼 Travail</option>
                        <option value="fa-money">💰 Salaire</option>
                        <option value="fa-line-chart">📈 Investissement</option>
                        <option value="fa-percent">% Remboursement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="form-color">Couleur</label>
                    <input type="color" id="form-color" name="color" class="form-input color-input" value="#2563eb">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('category-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>
