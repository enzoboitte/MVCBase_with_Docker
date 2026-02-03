// Dashboard Finance - JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Afficher la date courante
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Charger toutes les données
    loadSummary();
    loadForecast();
    loadAccounts();
    loadUpcoming();
    loadRecentTransactions();
    loadBudgets();
    loadFormData();

    // Initialiser le formulaire de transaction
    document.getElementById('quick-transaction-form').addEventListener('submit', handleQuickTransaction);
    document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];
});

// Formatter les montants
function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

// Charger le résumé (KPIs)
async function loadSummary() {
    try {
        const response = await fetch('/api/forecast/summary');
        const data = await response.json();

        if (data.code === 200) {
            document.getElementById('kpi-net-worth').textContent = formatAmount(data.net_worth);
            document.getElementById('kpi-income').textContent = formatAmount(data.month_income);
            document.getElementById('kpi-expense').textContent = formatAmount(data.month_expense);
            
            const balanceEl = document.getElementById('kpi-balance');
            balanceEl.textContent = formatAmount(data.month_balance);
            balanceEl.style.color = data.month_balance >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
        }
    } catch (error) {
        console.error('Error loading summary:', error);
    }
}

// Charger les prévisions
let forecastChart = null;

async function loadForecast() {
    try {
        const response = await fetch('/api/forecast/current-month');
        const data = await response.json();

        if (data.code === 200) {
            // Mettre à jour les valeurs
            document.getElementById('forecast-current').textContent = formatAmount(data.current_global_balance);
            document.getElementById('forecast-income').textContent = '+' + formatAmount(data.projected_income);
            document.getElementById('forecast-fixed').textContent = '-' + formatAmount(data.projected_fixed_costs);
            document.getElementById('forecast-variable').textContent = '-' + formatAmount(data.projected_variable_spend);
            
            const endEl = document.getElementById('forecast-end');
            endEl.textContent = formatAmount(data.estimated_end_balance);
            endEl.className = 'value ' + (data.estimated_end_balance >= 0 ? 'positive' : 'negative');

            // Statut
            const statusEl = document.getElementById('forecast-status');
            statusEl.className = 'forecast-status ' + data.status;
            statusEl.textContent = data.status === 'safe' ? '✓ Sain' : 
                                   data.status === 'warning' ? '⚠ Attention' : '⚠ Danger';

            // Graphique
            renderForecastChart(data.balance_history);
        }
    } catch (error) {
        console.error('Error loading forecast:', error);
    }
}

function renderForecastChart(history) {
    const ctx = document.getElementById('forecast-chart').getContext('2d');
    
    const labels = history.map(h => {
        const date = new Date(h.date);
        return date.getDate();
    });
    
    const actualData = history.map(h => h.type === 'actual' ? h.balance : null);
    const projectedData = history.map(h => h.type === 'projected' ? h.balance : null);
    
    // Connecter les deux lignes
    const lastActualIndex = actualData.findLastIndex(v => v !== null);
    if (lastActualIndex >= 0 && lastActualIndex < projectedData.length) {
        projectedData[lastActualIndex] = actualData[lastActualIndex];
    }

    if (forecastChart) {
        forecastChart.destroy();
    }

    forecastChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Solde réel',
                    data: actualData,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2
                },
                {
                    label: 'Prévision',
                    data: projectedData,
                    borderColor: '#94a3b8',
                    borderDash: [5, 5],
                    backgroundColor: 'rgba(148, 163, 184, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => formatAmount(context.raw)
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    title: { display: true, text: 'Jour du mois' }
                },
                y: {
                    grid: { color: '#e2e8f0' },
                    ticks: {
                        callback: (value) => formatAmount(value)
                    }
                }
            }
        }
    });
}

// Charger les comptes
async function loadAccounts() {
    try {
        const response = await fetch('/api/accounts');
        const data = await response.json();
        const container = document.getElementById('accounts-list');

        if (data.code === 200 && data.data.length > 0) {
            container.innerHTML = data.data.slice(0, 4).map(account => `
                <div class="account-item">
                    <div class="account-icon" style="background: ${account.color}">
                        <i class="fa ${account.icon}"></i>
                    </div>
                    <div class="account-info">
                        <div class="account-name">${account.name}</div>
                        <div class="account-type">${getAccountTypeLabel(account.type)}</div>
                    </div>
                    <div class="account-balance">${formatAmount(account.current_balance)}</div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fa fa-university"></i><p>Aucun compte</p></div>';
        }
    } catch (error) {
        console.error('Error loading accounts:', error);
    }
}

function getAccountTypeLabel(type) {
    const labels = {
        'checking': 'Compte courant',
        'savings': 'Épargne',
        'cash': 'Espèces',
        'credit_card': 'Carte de crédit'
    };
    return labels[type] || type;
}

// Charger les abonnements à venir
async function loadUpcoming() {
    try {
        const response = await fetch('/api/subscriptions/upcoming?days=7');
        const data = await response.json();
        const container = document.getElementById('upcoming-list');

        if (data.code === 200 && data.data.length > 0) {
            container.innerHTML = data.data.slice(0, 5).map(sub => {
                const dueDate = new Date(sub.next_due_date);
                const today = new Date();
                const diffDays = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
                const dateLabel = diffDays === 0 ? "Aujourd'hui" : 
                                  diffDays === 1 ? 'Demain' : 
                                  `Dans ${diffDays} jours`;
                
                return `
                    <div class="upcoming-item">
                        <div class="upcoming-icon" style="background: ${sub.color}">
                            <i class="fa ${sub.icon}"></i>
                        </div>
                        <div class="upcoming-info">
                            <div class="upcoming-name">${sub.name}</div>
                            <div class="upcoming-date">${dateLabel}</div>
                        </div>
                        <div class="upcoming-amount ${sub.type}">${sub.type === 'expense' ? '-' : '+'}${formatAmount(sub.amount)}</div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fa fa-calendar-check-o"></i><p>Aucune échéance prochaine</p></div>';
        }
    } catch (error) {
        console.error('Error loading upcoming:', error);
    }
}

// Charger les transactions récentes
async function loadRecentTransactions() {
    try {
        const response = await fetch('/api/transactions?limit=5');
        const data = await response.json();
        const container = document.getElementById('recent-transactions');

        if (data.code === 200 && data.data.length > 0) {
            container.innerHTML = data.data.map(tx => {
                const date = new Date(tx.date);
                return `
                    <div class="transaction-item">
                        <div class="transaction-icon" style="background: ${tx.category_color || '#64748b'}20; color: ${tx.category_color || '#64748b'}">
                            <i class="fa ${tx.category_icon || 'fa-exchange'}"></i>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-description">${tx.description}</div>
                            <div class="transaction-category">${tx.category_name || 'Sans catégorie'} • ${tx.account_name}</div>
                        </div>
                        <div class="transaction-date">${date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })}</div>
                        <div class="transaction-amount ${tx.type}">${tx.type === 'expense' ? '-' : '+'}${formatAmount(tx.amount)}</div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fa fa-exchange"></i><p>Aucune transaction</p></div>';
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
    }
}

// Charger les budgets
async function loadBudgets() {
    try {
        const response = await fetch('/api/categories/stats');
        const data = await response.json();
        const container = document.getElementById('budget-list');

        if (data.code === 200) {
            const budgetsWithAmount = data.data.filter(b => b.budget_amount !== null);
            
            if (budgetsWithAmount.length > 0) {
                container.innerHTML = budgetsWithAmount.map(budget => {
                    const percentage = Math.min(budget.percentage || 0, 100);
                    const statusClass = percentage >= 100 ? 'danger' : percentage >= 80 ? 'warning' : 'safe';
                    
                    return `
                        <div class="budget-item">
                            <div class="budget-header">
                                <span class="budget-name">
                                    <i style="background: ${budget.color}"class="fa ${budget.icon}"></i>
                                    ${budget.name}
                                </span>
                                <span class="budget-values">${formatAmount(budget.spent)} / ${formatAmount(budget.budget_amount)}</span>
                            </div>
                            <div class="budget-progress">
                                <div class="budget-progress-bar ${statusClass}" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<div class="empty-state"><i class="fa fa-pie-chart"></i><p>Aucun budget défini</p></div>';
            }
        }
    } catch (error) {
        console.error('Error loading budgets:', error);
    }
}

// Charger les données pour le formulaire
async function loadFormData() {
    try {
        const [accountsRes, categoriesRes] = await Promise.all([
            fetch('/api/accounts'),
            fetch('/api/categories')
        ]);
        
        const accounts = await accountsRes.json();
        const categories = await categoriesRes.json();

        const accountSelect = document.getElementById('form-account');
        const categorySelect = document.getElementById('form-category');

        if (accounts.code === 200) {
            accountSelect.innerHTML = accounts.data.map(a => 
                `<option value="${a.id}">${a.name}</option>`
            ).join('');
        }

        if (categories.code === 200) {
            categorySelect.innerHTML = '<option value="">-- Sans catégorie --</option>' + 
                categories.data.map(c => 
                    `<option value="${c.id}">${c.name}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error loading form data:', error);
    }
}

// Gérer l'ajout rapide de transaction
async function handleQuickTransaction(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    try {
        const response = await fetch('/api/transactions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.code === 201) {
            closeModal('quick-transaction-modal');
            form.reset();
            document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];
            
            // Recharger les données
            loadSummary();
            loadForecast();
            loadAccounts();
            loadRecentTransactions();
            loadBudgets();
        } else {
            alert(result.message || 'Erreur lors de la création');
        }
    } catch (error) {
        console.error('Error creating transaction:', error);
        alert('Erreur réseau');
    }
}

// Gestion des modals
function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Fermer modal en cliquant à l'extérieur
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    });
});
