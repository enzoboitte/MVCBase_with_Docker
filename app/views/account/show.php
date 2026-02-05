<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Card->getHtmlSvg() ?>
                    <span id="accountName">Chargement...</span>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <a href="/accounts/<?= $accountId ?>/edit" class="btn btn-outline btn-sm">
                        <?= EFinanceIcon::Edit->getHtmlSvg('icon-sm') ?>
                        Modifier
                    </a>
                    <a href="/accounts" class="btn btn-ghost btn-sm">
                        <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                        Retour
                    </a>
                </div>
            </div>
        </section>

        <!-- Infos du compte -->
        <section class="row center">
            <div class="col-4">
                <div class="card nohover">
                    <div class="card-header">
                        <div class="card-title">Solde actuel</div>
                    </div>
                    <div class="card-value" id="accountBalance">-</div>
                    <div class="card-footer text-muted">Mis à jour automatiquement</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card nohover">
                    <div class="card-header">
                        <div class="card-title">Solde comptable</div>
                    </div>
                    <div class="card-value" id="accountingBalance">-</div>
                    <div class="card-footer text-muted">Opérations validées</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card nohover">
                    <div class="card-header">
                        <div class="card-title">Solde instantané</div>
                    </div>
                    <div class="card-value" id="instantBalance">-</div>
                    <div class="card-footer text-muted">Incluant les opérations en cours</div>
                </div>
            </div>
        </section>

        <!-- Détails -->
        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">Détails du compte</div>
            </div>
            <div class="card-body">
                <div class="list">
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Type</div>
                        </div>
                        <div id="accountType">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Couleur</div>
                        </div>
                        <div id="accountColor" style="width:24px;height:24px;border-radius:50%;"></div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Source</div>
                        </div>
                        <div id="accountSource">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Date de création</div>
                        </div>
                        <div id="accountCreated">-</div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button onclick="deleteAccount()" class="btn btn-danger btn-sm">
                    <?= EFinanceIcon::Delete->getHtmlSvg('icon-sm') ?>
                    Supprimer ce compte
                </button>
            </div>
        </section>
    </main>
</div>

<script>
const accountId = <?= $accountId ?>;
let accountData = null;

const typeLabels = {
    'checking': 'Compte courant',
    'savings': 'Épargne',
    'credit_card': 'Carte de crédit',
    'cash': 'Espèces',
    'other': 'Autre'
};

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

async function loadAccount() {
    try {
        const response = await apiRequest('GET', `/api/accounts/${accountId}`);
        
        if (response.code !== 200) {
            alert('Compte non trouvé');
            window.location.href = '/accounts';
            return;
        }
        
        accountData = response.data;
        
        // Mettre à jour l'interface
        document.getElementById('accountName').textContent = accountData.name;
        document.getElementById('accountBalance').textContent = formatCurrency(accountData.balance);
        document.getElementById('accountBalance').style.color = parseFloat(accountData.balance) < 0 ? '#dc2626' : '';
        document.getElementById('accountingBalance').textContent = formatCurrency(accountData.accounting_balance);
        document.getElementById('instantBalance').textContent = formatCurrency(accountData.instant_balance);
        document.getElementById('accountType').innerHTML = `<span class="chip">${typeLabels[accountData.type] || accountData.type}</span>`;
        document.getElementById('accountColor').style.background = accountData.color;
        document.getElementById('accountSource').innerHTML = accountData.id_bridge 
            ? '<span class="badge badge-success">Bridge (synchronisé)</span>' 
            : '<span class="badge badge-neutral">Manuel</span>';
        document.getElementById('accountCreated').textContent = formatDate(accountData.created_at);
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement du compte');
    }
}

async function deleteAccount() {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le compte "${accountData?.name}" ? Cette action est irréversible.`)) {
        return;
    }
    
    try {
        await apiRequest('DELETE', `/api/accounts/${accountId}`);
        window.location.href = '/accounts';
    } catch (error) {
        alert('Erreur lors de la suppression');
    }
}

document.addEventListener('DOMContentLoaded', loadAccount);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
