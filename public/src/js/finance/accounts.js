// Accounts Page JavaScript

document.addEventListener('DOMContentLoaded', () => {
    loadAccounts();
    document.getElementById('account-form').addEventListener('submit', handleAccountSubmit);
    document.getElementById('transfer-form').addEventListener('submit', handleTransfer);
});

function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
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

async function loadAccounts() {
    try {
        const response = await fetch('/api/accounts');
        const data = await response.json();

        if (data.code === 200) {
            // Net worth
            document.getElementById('net-worth').textContent = formatAmount(data.net_worth);

            // Accounts grid
            const grid = document.getElementById('accounts-grid');
            if (data.data.length > 0) {
                grid.innerHTML = data.data.map(account => `
                    <div class="account-card">
                        <div class="account-card-header">
                            <div class="account-card-icon" style="background: ${account.color}">
                                <i class="fa ${account.icon}"></i>
                            </div>
                            <div class="account-card-info">
                                <h4>${account.name}</h4>
                                <span class="account-card-type">${getAccountTypeLabel(account.type)}</span>
                            </div>
                        </div>
                        <div class="account-card-balance">${formatAmount(account.current_balance)}</div>
                        <div class="account-card-actions">
                            <button class="btn-edit" onclick="editAccount(${account.id})">
                                <i class="fa fa-edit"></i> Modifier
                            </button>
                            <button class="btn-delete" onclick="deleteAccount(${account.id}, '${account.name}')">
                                <i class="fa fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div class="empty-state"><i class="fa fa-university"></i><p>Aucun compte. Créez votre premier compte !</p></div>';
            }

            // Populate transfer selects
            populateAccountSelects(data.data);
        }
    } catch (error) {
        console.error('Error loading accounts:', error);
    }
}

function populateAccountSelects(accounts) {
    const fromSelect = document.getElementById('from-account');
    const toSelect = document.getElementById('to-account');
    
    const options = accounts.map(a => `<option value="${a.id}">${a.name} (${formatAmount(a.current_balance)})</option>`).join('');
    
    fromSelect.innerHTML = options;
    toSelect.innerHTML = options;
}

function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = 'auto';
    // Reset form
    if (id === 'account-modal') {
        document.getElementById('account-form').reset();
        document.getElementById('account-id').value = '';
        document.getElementById('account-modal-title').textContent = 'Nouveau compte';
    }
}

async function editAccount(id) {
    try {
        const response = await fetch(`/api/accounts/${id}`);
        const data = await response.json();

        if (data.code === 200) {
            const account = data.data;
            const form = document.getElementById('account-form');
            
            document.getElementById('account-id').value = account.id;
            document.getElementById('account-modal-title').textContent = 'Modifier le compte';
            
            form.querySelector('[name="name"]').value = account.name;
            form.querySelector('[name="type"]').value = account.type;
            form.querySelector('[name="current_balance"]').value = account.current_balance;
            form.querySelector('[name="icon"]').value = account.icon;
            form.querySelector('[name="color"]').value = account.color;
            form.querySelector('[name="include_in_net_worth"]').checked = account.include_in_net_worth;
            
            openModal('account-modal');
        }
    } catch (error) {
        console.error('Error loading account:', error);
    }
}

async function handleAccountSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    data.include_in_net_worth = form.querySelector('[name="include_in_net_worth"]').checked;
    
    const id = document.getElementById('account-id').value;
    const isEdit = id !== '';
    
    try {
        const response = await fetch(isEdit ? `/api/accounts/${id}` : '/api/accounts', {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.code === 200 || result.code === 201) {
            closeModal('account-modal');
            loadAccounts();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error saving account:', error);
        alert('Erreur réseau');
    }
}

async function deleteAccount(id, name) {
    if (!confirm(`Supprimer le compte "${name}" ? Cette action est irréversible.`)) {
        return;
    }

    try {
        const response = await fetch(`/api/accounts/${id}`, { method: 'DELETE' });
        const result = await response.json();

        if (result.code === 200) {
            loadAccounts();
        } else {
            alert(result.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Error deleting account:', error);
        alert('Erreur réseau');
    }
}

async function handleTransfer(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    if (data.from_account_id === data.to_account_id) {
        alert('Les comptes source et destination doivent être différents');
        return;
    }

    try {
        const response = await fetch('/api/accounts/transfer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.code === 200) {
            form.reset();
            loadAccounts();
            alert('Virement effectué avec succès !');
        } else {
            alert(result.message || 'Erreur lors du virement');
        }
    } catch (error) {
        console.error('Error transferring:', error);
        alert('Erreur réseau');
    }
}

// Close modal on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal(modal.id);
        }
    });
});
