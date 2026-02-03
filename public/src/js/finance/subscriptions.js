// Subscriptions Page JavaScript

let subscriptionsData = [];
let accountsData = [];
let categoriesData = [];
let currentCalendarDate = new Date();

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('form-next-due').value = new Date().toISOString().split('T')[0];
    
    loadFormData();
    loadSubscriptions();
    loadUpcoming();
    loadStats();
    
    document.getElementById('subscription-form').addEventListener('submit', handleSubscriptionSubmit);
});

function formatAmount(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}

function translateFrequency(freq) {
    const translations = {
        daily: 'Quotidien',
        weekly: 'Hebdomadaire',
        monthly: 'Mensuel',
        yearly: 'Annuel'
    };
    return translations[freq] || freq;
}

async function loadFormData() {
    try {
        const [accountsRes, categoriesRes] = await Promise.all([
            fetch('/api/accounts'),
            fetch('/api/categories')
        ]);
        
        const accounts = await accountsRes.json();
        const categories = await categoriesRes.json();

        if (accounts.code === 200) {
            accountsData = accounts.data;
            document.getElementById('form-account').innerHTML = 
                accounts.data.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
        }

        if (categories.code === 200) {
            categoriesData = categories.data.filter(c => c.type === 'expense');
            document.getElementById('form-category').innerHTML = 
                '<option value="">-- Sans catégorie --</option>' + 
                categoriesData.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading form data:', error);
    }
}

async function loadSubscriptions() {
    const frequency = document.getElementById('filter-frequency').value;
    let url = '/api/subscriptions';
    if (frequency) url += `?frequency=${frequency}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.code === 200) {
            subscriptionsData = data.data;
            renderSubscriptions(data.data);
            document.getElementById('stat-count').textContent = data.data.filter(s => s.is_active).length;
        }
    } catch (error) {
        console.error('Error loading subscriptions:', error);
    }
}

function renderSubscriptions(subscriptions) {
    const container = document.getElementById('subscriptions-container');
    
    if (subscriptions.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fa fa-refresh"></i>
                <p>Aucun abonnement</p>
                <button class="btn btn-primary" onclick="openModal('subscription-modal')">
                    <i class="fa fa-plus"></i> Ajouter un abonnement
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = subscriptions.map(sub => `
        <div class="subscription-card">
            <div class="subscription-header">
                <div class="subscription-icon">
                    <i class="fa ${sub.icon || 'fa-refresh'}"></i>
                </div>
                <div class="subscription-info">
                    <div class="subscription-name">${sub.name}</div>
                    <div class="subscription-frequency">${translateFrequency(sub.frequency)}</div>
                </div>
                <span class="subscription-badge ${sub.is_active ? 'badge-active' : 'badge-inactive'}">
                    ${sub.is_active ? 'Actif' : 'Inactif'}
                </span>
            </div>
            
            <div class="subscription-amount">${formatAmount(sub.amount)}</div>
            
            <div class="subscription-details">
                <div class="subscription-detail">
                    <span class="subscription-detail-label">Prochaine échéance</span>
                    <span class="subscription-detail-value">${formatDate(sub.next_due_date)}</span>
                </div>
                <div class="subscription-detail">
                    <span class="subscription-detail-label">Compte</span>
                    <span class="subscription-detail-value">${sub.account_name}</span>
                </div>
            </div>
            
            <div class="subscription-actions">
                <button class="btn-convert" onclick="convertToTransaction(${sub.id})" title="Convertir en transaction">
                    <i class="fa fa-check"></i> Payer
                </button>
                <button class="btn-edit-sub" onclick="editSubscription(${sub.id})">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn-delete-sub" onclick="deleteSubscription(${sub.id})">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

async function loadUpcoming() {
    try {
        const response = await fetch('/api/subscriptions/upcoming?days=30');
        const data = await response.json();

        if (data.code === 200) {
            renderUpcoming(data.data);
            
            // Count upcoming in next 7 days
            const upcoming7days = data.data.filter(sub => {
                const dueDate = new Date(sub.next_due_date);
                const today = new Date();
                const diffTime = dueDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays >= 0 && diffDays <= 7;
            });
            document.getElementById('stat-upcoming').textContent = upcoming7days.length;
        }
    } catch (error) {
        console.error('Error loading upcoming:', error);
    }
}

function renderUpcoming(subscriptions) {
    const container = document.getElementById('upcoming-list');
    
    if (subscriptions.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa fa-calendar-check-o"></i>
                <p>Aucune échéance à venir</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = subscriptions.slice(0, 5).map(sub => {
        const date = new Date(sub.next_due_date);
        const day = date.getDate();
        const month = date.toLocaleDateString('fr-FR', { month: 'short' });
        
        return `
            <div class="upcoming-item">
                <div class="upcoming-date">
                    <div class="upcoming-date-day">${day}</div>
                    <div class="upcoming-date-month">${month}</div>
                </div>
                <div class="upcoming-info">
                    <div class="upcoming-name">${sub.name}</div>
                    <div class="upcoming-account">${sub.account_name}</div>
                </div>
                <div class="upcoming-amount">${formatAmount(sub.amount)}</div>
                <button class="upcoming-action" onclick="convertToTransaction(${sub.id})">
                    <i class="fa fa-check"></i> Payer
                </button>
            </div>
        `;
    }).join('');
}

async function loadStats() {
    try {
        const response = await fetch('/api/subscriptions');
        const data = await response.json();

        if (data.code === 200) {
            // Calculate monthly total
            let monthlyTotal = 0;
            data.data.filter(s => s.is_active).forEach(sub => {
                switch (sub.frequency) {
                    case 'daily': monthlyTotal += sub.amount * 30; break;
                    case 'weekly': monthlyTotal += sub.amount * 4.33; break;
                    case 'monthly': monthlyTotal += sub.amount; break;
                    case 'yearly': monthlyTotal += sub.amount / 12; break;
                }
            });
            
            document.getElementById('stat-monthly').textContent = formatAmount(monthlyTotal);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// View Toggle
function setView(view) {
    const listSection = document.getElementById('subscriptions-list');
    const calendarSection = document.getElementById('subscriptions-calendar');
    const listBtn = document.getElementById('view-list');
    const calendarBtn = document.getElementById('view-calendar');
    
    if (view === 'list') {
        listSection.style.display = 'block';
        calendarSection.style.display = 'none';
        listBtn.classList.add('active');
        calendarBtn.classList.remove('active');
    } else {
        listSection.style.display = 'none';
        calendarSection.style.display = 'block';
        listBtn.classList.remove('active');
        calendarBtn.classList.add('active');
        renderCalendar();
    }
}

// Calendar Functions
function navigateMonth(direction) {
    currentCalendarDate.setMonth(currentCalendarDate.getMonth() + direction);
    renderCalendar();
}

async function renderCalendar() {
    const year = currentCalendarDate.getFullYear();
    const month = currentCalendarDate.getMonth();
    
    // Update header
    const monthName = currentCalendarDate.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
    document.getElementById('calendar-month').textContent = monthName.charAt(0).toUpperCase() + monthName.slice(1);
    
    // Get calendar data
    try {
        const response = await fetch(`/api/subscriptions/calendar?year=${year}&month=${month + 1}`);
        const data = await response.json();
        
        if (data.code === 200) {
            renderCalendarGrid(year, month, data.data);
        }
    } catch (error) {
        console.error('Error loading calendar:', error);
        renderCalendarGrid(year, month, []);
    }
}

function renderCalendarGrid(year, month, events) {
    const grid = document.getElementById('calendar-grid');
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const today = new Date();
    
    // Get first day of week (0 = Sunday, adjust to Monday = 0)
    let startDay = firstDay.getDay() - 1;
    if (startDay < 0) startDay = 6;
    
    let html = '';
    
    // Previous month days
    const prevMonthLast = new Date(year, month, 0).getDate();
    for (let i = startDay - 1; i >= 0; i--) {
        html += `<div class="calendar-day other-month"><span class="day-number">${prevMonthLast - i}</span></div>`;
    }
    
    // Current month days
    for (let day = 1; day <= lastDay.getDate(); day++) {
        const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
        const dayEvents = events.filter(e => new Date(e.next_due_date).getDate() === day);
        
        html += `
            <div class="calendar-day ${isToday ? 'today' : ''}">
                <span class="day-number">${day}</span>
                <div class="day-events">
                    ${dayEvents.map(e => `
                        <div class="day-event" onclick="editSubscription(${e.id})" title="${e.name} - ${formatAmount(e.amount)}">
                            ${e.name}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Next month days
    const totalCells = Math.ceil((startDay + lastDay.getDate()) / 7) * 7;
    const remaining = totalCells - startDay - lastDay.getDate();
    for (let i = 1; i <= remaining; i++) {
        html += `<div class="calendar-day other-month"><span class="day-number">${i}</span></div>`;
    }
    
    grid.innerHTML = html;
}

// Modal Functions
function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = 'auto';
    
    if (id === 'subscription-modal') {
        document.getElementById('subscription-form').reset();
        document.getElementById('subscription-id').value = '';
        document.getElementById('subscription-modal-title').textContent = 'Nouvel abonnement';
        document.getElementById('form-next-due').value = new Date().toISOString().split('T')[0];
    }
}

async function editSubscription(id) {
    try {
        const response = await fetch(`/api/subscriptions/${id}`);
        const data = await response.json();

        if (data.code === 200) {
            const sub = data.data;
            const form = document.getElementById('subscription-form');
            
            document.getElementById('subscription-id').value = sub.id;
            document.getElementById('subscription-modal-title').textContent = 'Modifier l\'abonnement';
            
            form.querySelector('[name="name"]').value = sub.name;
            form.querySelector('[name="amount"]').value = sub.amount;
            form.querySelector('[name="frequency"]').value = sub.frequency;
            form.querySelector('[name="account_id"]').value = sub.account_id;
            form.querySelector('[name="category_id"]').value = sub.category_id || '';
            form.querySelector('[name="next_due_date"]').value = sub.next_due_date;
            form.querySelector('[name="is_active"]').value = sub.is_active ? '1' : '0';
            form.querySelector('[name="icon"]').value = sub.icon || '';
            
            openModal('subscription-modal');
        }
    } catch (error) {
        console.error('Error loading subscription:', error);
    }
}

async function handleSubscriptionSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    const id = document.getElementById('subscription-id').value;
    const isEdit = id !== '';
    
    try {
        const response = await fetch(isEdit ? `/api/subscriptions/${id}` : '/api/subscriptions', {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.code === 200 || result.code === 201) {
            closeModal('subscription-modal');
            loadSubscriptions();
            loadUpcoming();
            loadStats();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error saving subscription:', error);
        alert('Erreur réseau');
    }
}

async function deleteSubscription(id) {
    if (!confirm('Supprimer cet abonnement ?')) return;
    
    try {
        const response = await fetch(`/api/subscriptions/${id}`, { method: 'DELETE' });
        const result = await response.json();

        if (result.code === 200) {
            loadSubscriptions();
            loadUpcoming();
            loadStats();
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error deleting subscription:', error);
    }
}

async function convertToTransaction(id) {
    if (!confirm('Convertir cet abonnement en transaction ?')) return;
    
    try {
        const response = await fetch(`/api/subscriptions/${id}/convert`, { method: 'POST' });
        const result = await response.json();

        if (result.code === 200) {
            loadSubscriptions();
            loadUpcoming();
            alert('Transaction créée avec succès !');
        } else {
            alert(result.message || 'Erreur');
        }
    } catch (error) {
        console.error('Error converting subscription:', error);
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
