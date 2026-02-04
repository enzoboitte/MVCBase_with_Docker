<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Edit->getHtmlSvg() ?>
                    Modifier la transaction
                </div>
                <a href="/transactions/<?= $transactionId ?>" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
        </section>

        <!-- Formulaire -->
        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">Informations</div>
            </div>
            <div class="card-body">
                <form id="editTransactionForm" style="display:flex;flex-direction:column;gap:1rem;max-width:500px;">
                    <!-- Type -->
                    <div class="input-group">
                        <label class="input-label">Type *</label>
                        <div style="display:flex;gap:1rem;">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="radio" name="type" value="expense">
                                <span class="chip" style="background:rgba(220,38,38,0.1);color:#dc2626;">
                                    <?= EFinanceIcon::Expense->getHtmlSvg('icon-sm') ?> Dépense
                                </span>
                            </label>
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="radio" name="type" value="income">
                                <span class="chip" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                                    <?= EFinanceIcon::Income->getHtmlSvg('icon-sm') ?> Revenu
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Compte -->
                    <div class="input-group">
                        <label class="input-label">Compte *</label>
                        <div class="input-control">
                            <select name="account_id" id="accountSelect" required>
                                <option value="">Sélectionner un compte</option>
                            </select>
                        </div>
                    </div>

                    <!-- Montant -->
                    <div class="input-group">
                        <label class="input-label">Montant (€) *</label>
                        <div class="input-control">
                            <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    <!-- Catégorie -->
                    <div class="input-group">
                        <label class="input-label">Catégorie *</label>
                        <div class="input-control">
                            <select name="category_id" id="categorySelect" required>
                                <option value="">Sélectionner une catégorie</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="input-group">
                        <label class="input-label">Date *</label>
                        <div class="input-control">
                            <input type="date" name="date" required>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="input-group">
                        <label class="input-label">Description</label>
                        <div class="input-control">
                            <input type="text" name="description" placeholder="Ex: Courses au supermarché...">
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">
                            <?= EFinanceIcon::Save->getHtmlSvg('icon-sm') ?>
                            Enregistrer les modifications
                        </button>
                        <a href="/transactions/<?= $transactionId ?>" class="btn btn-ghost">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
const transactionId = <?= $transactionId ?>;
let allCategories = [];
let originalData = null;

async function loadAccounts() {
    try {
        const res = await apiRequest('GET', '/api/accounts');
        const select = document.getElementById('accountSelect');
        (res.data || []).forEach(acc => {
            const opt = document.createElement('option');
            opt.value = acc.id;
            opt.textContent = acc.name;
            select.appendChild(opt);
        });
    } catch (error) {
        console.error('Erreur chargement comptes:', error);
    }
}

async function loadCategories() {
    try {
        const res = await apiRequest('GET', '/api/categories');
        allCategories = res.data || [];
    } catch (error) {
        console.error('Erreur chargement catégories:', error);
    }
}

function updateCategorySelect(selectedId = null) {
    const type = document.querySelector('input[name="type"]:checked')?.value || 'expense';
    const select = document.getElementById('categorySelect');
    select.innerHTML = '<option value="">Sélectionner une catégorie</option>';
    
    allCategories
        .filter(cat => cat.type === type)
        .forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            if (selectedId && cat.id == selectedId) {
                opt.selected = true;
            }
            select.appendChild(opt);
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
        
        // Vérifier si c'est une transaction Bridge
        if (response.data.id_bridge) {
            alert('Les transactions importées ne peuvent pas être modifiées');
            window.location.href = `/transactions/${transactionId}`;
            return;
        }
        
        originalData = response.data;
        const tx = originalData;
        const form = document.getElementById('editTransactionForm');
        
        // Remplir le formulaire
        const type = parseFloat(tx.amount) >= 0 ? 'income' : 'expense';
        document.querySelector(`input[name="type"][value="${type}"]`).checked = true;
        
        form.account_id.value = tx.account_id;
        form.amount.value = Math.abs(parseFloat(tx.amount));
        form.date.value = tx.date;
        form.description.value = tx.description || '';
        
        // Mettre à jour les catégories après avoir défini le type
        updateCategorySelect(tx.category_id);
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de la transaction');
    }
}

// Écouter le changement de type
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', () => updateCategorySelect());
});

document.getElementById('editTransactionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const data = {
        type: form.type.value,
        account_id: parseInt(form.account_id.value),
        amount: parseFloat(form.amount.value),
        category_id: parseInt(form.category_id.value),
        date: form.date.value,
        description: form.description.value || null
    };
    
    try {
        const result = await apiRequest('PUT', `/api/transactions/${transactionId}`, data);
        if (result.code === 200) {
            window.location.href = `/transactions/${transactionId}`;
        } else {
            alert(result.error || 'Erreur lors de la modification');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la modification de la transaction');
    }
});

document.addEventListener('DOMContentLoaded', async () => {
    await loadAccounts();
    await loadCategories();
    await loadTransaction();
});
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
