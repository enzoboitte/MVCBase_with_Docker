<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Edit->getHtmlSvg() ?>
                    Modifier l'abonnement
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
                <span class="badge badge-neutral" id="periodLabel">--</span>
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
                            Enregistrer les modifications
                        </button>
                        <a href="/subscriptions" class="btn btn-ghost">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
const subscriptionId = <?= json_encode($subscriptionId ?? null) ?>;
let currentSubscription = null;

function getPeriodLabel(period) {
    return {
        'weekly': 'Hebdomadaire',
        'monthly': 'Mensuel',
        'yearly': 'Annuel'
    }[period] || period;
}

function updatePeriodFields(subscription = null) {
    const period = document.querySelector('[name="type_period"]').value;
    const container = document.getElementById('periodFields');
    
    // Mettre à jour le badge
    document.getElementById('periodLabel').textContent = getPeriodLabel(period);
    
    if (period === 'weekly') {
        const selectedDay = subscription?.day_of_week || 1;
        container.innerHTML = `
            <div class="input-group">
                <label class="input-label">Jour de la semaine</label>
                <div class="input-control">
                    <select name="day_of_week">
                        <option value="1" ${selectedDay == 1 ? 'selected' : ''}>Lundi</option>
                        <option value="2" ${selectedDay == 2 ? 'selected' : ''}>Mardi</option>
                        <option value="3" ${selectedDay == 3 ? 'selected' : ''}>Mercredi</option>
                        <option value="4" ${selectedDay == 4 ? 'selected' : ''}>Jeudi</option>
                        <option value="5" ${selectedDay == 5 ? 'selected' : ''}>Vendredi</option>
                        <option value="6" ${selectedDay == 6 ? 'selected' : ''}>Samedi</option>
                        <option value="7" ${selectedDay == 7 ? 'selected' : ''}>Dimanche</option>
                    </select>
                </div>
            </div>
        `;
    } else if (period === 'monthly') {
        const selectedDay = subscription?.day_of_month || 1;
        container.innerHTML = `
            <div class="input-group">
                <label class="input-label">Jour du mois</label>
                <div class="input-control">
                    <select name="day_of_month">
                        ${Array.from({length: 31}, (_, i) => `<option value="${i+1}" ${selectedDay == i+1 ? 'selected' : ''}>${i+1}</option>`).join('')}
                    </select>
                </div>
            </div>
        `;
    } else if (period === 'yearly') {
        const selectedDate = subscription?.date_of_year || '';
        container.innerHTML = `
            <div class="input-group">
                <label class="input-label">Date annuelle</label>
                <div class="input-control">
                    <input type="date" name="date_of_year" value="${selectedDate}">
                </div>
            </div>
        `;
    }
}

async function loadSubscription() {
    if (!subscriptionId) return;
    
    try {
        const response = await apiRequest('GET', `/api/subscriptions/${subscriptionId}`);
        
        if (response.code !== 200) {
            alert('Abonnement non trouvé');
            window.location.href = '/subscriptions';
            return;
        }
        
        currentSubscription = response.data;
        const form = document.getElementById('subscriptionForm');
        
        form.name.value = currentSubscription.name;
        form.amount.value = currentSubscription.amount;
        form.type.value = currentSubscription.type;
        form.type_period.value = currentSubscription.type_period;
        
        updatePeriodFields(currentSubscription);
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de l\'abonnement');
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
        const result = await apiRequest('PUT', `/api/subscriptions/${subscriptionId}`, data);
        
        if (result.code === 200) {
            window.location.href = '/subscriptions';
        } else {
            alert(result.error || 'Erreur lors de la modification');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la modification de l\'abonnement');
    }
});

document.addEventListener('DOMContentLoaded', loadSubscription);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
