// Transactions Page JavaScript

let currentPage = 1;
let currentFilters = {};
let accountsData = [];
let categoriesData = [];

document.addEventListener('DOMContentLoaded', () => {
    // Set default month filter to current month
    document.getElementById('filter-month').value = new Date().toISOString().slice(0, 7);
    document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];
    
    loadFiltersData();
    loadTransactions();
    loadStats();
    
    document.getElementById('transaction-form').addEventListener('submit', handleTransactionSubmit);
    document.getElementById('form-type').addEventListener('change', updateCategoryOptions);
});

function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

async function loadFiltersData() {
    try {
        const [accountsRes, categoriesRes] = await Promise.all([
            fetch('/api/accounts'),
            fetch('/api/categories')
        ]);
        
        const accounts = await accountsRes.json();
        const categories = await categoriesRes.json();

        if (accounts.code === 200) {
            accountsData = accounts.data;
            const filterAccount = document.getElementById('filter-account');
            const formAccount = document.getElementById('form-account');
            
            const accountOptions = accounts.data.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
            
            filterAccount.innerHTML = '<option value="">Tous les comptes</option>' + accountOptions;
            formAccount.innerHTML = accountOptions;
        }

        if (categories.code === 200) {
            categoriesData = categories.data;
            const filterCategory = document.getElementById('filter-category');
            
            filterCategory.innerHTML = '<option value="">Toutes les catégories</option>' + 
                categories.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            
            updateCategoryOptions();
        }
    } catch (error) {
        console.error('Error loading filters data:', error);
    }
}

function updateCategoryOptions() {
    const type = document.getElementById('form-type').value;
    const formCategory = document.getElementById('form-category');
    const filteredCategories = categoriesData.filter(c => c.type === type);
    
    formCategory.innerHTML = '<option value="">-- Sans catégorie --</option>' + 
        filteredCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
}

async function loadTransactions(page = 1) {
    currentPage = page;
    const month = document.getElementById('filter-month').value;
    const accountId = document.getElementById('filter-account').value;
    const categoryId = document.getElementById('filter-category').value;
    const type = document.getElementById('filter-type').value;
    
    let url = `/api/transactions?limit=20&offset=${(page - 1) * 20}`;
    if (month) url += `&month=${month}`;
    if (accountId) url += `&account_id=${accountId}`;
    if (categoryId) url += `&category_id=${categoryId}`;
    if (type) url += `&type=${type}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.code === 200) {
            renderTransactions(data.data);
            renderPagination(data.total, page, 20);
            document.getElementById('transactions-count').textContent = `${data.total} transaction${data.total > 1 ? 's' : ''}`;
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
    }
}

function renderTransactions(transactions) {
    const tbody = document.getElementById('transactions-body');
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
                    <i class="fa fa-exchange" style="font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 10px;"></i>
                    Aucune transaction
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = transactions.map(tx => {
        const date = new Date(tx.date);
        const formattedDate = date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
        
        let amountPrefix = '';
        if (tx.type === 'income') amountPrefix = '+';
        else if (tx.type === 'expense') amountPrefix = '-';
        
        return `
            <tr>
                <td>${formattedDate}</td>
                <td>${tx.description}</td>
                <td>
                    <div class="tx-category">
                        ${tx.category_icon ? `<span class="tx-category-icon" style="background: ${tx.category_color || '#64748b'}">
                            <i class="fa ${tx.category_icon}"></i>
                        </span>` : ''}
                        ${tx.category_name || '<span style="color: var(--text-light);">-</span>'}
                    </div>
                </td>
                <td>${tx.account_name}</td>
                <td class="tx-amount ${tx.type}">${amountPrefix}${formatAmount(tx.amount)}</td>
                <td class="tx-actions">
                    <button class="btn-edit" onclick="editTransaction(${tx.id})">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn-delete" onclick="deleteTransaction(${tx.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(total, current, perPage) {
    const container = document.getElementById('pagination');
    const totalPages = Math.ceil(total / perPage);
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `<button ${current === 1 ? 'disabled' : ''} onclick="loadTransactions(${current - 1})">←</button>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= current - 2 && i <= current + 2)) {
            html += `<button class="${i === current ? 'active' : ''}" onclick="loadTransactions(${i})">${i}</button>`;
        } else if (i === current - 3 || i === current + 3) {
            html += '<span style="padding: 0 8px;">...</span>';
        }
    }
    
    // Next button
    html += `<button ${current === totalPages ? 'disabled' : ''} onclick="loadTransactions(${current + 1})">→</button>`;
    
    container.innerHTML = html;
}

async function loadStats() {
    const month = document.getElementById('filter-month').value || new Date().toISOString().slice(0, 7);
    
    try {
        const response = await fetch(`/api/transactions/stats?month=${month}`);
        const data = await response.json();

        if (data.code === 200) {
            document.getElementById('stat-income').textContent = formatAmount(data.income);
            document.getElementById('stat-expense').textContent = formatAmount(data.expense);
            
            const balanceEl = document.getElementById('stat-balance');
            balanceEl.textContent = formatAmount(data.balance);
            balanceEl.style.color = data.balance >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function applyFilters() {
    loadTransactions(1);
    loadStats();
}

function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = 'auto';
    
    if (id === 'transaction-modal') {
        document.getElementById('transaction-form').reset();
        document.getElementById('transaction-id').value = '';
        document.getElementById('transaction-modal-title').textContent = 'Nouvelle transaction';
        document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];
        updateCategoryOptions();
    }
}

async function editTransaction(id) {
    try {
        const response = await fetch(`/api/transactions/${id}`);
        const data = await response.json();

        if (data.code === 200) {
            const tx = data.data;
            const form = document.getElementById('transaction-form');
            
            document.getElementById('transaction-id').value = tx.id;
            document.getElementById('transaction-modal-title').textContent = 'Modifier la transaction';
            
            form.querySelector('[name="type"]').value = tx.type;
            updateCategoryOptions();
            
            form.querySelector('[name="amount"]').value = tx.amount;
            form.querySelector('[name="description"]').value = tx.description;
            form.querySelector('[name="account_id"]').value = tx.account_id;
            form.querySelector('[name="category_id"]').value = tx.category_id || '';
            form.querySelector('[name="date"]').value = tx.date;
            form.querySelector('[name="notes"]').value = tx.notes || '';
            
            openModal('transaction-modal');
        }
    } catch (error) {
        console.error('Error loading transaction:', error);
    }
}

async function handleTransactionSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    const id = document.getElementById('transaction-id').value;
    const isEdit = id !== '';
    
    try {
        const response = await fetch(isEdit ? `/api/transactions/${id}` : '/api/transactions', {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.code === 200 || result.code === 201) {
            closeModal('transaction-modal');
            loadTransactions(currentPage);
            loadStats();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error saving transaction:', error);
        alert('Erreur réseau');
    }
}

async function deleteTransaction(id) {
    if (!confirm('Supprimer cette transaction ?')) return;
    
    try {
        const response = await fetch(`/api/transactions/${id}`, { method: 'DELETE' });
        const result = await response.json();

        if (result.code === 200) {
            loadTransactions(currentPage);
            loadStats();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error deleting transaction:', error);
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
