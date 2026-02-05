<?php ob_start();

function F_sFormatCurrency(float $l_fAmount): string {
    return number_format($l_fAmount, 2, ',', ' ') . ' €';
}
?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        
        <section class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Wallet->getHtmlSvg() ?> Solde total</div>
                        <span class="badge badge-success">Actif</span>
                    </div>
                    <div class="card-value" id="totalBalance">--</div>
                    <div class="card-footer text-muted" id="lastUpdate">Chargement...</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Calendar->getHtmlSvg() ?> Prévision fin de mois</div>
                    </div>
                    <div class="card-value" id="projectedBalance">--</div>
                    <div class="card-footer" id="projectedVariation">--</div>
                </div>
            </div>
        </section>

        <section class="row center">
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Revenus</div></div>
                    <div class="card-value text-success" id="totalIncome">--</div>
                </div>
            </div>
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Dépenses</div></div>
                    <div class="card-value text-danger" id="totalExpense">--</div>
                </div>
            </div>
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Abonnements</div></div>
                    <div class="card-value" id="totalSubscriptions">--</div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div class="card-title"><?= EFinanceIcon::Card->getHtmlSvg() ?> Mes comptes</div>
            </div>
            <div class="card-body" style="overflow-x: auto; overflow-y: hidden;">
                <div id="account_list" class="accounts-scroll">
                    <!-- Chargé via JS -->
                </div>
            </div>
        </section>

        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Filter->getHtmlSvg() ?>
                    Dépenses par catégorie
                </div>
                <span class="badge badge-neutral" id="currentMonth">--</span>
            </div>

            <div class="card-body" id="expensesCategoryContainer">
                <p style="text-align:center;color:var(--text-light);">Chargement...</p>
            </div>

            <div class="card-footer">
                <div style="display:flex;justify-content:space-between;">
                    <span class="text-muted">Total dépenses</span>
                    <span style="font-weight:700;" id="totalExpenseFooter">--</span>
                </div>
            </div>
        </section>

    </main>
</div>

<script>
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

function formatMonth(monthStr) {
    const [year, month] = monthStr.split('-');
    const date = new Date(year, parseInt(month) - 1);
    return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
}

async function loadDashboard() {
    try {
        const response = await apiRequest('GET', '/api/stats/dashboard');
        
        if (response.code !== 200) {
            console.error('Erreur API:', response);
            return;
        }
        
        const data = response.data;
        
        // Solde total
        document.getElementById('totalBalance').textContent = formatCurrency(data.total_balance);
        document.getElementById('lastUpdate').textContent = `Mise à jour : ${new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`;
        
        // Prévision
        document.getElementById('projectedBalance').textContent = formatCurrency(data.forecast.projected_balance);
        const variation = data.forecast.projected_variation;
        const variationEl = document.getElementById('projectedVariation');
        variationEl.textContent = (variation >= 0 ? '+' : '') + formatCurrency(variation);
        variationEl.className = 'card-footer ' + (variation >= 0 ? 'text-success' : 'text-danger');
        
        // Revenus/Dépenses/Abonnements du mois
        document.getElementById('totalIncome').textContent = formatCurrency(data.monthly.income);
        document.getElementById('totalExpense').textContent = '- ' + formatCurrency(data.monthly.expense);
        document.getElementById('totalSubscriptions').textContent = formatCurrency(data.subscriptions.expense);
        
        // Mois en cours
        document.getElementById('currentMonth').textContent = formatMonth(data.period.month);
        
        // Dépenses par catégorie
        const container = document.getElementById('expensesCategoryContainer');
        const categories = data.expenses_by_category || [];
        const totalExpense = data.monthly.expense;
        
        if (categories.length === 0) {
            container.innerHTML = '<p style="text-align:center;color:var(--text-light);">Aucune dépense ce mois-ci</p>';
        } else {
            container.innerHTML = '<div style="display:flex;flex-direction:column;gap:1rem;">' +
                categories.map(cat => `
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        <div style="display:flex;justify-content:space-between;">
                            <span>${cat.name}</span>
                            <span><b>${formatCurrency(cat.total)}</b> <small class="text-muted">(${cat.percentage}%)</small></span>
                        </div>
                        <div style="height:6px;background:var(--bg-secondary);border-radius:9px;">
                            <div style="height:100%;background:var(--primary-color);width:${cat.percentage}%;border-radius:9px;transition:width 0.3s ease;"></div>
                        </div>
                    </div>
                `).join('') +
            '</div>';
        }
        
        document.getElementById('totalExpenseFooter').textContent = formatCurrency(totalExpense);
        
        // Comptes
        loadAccounts(data.accounts);
        
    } catch (error) {
        console.error('Erreur lors du chargement du dashboard:', error);
    }
}

function loadAccounts(accounts) {
    const accountList = document.getElementById('account_list');
    
    if (!accounts || accounts.length === 0) {
        accountList.innerHTML = '<p style="padding:2rem;color:var(--text-light);">Aucun compte enregistré. <a href="/accounts/create">Ajouter un compte</a></p>';
        return;
    }
    
    accountList.innerHTML = accounts.map(acc => `
        <div class="account-card-item">
            <a href="/accounts/${acc.id}" class="card nohover" style="background:${acc.color}15;text-decoration:none;color:inherit;">
                <b class="text-truncate">${acc.name}</b><br>
                ${formatCurrency(acc.current_balance)}
                <div style="margin-top:0.5rem;font-size:0.75rem;" class="${acc.variation >= 0 ? 'text-success' : 'text-danger'}">
                    ${acc.variation >= 0 ? '↑' : '↓'} ${formatCurrency(Math.abs(acc.variation))} prévu
                </div>
            </a>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>