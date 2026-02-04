<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header avec filtres -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Transaction->getHtmlSvg() ?>
                    Transactions
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <button onclick="syncTransactions()" class="btn btn-outline btn-sm" id="btnSync">
                        <?= EFinanceIcon::Sync->getHtmlSvg('icon-sm') ?>
                        Synchroniser
                    </button>
                    <a href="/transactions/add" class="btn btn-primary btn-sm">
                        <?= EFinanceIcon::Plus->getHtmlSvg('icon-sm') ?>
                        Nouvelle transaction
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="filterForm" class="form-inline" style="flex-wrap:wrap;gap:1rem;">
                    <div class="input-group">
                        <label class="input-label">Compte</label>
                        <div class="input-control">
                            <select name="account_id" id="filterAccount">
                                <option value="">Tous les comptes</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Catégorie</label>
                        <div class="input-control">
                            <select name="category_id" id="filterCategory">
                                <option value="">Toutes</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Type</label>
                        <div class="input-control">
                            <select name="type" id="filterType">
                                <option value="">Tous</option>
                                <option value="income">Revenus</option>
                                <option value="expense">Dépenses</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Du</label>
                        <div class="input-control">
                            <input type="date" name="start_date" id="filterStartDate">
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Au</label>
                        <div class="input-control">
                            <input type="date" name="end_date" id="filterEndDate">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm" style="align-self:flex-end;">
                        <?= EFinanceIcon::Filter->getHtmlSvg('icon-sm') ?>
                        Filtrer
                    </button>
                    <button type="button" onclick="resetFilters()" class="btn btn-ghost btn-sm" style="align-self:flex-end;">
                        Réinitialiser
                    </button>
                </form>
            </div>
        </section>

        <!-- Stats rapides -->
        <section class="row">
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Income->getHtmlSvg() ?> Revenus</div>
                    </div>
                    <div class="card-value text-success" id="statIncome">0,00 €</div>
                    <div class="card-footer text-muted">Ce mois</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Expense->getHtmlSvg() ?> Dépenses</div>
                    </div>
                    <div class="card-value text-danger" id="statExpense">0,00 €</div>
                    <div class="card-footer text-muted">Ce mois</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::TrendUp->getHtmlSvg() ?> Balance</div>
                    </div>
                    <div class="card-value" id="statBalance">0,00 €</div>
                    <div class="card-footer text-muted">Ce mois</div>
                </div>
            </div>
        </section>

        <!-- Tableau des transactions -->
        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">Liste des transactions</div>
                <span class="badge badge-neutral" id="transactionCount">0 transaction(s)</span>
            </div>
            <div class="card-body" style="overflow:auto;">
                <table class="table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Compte</th>
                            <th>Catégorie</th>
                            <th>Montant</th>
                            <th>Source</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rempli via JS -->
                    </tbody>
                </table>
                <p id="noTransactions" style="display:none;text-align:center;padding:2rem;color:var(--text-light);">
                    Aucune transaction trouvée. <a href="/transactions/add">Ajouter une transaction</a>
                </p>
            </div>
            <div class="card-footer" id="pagination" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="text-muted" id="paginationInfo">Affichage 1-50 sur 100</span>
                    <div style="display:flex;gap:0.5rem;">
                        <button onclick="prevPage()" class="btn btn-ghost btn-sm" id="btnPrev" disabled>Précédent</button>
                        <button onclick="nextPage()" class="btn btn-ghost btn-sm" id="btnNext">Suivant</button>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
const ICON_VIEW = `<?= str_replace("\n", "", EFinanceIcon::View->getHtmlSvg('icon-sm')) ?>`;
const ICON_EDIT = `<?= str_replace("\n", "", EFinanceIcon::Edit->getHtmlSvg('icon-sm')) ?>`;
const ICON_DELETE = `<?= str_replace("\n", "", EFinanceIcon::Delete->getHtmlSvg('icon-sm')) ?>`;
const ICON_SYNC = `<?= str_replace("\n", "", EFinanceIcon::Sync->getHtmlSvg('icon-sm')) ?>`;

let currentOffset = 0;
const limit = 50;
let totalTransactions = 0;

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

async function loadFilters() {
    try {
        // Charger les comptes
        const accountsRes = await apiRequest('GET', '/api/accounts');
        const accountSelect = document.getElementById('filterAccount');
        (accountsRes.data || []).forEach(acc => {
            const opt = document.createElement('option');
            opt.value = acc.id;
            opt.textContent = acc.name;
            accountSelect.appendChild(opt);
        });

        // Charger les catégories
        const categoriesRes = await apiRequest('GET', '/api/categories');
        const categorySelect = document.getElementById('filterCategory');
        (categoriesRes.data || []).forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = `${cat.name} (${cat.type === 'income' ? 'Revenu' : 'Dépense'})`;
            categorySelect.appendChild(opt);
        });
    } catch (error) {
        console.error('Erreur chargement filtres:', error);
    }
}

async function loadStats() {
    try {
        const res = await apiRequest('GET', '/api/transactions/stats');
        if (res.code === 200) {
            document.getElementById('statIncome').textContent = formatCurrency(res.data.total_income);
            document.getElementById('statExpense').textContent = formatCurrency(res.data.total_expense);
            
            const balance = res.data.balance;
            const balanceEl = document.getElementById('statBalance');
            balanceEl.textContent = formatCurrency(balance);
            balanceEl.className = 'card-value ' + (balance >= 0 ? 'text-success' : 'text-danger');
        }
    } catch (error) {
        console.error('Erreur chargement stats:', error);
    }
}

async function loadTransactions() {
    const tbody = document.querySelector('#transactionsTable tbody');
    const noTransactions = document.getElementById('noTransactions');
    const countBadge = document.getElementById('transactionCount');
    const pagination = document.getElementById('pagination');
    
    // Construire l'URL avec les filtres
    const params = new URLSearchParams();
    params.append('limit', limit);
    params.append('offset', currentOffset);
    
    const accountId = document.getElementById('filterAccount').value;
    const categoryId = document.getElementById('filterCategory').value;
    const type = document.getElementById('filterType').value;
    const startDate = document.getElementById('filterStartDate').value;
    const endDate = document.getElementById('filterEndDate').value;
    
    if (accountId) params.append('account_id', accountId);
    if (categoryId) params.append('category_id', categoryId);
    if (type) params.append('type', type);
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    try {
        const response = await apiRequest('GET', `/api/transactions?${params.toString()}`);
        const transactions = response.data || [];
        totalTransactions = response.total || 0;
        
        countBadge.textContent = `${totalTransactions} transaction(s)`;
        
        if (transactions.length === 0) {
            tbody.innerHTML = '';
            noTransactions.style.display = 'block';
            document.getElementById('transactionsTable').style.display = 'none';
            pagination.style.display = 'none';
            return;
        }
        
        noTransactions.style.display = 'none';
        document.getElementById('transactionsTable').style.display = 'table';
        
        tbody.innerHTML = transactions.map(tx => `
            <tr>
                <td style="white-space:nowrap;">${formatDate(tx.date)}</td>
                <td style="max-width:200px;" class="text-truncate" title="${tx.description || ''}">
                    ${tx.description || '<em class="text-muted">Sans description</em>'}
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <div style="width:8px;height:8px;border-radius:50%;background:${tx.account_color || '#ccc'};"></div>
                        <span>${tx.account_name || 'N/A'}</span>
                    </div>
                </td>
                <td>
                    <span class="chip">${tx.category_name || 'N/A'}</span>
                </td>
                <td style="font-weight:700;${parseFloat(tx.amount) < 0 ? 'color:#dc2626;' : 'color:#16a34a;'}">
                    ${parseFloat(tx.amount) >= 0 ? '+' : ''}${formatCurrency(tx.amount)}
                </td>
                <td>
                    ${tx.id_bridge ? '<span class="badge badge-success badge-pill">Bridge</span>' : '<span class="badge badge-neutral badge-pill">Manuel</span>'}
                </td>
                <td>
                    <div style="display:flex;gap:0.25rem;">
                        <a href="/transactions/${tx.id}" class="btn btn-ghost btn-sm" title="Voir">
                            ${ICON_VIEW}
                        </a>
                        ${!tx.id_bridge ? `
                            <a href="/transactions/${tx.id}/edit" class="btn btn-ghost btn-sm" title="Modifier">
                                ${ICON_EDIT}
                            </a>
                            <button onclick="deleteTransaction(${tx.id})" class="btn btn-ghost btn-sm text-danger" title="Supprimer">
                                ${ICON_DELETE}
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
        
        // Pagination
        if (totalTransactions > limit) {
            pagination.style.display = 'block';
            const start = currentOffset + 1;
            const end = Math.min(currentOffset + limit, totalTransactions);
            document.getElementById('paginationInfo').textContent = `Affichage ${start}-${end} sur ${totalTransactions}`;
            document.getElementById('btnPrev').disabled = currentOffset === 0;
            document.getElementById('btnNext').disabled = currentOffset + limit >= totalTransactions;
        } else {
            pagination.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Erreur chargement transactions:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#dc2626;">Erreur lors du chargement</td></tr>';
    }
}

async function deleteTransaction(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?')) {
        return;
    }
    
    try {
        await apiRequest('DELETE', `/api/transactions/${id}`);
        loadTransactions();
        loadStats();
    } catch (error) {
        alert('Erreur lors de la suppression');
    }
}

async function syncTransactions() {
    const btn = document.getElementById('btnSync');
    btn.disabled = true;
    btn.innerHTML = ICON_SYNC + ' Synchronisation...';
    
    try {
        // Initialiser Bridge si nécessaire
        await apiRequest('POST', '/api/bridge/init');
        const result = await apiRequest('POST', '/api/bridge/transactions/sync');
        alert(result.message || 'Synchronisation terminée');
        loadTransactions();
        loadStats();
    } catch (error) {
        console.error('Erreur sync:', error);
        alert('Erreur lors de la synchronisation');
    } finally {
        btn.disabled = false;
        btn.innerHTML = ICON_SYNC + ' Synchroniser';
    }
}

function prevPage() {
    if (currentOffset > 0) {
        currentOffset -= limit;
        loadTransactions();
    }
}

function nextPage() {
    if (currentOffset + limit < totalTransactions) {
        currentOffset += limit;
        loadTransactions();
    }
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    currentOffset = 0;
    loadTransactions();
}

document.getElementById('filterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    currentOffset = 0;
    loadTransactions();
});

document.addEventListener('DOMContentLoaded', () => {
    // Définir les dates par défaut (mois en cours)
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('filterStartDate').value = firstDay.toISOString().split('T')[0];
    document.getElementById('filterEndDate').value = now.toISOString().split('T')[0];
    
    loadFilters();
    loadStats();
    loadTransactions();
});
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
