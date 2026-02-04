<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header avec actions -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Card->getHtmlSvg() ?>
                    Mes comptes bancaires
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <button onclick="syncAccounts()" class="btn btn-outline btn-sm">
                        <?= EFinanceIcon::Sync->getHtmlSvg('icon-sm') ?>
                        Synchroniser
                    </button>
                    <a href="/accounts/create" class="btn btn-primary btn-sm">
                        <?= EFinanceIcon::Plus->getHtmlSvg('icon-sm') ?>
                        Ajouter un compte
                    </a>
                </div>
            </div>
        </section>

        <!-- Tableau des comptes -->
        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">Liste des comptes</div>
                <span class="badge badge-neutral" id="accountCount">0 compte(s)</span>
            </div>
            <div class="card-body" style="overflow:auto;">
                <table class="table" id="accountsTable">
                    <thead>
                        <tr>
                            <th>Couleur</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Solde</th>
                            <th>Source</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rempli via JS -->
                    </tbody>
                </table>
                <p id="noAccounts" style="display:none;text-align:center;padding:2rem;color:var(--text-light);">
                    Aucun compte enregistré. <a href="/accounts/create">Ajouter un compte</a>
                </p>
            </div>
        </section>
    </main>
</div>

<script>
const typeLabels = {
    'checking': 'Courant',
    'savings': 'Épargne',
    'credit_card': 'Carte de crédit',
    'cash': 'Espèces',
    'other': 'Autre'
};

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

async function loadAccounts() {
    const tbody = document.querySelector('#accountsTable tbody');
    const noAccounts = document.getElementById('noAccounts');
    const countBadge = document.getElementById('accountCount');
    
    try {
        const response = await apiRequest('GET', '/api/accounts');
        const accounts = response.data || [];
        
        countBadge.textContent = `${accounts.length} compte(s)`;
        
        if (accounts.length === 0) {
            tbody.innerHTML = '';
            noAccounts.style.display = 'block';
            document.getElementById('accountsTable').style.display = 'none';
            return;
        }
        
        noAccounts.style.display = 'none';
        document.getElementById('accountsTable').style.display = 'table';
        
        tbody.innerHTML = accounts.map(acc => `
            <tr>
                <td>
                    <div style="width:24px;height:24px;border-radius:50%;background:${acc.color};"></div>
                </td>
                <td>
                    <a href="/accounts/${acc.id}" style="font-weight:600;color:var(--primary-color);text-decoration:none;">
                        ${acc.name}
                    </a>
                </td>
                <td>
                    <span class="chip">${typeLabels[acc.type] || acc.type}</span>
                </td>
                <td style="font-weight:700;${parseFloat(acc.balance) < 0 ? 'color:#dc2626;' : ''}">
                    ${formatCurrency(acc.balance)}
                </td>
                <td>
                    ${acc.id_bridge ? '<span class="badge badge-success">Bridge</span>' : '<span class="badge badge-neutral">Manuel</span>'}
                </td>
                <td>
                    <div style="display:flex;gap:0.5rem;">
                        <a href="/accounts/${acc.id}" class="btn btn-ghost btn-sm" title="Voir">
                            <?= EFinanceIcon::View->getHtmlSvg('icon-sm') ?>
                        </a>
                        <a href="/accounts/${acc.id}/edit" class="btn btn-ghost btn-sm" title="Modifier">
                            <?= EFinanceIcon::Edit->getHtmlSvg('icon-sm') ?>
                        </a>
                        <button onclick="deleteAccount(${acc.id}, '${acc.name}')" class="btn btn-ghost btn-sm text-danger" title="Supprimer">
                            <?= EFinanceIcon::Delete->getHtmlSvg('icon-sm') ?>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Erreur lors du chargement des comptes:', error);
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#dc2626;">Erreur lors du chargement</td></tr>';
    }
}

async function deleteAccount(id, name) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le compte "${name}" ?`)) {
        return;
    }
    
    try {
        await apiRequest('DELETE', `/api/accounts/${id}`);
        loadAccounts();
    } catch (error) {
        alert('Erreur lors de la suppression');
    }
}

async function syncAccounts() {
    try {
        // Initialiser Bridge si nécessaire
        await apiRequest('POST', '/api/bridge/init');
        const result = await apiRequest('POST', '/api/bridge/sync');
        alert(result.message || 'Synchronisation terminée');
        loadAccounts();
    } catch (error) {
        console.error('Erreur sync:', error);
        alert('Erreur lors de la synchronisation. Vérifiez que vos comptes sont bien connectés à Bridge.');
    }
}

document.addEventListener('DOMContentLoaded', loadAccounts);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>