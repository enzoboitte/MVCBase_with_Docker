<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Plus->getHtmlSvg() ?>
                    Nouvelle transaction
                </div>
                <a href="/transactions" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
        </section>

        <!-- Formulaire -->
        <section class="card" style="width: fit-content;">
            <div class="card-header">
                <div class="card-title">Informations</div>
            </div>
            <div class="card-body center">
                <form id="createTransactionForm" style="display:flex;flex-direction:column;gap:1rem;max-width:500px;">
                    <!-- Type -->
                    <div class="input-group">
                        <label class="input-label">Type <span class="required">*</span></label>
                        <div style="display:flex;gap:1rem;">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="radio" name="type" value="expense" checked>
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
                        <label class="input-label">Compte <span class="required">*</span></label>
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
                        <label class="input-label">Catégorie <span class="required">*</span></label>
                        <div class="input-control">
                            <select name="category_id" id="categorySelect" required>
                                <option value="">Sélectionner une catégorie</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="input-group">
                        <label class="input-label">Date <span class="required">*</span></label>
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
                            <?= EFinanceIcon::Plus->getHtmlSvg('icon-sm') ?>
                            Créer la transaction
                        </button>
                        <a href="/transactions" class="btn btn-ghost">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
let allCategories = [];

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
        updateCategorySelect();
    } catch (error) {
        console.error('Erreur chargement catégories:', error);
    }
}

function updateCategorySelect() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const select = document.getElementById('categorySelect');
    select.innerHTML = '<option value="">Sélectionner une catégorie</option>';
    
    allCategories
        .filter(cat => cat.type === type)
        .forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            select.appendChild(opt);
        });
}

// Écouter le changement de type
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', updateCategorySelect);
});

document.getElementById('createTransactionForm').addEventListener('submit', async (e) => {
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
        const result = await apiRequest('POST', '/api/transactions', data);
        if (result.code === 201) {
            window.location.href = '/transactions';
        } else {
            alert(result.error || 'Erreur lors de la création');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la création de la transaction');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Date par défaut = aujourd'hui
    document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];
    
    loadAccounts();
    loadCategories();
});
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
