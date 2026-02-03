// Categories Page JavaScript

let currentTab = 'expense';
let categoriesData = [];

document.addEventListener('DOMContentLoaded', () => {
    initMonthSelector();
    loadCategories();
    loadBudgetStats();
    
    document.getElementById('category-form').addEventListener('submit', handleCategorySubmit);
});

function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

function initMonthSelector() {
    const select = document.getElementById('budget-month');
    const today = new Date();
    
    for (let i = 0; i < 6; i++) {
        const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
        const value = date.toISOString().slice(0, 7);
        const label = date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label.charAt(0).toUpperCase() + label.slice(1);
        select.appendChild(option);
    }
}

function setTab(tab) {
    currentTab = tab;
    
    document.getElementById('tab-expense').classList.toggle('active', tab === 'expense');
    document.getElementById('tab-income').classList.toggle('active', tab === 'income');
    
    document.getElementById('budget-overview').style.display = tab === 'expense' ? 'block' : 'none';
    document.getElementById('categories-title').textContent = 
        tab === 'expense' ? 'Catégories de dépenses' : 'Catégories de revenus';
    
    renderCategories();
}

function toggleBudgetField() {
    const type = document.getElementById('form-type').value;
    document.getElementById('budget-field').style.display = type === 'expense' ? 'block' : 'none';
}

async function loadCategories() {
    try {
        const response = await fetch('/api/categories');
        const data = await response.json();

        if (data.code === 200) {
            categoriesData = data.data;
            renderCategories();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function renderCategories() {
    const container = document.getElementById('categories-container');
    const filtered = categoriesData.filter(c => c.type === currentTab);
    
    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa fa-tags"></i>
                <p>Aucune catégorie de ${currentTab === 'expense' ? 'dépense' : 'revenu'}</p>
                <button class="btn btn-primary" onclick="openModal('category-modal')">
                    <i class="fa fa-plus"></i> Ajouter une catégorie
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = filtered.map(cat => `
        <div class="category-card">
            <div class="category-header">
                <div class="category-icon" style="background: ${cat.color || '#64748b'}">
                    <i class="fa ${cat.icon || 'fa-tag'}"></i>
                </div>
                <div class="category-info">
                    <div class="category-name">${cat.name}</div>
                    <div class="category-type">${cat.type === 'expense' ? 'Dépense' : 'Revenu'}</div>
                </div>
            </div>
            
            ${cat.type === 'expense' && cat.budget ? `
                <div class="category-stats">
                    <div class="category-stat">
                        <span class="category-stat-label">Budget</span>
                        <span class="category-stat-value">${formatAmount(cat.budget)}</span>
                    </div>
                </div>
            ` : ''}
            
            <div class="category-actions">
                <button class="btn-edit-cat" onclick="editCategory(${cat.id})">
                    <i class="fa fa-edit"></i> Modifier
                </button>
                <button class="btn-delete-cat" onclick="deleteCategory(${cat.id})">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

async function loadBudgetStats() {
    const month = document.getElementById('budget-month').value || new Date().toISOString().slice(0, 7);
    
    try {
        const response = await fetch(`/api/categories/stats?month=${month}`);
        const data = await response.json();

        if (data.code === 200) {
            renderBudgetStats(data.data);
        }
    } catch (error) {
        console.error('Error loading budget stats:', error);
    }
}

function renderBudgetStats(stats) {
    let totalBudget = 0;
    let totalSpent = 0;
    
    stats.forEach(cat => {
        totalBudget += parseFloat(cat.budget) || 0;
        totalSpent += parseFloat(cat.spent) || 0;
    });
    
    const remaining = totalBudget - totalSpent;
    
    document.getElementById('budget-total-value').textContent = formatAmount(totalBudget);
    document.getElementById('budget-spent-value').textContent = formatAmount(totalSpent);
    
    const remainingEl = document.getElementById('budget-remaining-value');
    remainingEl.textContent = formatAmount(remaining);
    remainingEl.style.color = remaining >= 0 ? 'var(--color-success)' : 'var(--color-danger)';
    
    // Render progress bars
    const container = document.getElementById('budget-progress-container');
    const budgetedCategories = stats.filter(c => c.budget > 0);
    
    if (budgetedCategories.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 20px; color: var(--text-light);">
                <p>Aucun budget défini pour ce mois</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = budgetedCategories.map(cat => {
        const percentage = Math.min((cat.spent / cat.budget) * 100, 100);
        const isOverBudget = cat.spent > cat.budget;
        const color = isOverBudget ? 'var(--color-danger)' : (percentage > 80 ? 'var(--color-warning)' : cat.color || 'var(--color-success)');
        
        return `
            <div class="budget-progress-item">
                <div class="budget-progress-header">
                    <span class="budget-progress-name">
                        <span class="budget-progress-icon" style="background: ${cat.color || '#64748b'}">
                            <i class="fa ${cat.icon || 'fa-tag'}"></i>
                        </span>
                        ${cat.name}
                    </span>
                    <span class="budget-progress-values">
                        ${formatAmount(cat.spent)} / ${formatAmount(cat.budget)}
                    </span>
                </div>
                <div class="budget-progress-bar">
                    <div class="budget-progress-fill" style="width: ${percentage}%; background: ${color}"></div>
                </div>
            </div>
        `;
    }).join('');
}

// Modal Functions
function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
    
    if (id === 'category-modal') {
        document.getElementById('form-type').value = currentTab;
        toggleBudgetField();
    }
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = 'auto';
    
    if (id === 'category-modal') {
        document.getElementById('category-form').reset();
        document.getElementById('category-id').value = '';
        document.getElementById('category-modal-title').textContent = 'Nouvelle catégorie';
        document.getElementById('form-color').value = '#2563eb';
        document.getElementById('form-type').value = currentTab;
        toggleBudgetField();
    }
}

async function editCategory(id) {
    try {
        const response = await fetch(`/api/categories/${id}`);
        const data = await response.json();

        if (data.code === 200) {
            const cat = data.data;
            const form = document.getElementById('category-form');
            
            document.getElementById('category-id').value = cat.id;
            document.getElementById('category-modal-title').textContent = 'Modifier la catégorie';
            
            form.querySelector('[name="name"]').value = cat.name;
            form.querySelector('[name="type"]').value = cat.type;
            form.querySelector('[name="budget"]').value = cat.budget || '';
            form.querySelector('[name="icon"]').value = cat.icon || '';
            form.querySelector('[name="color"]').value = cat.color || '#2563eb';
            
            toggleBudgetField();
            openModal('category-modal');
        }
    } catch (error) {
        console.error('Error loading category:', error);
    }
}

async function handleCategorySubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Clear budget for income categories
    if (data.type === 'income') {
        data.budget = null;
    }
    
    const id = document.getElementById('category-id').value;
    const isEdit = id !== '';
    
    try {
        const response = await fetch(isEdit ? `/api/categories/${id}` : '/api/categories', {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.code === 200 || result.code === 201) {
            closeModal('category-modal');
            loadCategories();
            loadBudgetStats();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error saving category:', error);
        alert('Erreur réseau');
    }
}

async function deleteCategory(id) {
    if (!confirm('Supprimer cette catégorie ?')) return;
    
    try {
        const response = await fetch(`/api/categories/${id}`, { method: 'DELETE' });
        const result = await response.json();

        if (result.code === 200) {
            loadCategories();
            loadBudgetStats();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error deleting category:', error);
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
