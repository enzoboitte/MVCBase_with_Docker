<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Edit->getHtmlSvg() ?>
                    Créer un compte personnalisé
                </div>
                <a href="/accounts/create" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
        </section>

        <!-- Formulaire -->
        <section class="card" style="width: fit-content;">
            <div class="card-header">
                <div class="card-title">Informations du compte</div>
            </div>
            <div class="card-body">
                <form id="createAccountForm" style="display:flex;flex-direction:column;gap:1rem;max-width:500px;">
                    <div class="input-group">
                        <label class="input-label">Nom du compte <span class="required">*</span></label>
                        <div class="input-control">
                            <input type="text" name="name" placeholder="Ex: Compte Courant, Épargne..." required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Type de compte <span class="required">*</span></label>
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

                    <div class="input-group">
                        <label class="input-label">Solde initial (€)</label>
                        <div class="input-control">
                            <input type="number" name="balance" step="0.01" value="0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Couleur</label>
                        <div class="input-control" style="padding:0.25rem;">
                            <input type="color" name="color" value="#2563eb" style="width:100%;height:36px;border:none;cursor:pointer;">
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">
                            <?= EFinanceIcon::Plus->getHtmlSvg('icon-sm') ?>
                            Créer le compte
                        </button>
                        <a href="/accounts" class="btn btn-ghost">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
document.getElementById('createAccountForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const data = {
        name: form.name.value,
        type: form.type.value,
        balance: parseFloat(form.balance.value) || 0,
        color: form.color.value
    };
    
    try {
        const result = await apiRequest('POST', '/api/accounts', data);
        if (result.code === 201) {
            window.location.href = '/accounts';
        } else {
            alert(result.error || 'Erreur lors de la création');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la création du compte');
    }
});
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
