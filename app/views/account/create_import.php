<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Import->getHtmlSvg() ?>
                    Importer depuis ma banque
                </div>
                <a href="/accounts/create" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted">Connectez-vous à votre banque pour importer vos comptes automatiquement.</p>
            </div>
        </section>

        <!-- Étapes -->
        <section class="card" id="stepConnect">
            <div class="card-header">
                <div class="card-title">
                    <span class="badge badge-neutral" style="margin-right:0.5rem;">1</span>
                    Connexion à votre banque
                </div>
            </div>
            <div class="card-body">
                <p>Cliquez sur le bouton ci-dessous pour vous connecter à votre banque via Bridge (service sécurisé).</p>
                <button onclick="initBridge()" class="btn btn-primary" id="btnConnect">
                    <?= EFinanceIcon::Link->getHtmlSvg('icon-sm') ?>
                    Connecter ma banque
                </button>
                <p id="connectStatus" class="text-muted mt-2" style="display:none;"></p>
            </div>
        </section>

        <!-- Liste des comptes à importer -->
        <section class="card flex-1" id="stepImport" style="display:none;">
            <div class="card-header">
                <div class="card-title">
                    <span class="badge badge-success" style="margin-right:0.5rem;">2</span>
                    Sélectionner les comptes à importer
                </div>
                <button onclick="refreshBridgeAccounts()" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Sync->getHtmlSvg('icon-sm') ?>
                    Rafraîchir
                </button>
            </div>
            <div class="card-body" style="overflow:auto;">
                <table class="table" id="bridgeAccountsTable">
                    <thead style="position:sticky;top:0;">
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Nom du compte</th>
                            <th>Type</th>
                            <th>Solde</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rempli via JS -->
                    </tbody>
                </table>
                <p id="noBridgeAccounts" style="display:none;text-align:center;padding:2rem;color:var(--text-light);">
                    Aucun compte trouvé. Avez-vous bien connecté votre banque ?
                </p>
            </div>
            <div class="card-footer">
                <button onclick="importSelected()" class="btn btn-primary" id="btnImport">
                    <?= EFinanceIcon::Import->getHtmlSvg('icon-sm') ?>
                    Importer les comptes sélectionnés
                </button>
            </div>
        </section>
    </main>
</div>

<script>
let bridgeAccounts = [];
let existingBridgeIds = [];

const typeLabels = {
    'checking': 'Courant',
    'savings': 'Épargne',
    'card': 'Carte',
    'loan': 'Prêt',
    'market': 'Marché',
    'life_insurance': 'Assurance vie'
};

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

async function initBridge() {
    const btn = document.getElementById('btnConnect');
    const status = document.getElementById('connectStatus');
    
    btn.disabled = true;
    btn.textContent = 'Initialisation...';
    status.style.display = 'flex';
    status.textContent = 'Connexion à Bridge en cours...';
    
    try {
        // Initialiser l'utilisateur Bridge
        const initResult = await apiRequest('POST', '/api/bridge/init');
        status.textContent = 'Création de la session de connexion...';
        
        // Créer une session de connexion
        const connectResult = await apiRequest('POST', '/api/bridge/connect', {
            callback_url: window.location.origin + '/accounts/create/import/callback'
        });
        
        if (connectResult.code === 200 && connectResult.data?.connect_url) {
            status.textContent = 'Redirection vers votre banque...';
            // Ouvrir dans une nouvelle fenêtre ou rediriger
            window.open(connectResult.data.connect_url, '_blank', 'width=600,height=700');
            
            status.innerHTML = 'Une fenêtre s\'est ouverte pour vous connecter à votre banque.<br>' +
                '<small>Une fois terminé, cliquez sur "Rafraîchir" ci-dessous.</small>';
            
            // Afficher l'étape d'import
            document.getElementById('stepImport').style.display = 'flex';
            btn.textContent = 'Reconnecter';
            btn.disabled = false;
        } else {
            throw new Error(connectResult.error || 'Erreur de connexion');
        }
    } catch (error) {
        console.error('Erreur Bridge:', error);
        status.textContent = 'Erreur: ' + (error.message || 'Impossible de se connecter à Bridge');
        status.classList.add('text-danger');
        btn.textContent = 'Réessayer';
        btn.disabled = false;
    }
}

async function refreshBridgeAccounts() {
    const tbody = document.querySelector('#bridgeAccountsTable tbody');
    const noAccounts = document.getElementById('noBridgeAccounts');
    
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Chargement...</td></tr>';
    
    try {
        // Récupérer les comptes existants pour savoir lesquels sont déjà importés
        const existingResult = await apiRequest('GET', '/api/accounts');
        existingBridgeIds = (existingResult.data || [])
            .filter(acc => acc.id_bridge)
            .map(acc => acc.id_bridge);
        
        // Récupérer les comptes Bridge
        const result = await apiRequest('GET', '/api/bridge/accounts');
        bridgeAccounts = result.data || [];
        
        if (bridgeAccounts.length === 0) {
            tbody.innerHTML = '';
            noAccounts.style.display = 'block';
            document.getElementById('bridgeAccountsTable').style.display = 'none';
            return;
        }
        
        noAccounts.style.display = 'none';
        document.getElementById('bridgeAccountsTable').style.display = 'table';
        
        tbody.innerHTML = bridgeAccounts.map(acc => {
            const isImported = existingBridgeIds.includes(String(acc.id));
            return `
                <tr>
                    <td>
                        <input type="checkbox" name="accountId" value="${acc.id}" 
                            ${isImported ? 'disabled' : ''}>
                    </td>
                    <td style="font-weight:600;">${acc.name}</td>
                    <td>
                        <span class="chip">${typeLabels[acc.type] || acc.type}</span>
                    </td>
                    <td style="font-weight:700;${acc.balance < 0 ? 'color:#dc2626;' : ''}">
                        ${formatCurrency(acc.balance)}
                    </td>
                    <td>
                        ${isImported 
                            ? '<span class="badge badge-success">Déjà importé</span>' 
                            : '<span class="badge badge-warning">Non importé</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        console.error('Erreur:', error);
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;">Erreur lors du chargement. Avez-vous connecté votre banque ?</td></tr>';
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('input[name="accountId"]:not(:disabled)').forEach(cb => {
        cb.checked = selectAll.checked;
    });
}

async function importSelected() {
    const selected = Array.from(document.querySelectorAll('input[name="accountId"]:checked'))
        .map(cb => parseInt(cb.value));
    
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins un compte à importer.');
        return;
    }
    
    const btn = document.getElementById('btnImport');
    btn.disabled = true;
    btn.textContent = 'Import en cours...';
    
    try {
        const result = await apiRequest('POST', '/api/bridge/accounts/import', {
            account_ids: selected
        });
        
        alert(result.message || 'Import terminé');
        window.location.href = '/accounts';
    } catch (error) {
        console.error('Erreur import:', error);
        alert('Erreur lors de l\'import');
        btn.disabled = false;
        btn.textContent = 'Importer les comptes sélectionnés';
    }
}

// Vérifier si on a déjà des comptes Bridge
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await apiRequest('POST', '/api/bridge/init');
        // Si l'init fonctionne, on peut afficher les comptes
        document.getElementById('stepImport').style.display = 'flex';
        refreshBridgeAccounts();
    } catch (e) {
        // Pas de session Bridge, l'utilisateur doit se connecter
    }
});
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
