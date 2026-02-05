<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Transaction->getHtmlSvg() ?>
                    <span id="transactionTitle">Détails de la transaction</span>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <a href="/transactions/<?= $transactionId ?>/edit" class="btn btn-outline btn-sm" id="btnEdit" style="display:none;">
                        <?= EFinanceIcon::Edit->getHtmlSvg('icon-sm') ?>
                        Modifier
                    </a>
                    <a href="/transactions" class="btn btn-ghost btn-sm">
                        <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                        Retour
                    </a>
                </div>
            </div>
        </section>

        <!-- Montant -->
        <section class="card">
            <div class="card-header">
                <div class="card-title">Montant</div>
                <span class="badge" id="typeBadge">-</span>
            </div>
            <div class="card-value" id="transactionAmount" style="font-size:2rem;">-</div>
        </section>

        <!-- Détails -->
        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">Informations</div>
            </div>
            <div class="card-body">
                <div class="list">
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Date</div>
                        </div>
                        <div id="transactionDate">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Compte</div>
                        </div>
                        <div id="transactionAccount">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Catégorie</div>
                        </div>
                        <div id="transactionCategory">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Description</div>
                        </div>
                        <div id="transactionDescription">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Source</div>
                        </div>
                        <div id="transactionSource">-</div>
                    </div>
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-title">Date d'ajout</div>
                        </div>
                        <div id="transactionCreated">-</div>
                    </div>
                </div>
            </div>
            <div class="card-footer" id="deleteSection" style="display:none;">
                <button onclick="deleteTransaction()" class="btn btn-danger btn-sm">
                    <?= EFinanceIcon::Delete->getHtmlSvg('icon-sm') ?>
                    Supprimer cette transaction
                </button>
            </div>
        </section>
    </main>
</div>

<script>
const transactionId = <?= $transactionId ?>;
let transactionData = null;

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

function formatDateTime(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

async function loadTransaction() {
    try {
        const response = await apiRequest('GET', `/api/transactions/${transactionId}`);
        
        if (response.code !== 200) {
            alert('Transaction non trouvée');
            window.location.href = '/transactions';
            return;
        }
        
        transactionData = response.data;
        const tx = transactionData;
        
        // Mise à jour de l'interface
        const amount = parseFloat(tx.amount);
        const isIncome = amount >= 0;
        
        document.getElementById('transactionAmount').textContent = (isIncome ? '+' : '') + formatCurrency(amount);
        document.getElementById('transactionAmount').style.color = isIncome ? '#16a34a' : '#dc2626';
        
        const typeBadge = document.getElementById('typeBadge');
        typeBadge.textContent = isIncome ? 'Revenu' : 'Dépense';
        typeBadge.className = 'badge ' + (isIncome ? 'badge-success' : 'badge-danger');
        
        document.getElementById('transactionDate').textContent = formatDate(tx.date);
        document.getElementById('transactionAccount').innerHTML = `
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <div style="width:10px;height:10px;border-radius:50%;background:${tx.account_color || '#ccc'};"></div>
                <span>${tx.account_name || 'N/A'}</span>
            </div>
        `;
        document.getElementById('transactionCategory').innerHTML = `<span class="chip">${tx.category_name || 'N/A'}</span>`;
        document.getElementById('transactionDescription').textContent = tx.description || 'Aucune description';
        document.getElementById('transactionSource').innerHTML = tx.id_bridge 
            ? '<span class="badge badge-success">Importée via Bridge</span>' 
            : '<span class="badge badge-neutral">Saisie manuelle</span>';
        document.getElementById('transactionCreated').textContent = formatDateTime(tx.created_at);
        
        // Afficher les boutons edit/delete seulement pour les transactions manuelles
        if (!tx.id_bridge) {
            document.getElementById('btnEdit').style.display = 'inline-flex';
            document.getElementById('deleteSection').style.display = 'block';
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de la transaction');
    }
}

async function deleteTransaction() {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?')) {
        return;
    }
    
    try {
        await apiRequest('DELETE', `/api/transactions/${transactionId}`);
        window.location.href = '/transactions';
    } catch (error) {
        alert('Erreur lors de la suppression');
    }
}

document.addEventListener('DOMContentLoaded', loadTransaction);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
