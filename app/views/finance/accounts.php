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
            <li class="nav-item active">
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
            <h2>Mes Comptes</h2>
            <button class="btn-primary" onclick="openModal('account-modal')">
                <i class="fa fa-plus"></i> Nouveau compte
            </button>
        </header>

        <!-- Net Worth -->
        <div class="net-worth-card">
            <div class="net-worth-label">Patrimoine Net</div>
            <div class="net-worth-value" id="net-worth">--</div>
        </div>

        <!-- Accounts Grid -->
        <div class="accounts-grid" id="accounts-grid">
            <!-- Loaded by JS -->
        </div>

        <!-- Transfer Section -->
        <section class="transfer-section">
            <h3><i class="fa fa-exchange"></i> Virement interne</h3>
            <form id="transfer-form" class="transfer-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Compte source</label>
                        <select name="from_account_id" id="from-account" required></select>
                    </div>
                    <div class="form-group transfer-icon">
                        <i class="fa fa-arrow-right"></i>
                    </div>
                    <div class="form-group">
                        <label>Compte destination</label>
                        <select name="to_account_id" id="to-account" required></select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Montant</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Description (optionnel)</label>
                        <input type="text" name="description" placeholder="Virement interne">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Transférer</button>
                    </div>
                </div>
            </form>
        </section>
    </main>
</div>

<!-- Modal Compte -->
<div class="modal" id="account-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="account-modal-title">Nouveau compte</h3>
            <button class="modal-close" onclick="closeModal('account-modal')">&times;</button>
        </div>
        <form id="account-form">
            <input type="hidden" name="id" id="account-id">
            <div class="form-group">
                <label>Nom du compte</label>
                <input type="text" name="name" required placeholder="Ex: Boursorama, Livret A...">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="checking">Compte courant</option>
                        <option value="savings">Épargne</option>
                        <option value="cash">Espèces</option>
                        <option value="credit_card">Carte de crédit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Solde initial</label>
                    <input type="number" name="current_balance" step="0.01" value="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Icône</label>
                    <select name="icon">
                        <option value="fa-university">🏦 Banque</option>
                        <option value="fa-piggy-bank">🐷 Épargne</option>
                        <option value="fa-money">💵 Cash</option>
                        <option value="fa-credit-card">💳 Carte</option>
                        <option value="fa-building">🏢 Entreprise</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Couleur</label>
                    <input type="color" name="color" value="#2563eb">
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="include_in_net_worth" checked>
                    Inclure dans le patrimoine net
                </label>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('account-modal')">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>
