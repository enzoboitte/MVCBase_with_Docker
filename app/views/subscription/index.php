<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header avec actions -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Recurring->getHtmlSvg() ?>
                    Mes abonnements
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <a href="/subscriptions/create" class="btn btn-primary btn-sm">
                        <?= EFinanceIcon::Plus->getHtmlSvg('icon-sm') ?>
                        Ajouter un abonnement
                    </a>
                </div>
            </div>
        </section>

        <section class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::TrendDown->getHtmlSvg() ?> Déjà prélevé ce mois</div>
                        <span class="badge badge-success" id="paidCount">--</span>
                    </div>
                    <div class="card-value text-danger" id="paidExpense">--</div>
                    <div class="card-footer text-success" id="paidIncome">+ -- en revenus</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Calendar->getHtmlSvg() ?> Reste à prélever</div>
                        <span class="badge badge-warning" id="remainingCount">--</span>
                    </div>
                    <div class="card-value text-warning" id="remainingExpense">--</div>
                    <div class="card-footer text-success" id="remainingIncome">+ -- en revenus</div>
                </div>
            </div>
        </section>

        <!--<section class="row center">
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Total mensuel</div></div>
                    <div class="card-value" id="monthlyNet">--</div>
                </div>
            </div>
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Revenus récurrents</div></div>
                    <div class="card-value text-success" id="monthlyIncome">--</div>
                </div>
            </div>
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Dépenses récurrentes</div></div>
                    <div class="card-value text-danger" id="monthlyExpense">--</div>
                </div>
            </div>
        </section>-->

        <section class="card">
            <div class="card-header">
                <div class="card-title"><?= EFinanceIcon::TrendDown->getHtmlSvg() ?> Déjà prélevés</div>
                <span class="badge badge-neutral" id="currentMonth">--</span>
            </div>
            <div class="card-body" id="paidList">
                <p style="text-align:center;color:var(--text-light);">Chargement...</p>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div class="card-title"><?= EFinanceIcon::Calendar->getHtmlSvg() ?> À venir ce mois</div>
            </div>
            <div class="card-body" id="remainingList">
                <p style="text-align:center;color:var(--text-light);">Chargement...</p>
            </div>
        </section>

        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title"><?= EFinanceIcon::Recurring->getHtmlSvg() ?> Tous mes abonnements</div>
            </div>
            <div class="card-body" id="allSubscriptionsList">
                <p style="text-align:center;color:var(--text-light);">Chargement...</p>
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

function getPeriodLabel(period) {
    return {
        'weekly': 'Hebdomadaire',
        'monthly': 'Mensuel',
        'yearly': 'Annuel'
    }[period] || period;
}

function getDayLabel(sub) {
    if (sub.type_period === 'weekly') {
        const days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        return days[sub.day_of_week] || '';
    }
    if (sub.type_period === 'monthly') {
        return `Le ${sub.day_of_month} du mois`;
    }
    if (sub.type_period === 'yearly' && sub.date_of_year) {
        const date = new Date(sub.date_of_year);
        return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' });
    }
    return '';
}

function renderSubscriptionItem(sub, showActions = false) {
    const isExpense = sub.type === 'expense';
    const amountClass = isExpense ? 'text-danger' : 'text-success';
    const amountPrefix = isExpense ? '-' : '+';
    
    return `
        <div class="subscription-item" style="display:flex;justify-content:space-between;align-items:center;padding:1rem;background:var(--bg-secondary);border-radius:8px;">
            <div style="flex:1;">
                <div style="font-weight:600;">${sub.name}</div>
                <div class="text-muted" style="font-size:0.85rem;">
                    ${getPeriodLabel(sub.type_period)} • ${getDayLabel(sub)}
                </div>
            </div>
            <div style="text-align:right;display:flex;align-items:center;gap:1rem;">
                <span class="${amountClass}" style="font-weight:700;font-size:1.1rem;">
                    ${amountPrefix} ${formatCurrency(sub.amount)}
                </span>
                ${showActions ? `
                    <div style="display:flex;gap:0.5rem;">
                        <a href="/subscriptions/${sub.id}/edit" class="btn btn-ghost btn-sm" style="padding:0.4rem 0.6rem;"><?= EFinanceIcon::Edit->getHtmlSvg('icon-sm') ?></a>
                        <button onclick="deleteSubscription(${sub.id})" class="btn btn-ghost btn-sm text-danger" style="padding:0.4rem 0.6rem;"><?= EFinanceIcon::Delete->getHtmlSvg('icon-sm') ?></button>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

async function loadSubscriptions() {
    try {
        const response = await apiRequest('GET', '/api/subscriptions/stats');
        
        if (response.code !== 200) {
            console.error('Erreur API:', response);
            return;
        }
        
        const data = response.data;
        
        // Mois en cours
        document.getElementById('currentMonth').textContent = formatMonth(data.period.month);
        
        // Stats déjà prélevé
        document.getElementById('paidExpense').textContent = '- ' + formatCurrency(data.paid_this_month.expense);
        document.getElementById('paidIncome').textContent = '+ ' + formatCurrency(data.paid_this_month.income) + ' en revenus';
        document.getElementById('paidCount').textContent = data.paid_subscriptions.length + ' prélevé(s)';
        
        // Stats à venir
        document.getElementById('remainingExpense').textContent = '- ' + formatCurrency(data.remaining_this_month.expense);
        document.getElementById('remainingIncome').textContent = '+ ' + formatCurrency(data.remaining_this_month.income) + ' en revenus';
        document.getElementById('remainingCount').textContent = data.remaining_subscriptions.length + ' à venir';
        
        // Totaux mensuels
        /*document.getElementById('monthlyNet').textContent = formatCurrency(data.monthly_totals.net);
        document.getElementById('monthlyIncome').textContent = formatCurrency(data.monthly_totals.income);
        document.getElementById('monthlyExpense').textContent = '- ' + formatCurrency(data.monthly_totals.expense);*/
        
        // Liste des abonnements prélevés
        const paidList = document.getElementById('paidList');
        if (data.paid_subscriptions.length === 0) {
            paidList.innerHTML = '<p style="text-align:center;color:var(--text-light);padding:1rem;">Aucun prélèvement effectué ce mois</p>';
        } else {
            paidList.innerHTML = '<div style="display:flex;flex-direction:column;gap:0.5rem;">' +
                data.paid_subscriptions.map(sub => renderSubscriptionItem(sub)).join('') +
            '</div>';
        }
        
        // Liste des abonnements à venir
        const remainingList = document.getElementById('remainingList');
        if (data.remaining_subscriptions.length === 0) {
            remainingList.innerHTML = '<p style="text-align:center;color:var(--text-light);padding:1rem;">Aucun prélèvement à venir ce mois</p>';
        } else {
            remainingList.innerHTML = '<div style="display:flex;flex-direction:column;gap:0.5rem;">' +
                data.remaining_subscriptions.map(sub => renderSubscriptionItem(sub)).join('') +
            '</div>';
        }
        
        // Tous les abonnements
        const allList = document.getElementById('allSubscriptionsList');
        const allSubs = data.all_subscriptions || [];
        if (allSubs.length === 0) {
            allList.innerHTML = '<p style="text-align:center;color:var(--text-light);padding:2rem;">Aucun abonnement. <a href="/subscriptions/create">Ajouter un abonnement</a></p>';
        } else {
            allList.innerHTML = '<div style="display:flex;flex-direction:column;gap:0.5rem;">' +
                allSubs.map(sub => renderSubscriptionItem(sub, true)).join('') +
            '</div>';
        }
        
    } catch (error) {
        console.error('Erreur lors du chargement des abonnements:', error);
    }
}

async function deleteSubscription(id) {
    if (!confirm('Supprimer cet abonnement ?')) return;
    
    try {
        const response = await apiRequest('DELETE', `/api/subscriptions/${id}`);
        if (response.code === 200) {
            loadSubscriptions();
        } else {
            alert(response.error || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

document.addEventListener('DOMContentLoaded', loadSubscriptions);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'subscription-page';
require ROOT . '/app/views/layout.php'; 
?>
