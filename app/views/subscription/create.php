<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Recurring->getHtmlSvg() ?>
                    Nouvel abonnement
                </div>
                <a href="/subscriptions" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
        </section>

        <!-- Formulaire -->
        <section class="card" style="width: fit-content;">
            <div class="card-header">
                <div class="card-title">Informations de l'abonnement</div>
                <span class="badge badge-neutral" id="periodLabel">Mensuel</span>
            </div>
            <div class="card-body">
                <form id="subscriptionForm" style="display:flex;flex-direction:column;gap:1rem;max-width:500px;">
                    <div class="input-group">
                        <label class="input-label">Nom de l'abonnement <span class="required">*</span></label>
                        <div class="input-control">
                            <input type="text" name="name" placeholder="Ex: Netflix, Spotify, Salaire..." required>
                        </div>
                    </div>

                    <div class="row" style="display:flex;gap:1rem;">
                        <div class="input-group" style="flex:1;">
                            <label class="input-label">Montant (€) <span class="required">*</span></label>
                            <div class="input-control">
                                <input type="number" name="amount" step="0.01" min="0" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="input-group" style="flex:1;">
                            <label class="input-label">Type <span class="required">*</span></label>
                            <div class="input-control">
                                <select name="type" required>
                                    <option value="expense">Dépense</option>
                                    <option value="income">Revenu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Fréquence <span class="required">*</span></label>
                        <div class="input-control">
                            <select name="type_period" required onchange="updatePeriodFields()">
                                <option value="monthly">Mensuel</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="yearly">Annuel</option>
                            </select>
                        </div>
                    </div>

                    <div id="periodFields">
                        <!-- Champs dynamiques selon la fréquence -->
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">
                            <?= EFinanceIcon::Save->getHtmlSvg('icon-sm') ?>
                            Créer l'abonnement
                        </button>
                        <a href="/subscriptions" class="btn btn-ghost">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
function getPeriodLabel(period) {
    return {
        'weekly': 'Hebdomadaire',
        'monthly': 'Mensuel',
        'yearly': 'Annuel'
    }[period] || period;
}

function updatePeriodFields() {
    const period = document.querySelector('[name="type_period"]').value;
    const container = document.getElementById('periodFields');
    
    // Mettre à jour le badge
    document.getElementById('periodLabel').textContent = getPeriodLabel(period);
    
    if (period === 'weekly') {
        container.innerHTML = `
            <div class="input-group">
                <label class="input-label">Jour de la semaine</label>
                <div class="input-control">
                    <select name="day_of_week">
                        <option value="1">Lundi</option>
                        <option value="2">Mardi</option>
                        <option value="3">Mercredi</option>
                        <option value="4">Jeudi</option>
                        <option value="5">Vendredi</option>
                        <option value="6">Samedi</option>
                        <option value="7">Dimanche</option>
                    </select>
                </div>
            </div>
        `;
    } else if (period === 'monthly') {
        container.innerHTML = `
            <div class="input-group">
                <label class="input-label">Jour du mois</label>
                <div class="input-control">
                    <select name="day_of_month">
                        ${Array.from({length: 31}, (_, i) => `<option value="${i+1}">${i+1}</option>`).join('')}
                    </select>
                </div>
            </div>
        `;
    } else if (period === 'yearly') {
        container.innerHTML = `
            <div class="input-group">
                <label class="input-label">Date annuelle</label>
                <div class="input-control">
                    <input type="date" name="date_of_year">
                </div>
            </div>
        `;
    }
}

document.getElementById('subscriptionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const period = form.type_period.value;
    
    const data = {
        name: form.name.value,
        amount: parseFloat(form.amount.value),
        type: form.type.value,
        type_period: period,
    };
    
    if (period === 'weekly' && form.day_of_week) {
        data.day_of_week = parseInt(form.day_of_week.value);
    } else if (period === 'monthly' && form.day_of_month) {
        data.day_of_month = parseInt(form.day_of_month.value);
    } else if (period === 'yearly' && form.date_of_year) {
        data.date_of_year = form.date_of_year.value;
    }
    
    try {
        const response = await apiRequest('POST', '/api/subscriptions', data);
        
        if (response.code === 201) {
            window.location.href = '/subscriptions';
        } else {
            alert(response.error || 'Erreur lors de la création');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la création');
    }
});

// Initialiser les champs de période
document.addEventListener('DOMContentLoaded', updatePeriodFields);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
