<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Edit->getHtmlSvg() ?>
                    Modifier le compte
                </div>
                <a href="/accounts/<?= $accountId ?>" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
        </section>

        <!-- Formulaire -->
        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">Informations du compte</div>
                <span class="badge badge-neutral" id="sourceLabel">Manuel</span>
            </div>
            <div class="card-body">
                <form id="editAccountForm" style="display:flex;flex-direction:column;gap:1rem;max-width:500px;">
                    <div class="input-group">
                        <label class="input-label">Nom du compte *</label>
                        <div class="input-control">
                            <input type="text" name="name" placeholder="Ex: Compte Courant, Épargne..." required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Type de compte *</label>
                        <div class="input-control">
                            <select name="type" required>
                                <option value="checking">Compte courant</option>
                                <option value="savings">Épargne</option>
                                <option value="credit_card">Carte de crédit</option>
                                <option value="cash">Espèces</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group" id="balanceGroup">
                        <label class="input-label">Solde (€)</label>
                        <div class="input-control">
                            <input type="number" name="balance" step="0.01" value="0" placeholder="0.00">
                        </div>
                        <small class="text-muted" id="balanceNote" style="display:none;">
                            Ce compte est synchronisé avec Bridge. Le solde sera mis à jour automatiquement.
                        </small>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Couleur</label>
                        <div class="input-control" style="padding:0.25rem;">
                            <input type="color" name="color" value="#2563eb" style="width:100%;height:36px;border:none;cursor:pointer;">
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">
                            <?= EFinanceIcon::Save->getHtmlSvg('icon-sm') ?>
                            Enregistrer les modifications
                        </button>
                        <a href="/accounts/<?= $accountId ?>" class="btn btn-ghost">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
const accountId = <?= $accountId ?>;

async function loadAccount() {
    try {
        const response = await apiRequest('GET', `/api/accounts/${accountId}`);
        
        if (response.code !== 200) {
            alert('Compte non trouvé');
            window.location.href = '/accounts';
            return;
        }
        
        const account = response.data;
        const form = document.getElementById('editAccountForm');
        
        form.name.value = account.name;
        form.type.value = account.type;
        form.balance.value = account.balance;
        form.color.value = account.color;
        
        // Si compte Bridge, afficher un avertissement pour le solde
        if (account.id_bridge) {
            document.getElementById('sourceLabel').textContent = 'Synchronisé Bridge';
            document.getElementById('sourceLabel').classList.remove('badge-neutral');
            document.getElementById('sourceLabel').classList.add('badge-success');
            document.getElementById('balanceNote').style.display = 'block';
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement du compte');
    }
}

document.getElementById('editAccountForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const data = {
        name: form.name.value,
        type: form.type.value,
        balance: parseFloat(form.balance.value) || 0,
        color: form.color.value
    };
    
    try {
        const result = await apiRequest('PUT', `/api/accounts/${accountId}`, data);
        if (result.code === 200) {
            window.location.href = `/accounts/${accountId}`;
        } else {
            alert(result.error || 'Erreur lors de la modification');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la modification du compte');
    }
});

document.addEventListener('DOMContentLoaded', loadAccount);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
