/**
 * EXPERIENCE DASHBOARD - JavaScript
 */

let allTechnologies = [];
let selectedTechnologies = [];
let allContractTypes = [];

document.addEventListener('DOMContentLoaded', async () => {
    await loadContractTypes();
    await loadTechnologies();
    initTechnoSelector();
    initTasksManager();
    
    // Si on est sur la page d'édition
    if (typeof experienceId !== 'undefined') {
        await loadExperience();
    } else {
        // Ajouter la première ligne de tâche sur la page d'ajout
        addTaskInput();
    }
    
    // Intercepter le submit pour ajouter les données personnalisées
    const form = document.getElementById('experienceForm') || document.getElementById('editExperienceForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            // Mettre à jour les champs cachés avant l'envoi
            document.getElementById('exp-technologies').value = JSON.stringify(selectedTechnologies);
            document.getElementById('exp-tasks').value = JSON.stringify(collectTasks());
        });
    }
});

// Callback après création
async function onExperienceCreated(form, result) {
    if (result.code === 201) {
        form.reset();
        selectedTechnologies = [];
        updateSelectedDisplay();
        clearTasks();
        addTaskInput();
        await handleTable(document.getElementById('experienceList'));
    } else {
        alert('Erreur lors de la création de l\'expérience');
    }
}

// Callback après mise à jour
function onExperienceUpdated(form, result) {
    if (result.code === 200) {
        window.location.href = '/dashboard/experiences';
    } else {
        alert('Erreur lors de la modification');
    }
}

// ========================
// CONTRACT TYPES
// ========================
async function loadContractTypes() {
    try {
        const response = await fetch('/contract-types');
        const result = await response.json();
        if (result.data) {
            allContractTypes = result.data;
            const select = document.getElementById('exp-contract');
            result.data.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.contract_type;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load contract types:', error);
    }
}

// ========================
// TECHNOLOGIES
// ========================
async function loadTechnologies() {
    try {
        const response = await fetch('/techno');
        const result = await response.json();
        if (result.data) {
            allTechnologies = result.data;
            renderTechnoOptions();
        }
    } catch (error) {
        console.error('Failed to load technologies:', error);
    }
}

function initTechnoSelector() {
    const selected = document.getElementById('techno-selected');
    const dropdown = document.getElementById('techno-dropdown');
    const search = document.getElementById('techno-search');
    
    if (!selected || !dropdown) return;
    
    selected.addEventListener('click', (e) => {
        if (e.target.classList.contains('techno-tag-remove')) return;
        dropdown.classList.toggle('open');
        if (dropdown.classList.contains('open')) {
            search.focus();
        }
    });
    
    search.addEventListener('input', (e) => {
        renderTechnoOptions(e.target.value);
    });
    
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.techno-multiselect')) {
            dropdown.classList.remove('open');
        }
    });
}

function renderTechnoOptions(filter = '') {
    const container = document.getElementById('techno-options');
    if (!container) return;
    
    const filtered = allTechnologies.filter(t => 
        t.libelle.toLowerCase().includes(filter.toLowerCase())
    );
    
    container.innerHTML = filtered.map(tech => `
        <div class="techno-option ${selectedTechnologies.includes(tech.code) ? 'selected' : ''}" 
             data-code="${tech.code}" 
             style="border-left: 3px solid ${tech.color};">
            <span>${tech.libelle}</span>
            ${selectedTechnologies.includes(tech.code) ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' : ''}
        </div>
    `).join('');
    
    container.querySelectorAll('.techno-option').forEach(opt => {
        opt.addEventListener('click', () => toggleTechno(opt.dataset.code));
    });
}

function toggleTechno(code) {
    const index = selectedTechnologies.indexOf(code);
    if (index > -1) {
        selectedTechnologies.splice(index, 1);
    } else {
        selectedTechnologies.push(code);
    }
    updateSelectedDisplay();
    renderTechnoOptions(document.getElementById('techno-search').value);
}

function updateSelectedDisplay() {
    const container = document.getElementById('techno-selected');
    const hiddenInput = document.getElementById('exp-technologies');
    
    if (!container) return;
    
    if (selectedTechnologies.length === 0) {
        container.innerHTML = '<span class="techno-placeholder">Sélectionner des technologies...</span>';
    } else {
        container.innerHTML = selectedTechnologies.map(code => {
            const tech = allTechnologies.find(t => t.code === code);
            return `<span class="techno-tag" style="border-left: 3px solid ${tech?.color || '#ccc'};">
                ${tech?.libelle || code}
                <button type="button" class="techno-tag-remove" onclick="removeTechno('${code}')">&times;</button>
            </span>`;
        }).join('');
    }
    
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(selectedTechnologies);
    }
}

function removeTechno(code) {
    selectedTechnologies = selectedTechnologies.filter(c => c !== code);
    updateSelectedDisplay();
    renderTechnoOptions(document.getElementById('techno-search').value);
}

// ========================
// TASKS MANAGEMENT
// ========================
function initTasksManager() {
    const container = document.getElementById('tasks-container');
    if (!container) return;
    
    // Délégation d'événements pour les boutons de suppression
    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-task')) {
            e.target.closest('.task-input-row').remove();
            updateTasksHidden();
        }
    });
    
    // Mettre à jour le champ caché à chaque modification
    container.addEventListener('input', () => {
        updateTasksHidden();
    });
}

function addTaskInput(value = '') {
    const container = document.getElementById('tasks-container');
    if (!container) return;
    
    const row = document.createElement('div');
    row.className = 'task-input-row';
    row.innerHTML = `
        <input type="text" class="task-input" placeholder="Décrivez une mission..." value="${escapeHtml(value)}">
        <button type="button" class="btn-add-task" onclick="addTaskInput()">+</button>
        <button type="button" class="btn-remove-task">×</button>
    `;
    container.appendChild(row);
    
    // Focus sur le nouveau champ
    row.querySelector('.task-input').focus();
}

function collectTasks() {
    const inputs = document.querySelectorAll('.task-input');
    const tasks = [];
    inputs.forEach(input => {
        const value = input.value.trim();
        if (value) {
            tasks.push(value);
        }
    });
    return tasks;
}

function updateTasksHidden() {
    const hiddenInput = document.getElementById('exp-tasks');
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(collectTasks());
    }
}

function clearTasks() {
    const container = document.getElementById('tasks-container');
    if (container) {
        container.innerHTML = '';
    }
}

// ========================
// LOAD EXPERIENCE (EDIT)
// ========================
async function loadExperience() {
    try {
        const response = await fetch(`/experience/${experienceId}`);
        const result = await response.json();
        
        if (result.code === 200 && result.data) {
            const exp = result.data;
            
            // Remplir les champs
            document.getElementById('exp-title').value = exp.title;
            document.getElementById('exp-company').value = exp.company;
            document.getElementById('exp-location').value = exp.location;
            document.getElementById('exp-contract').value = exp.contract_type_id;
            document.getElementById('exp-start').value = exp.start_date;
            document.getElementById('exp-end').value = exp.end_date || '';
            document.getElementById('exp-description').value = exp.description;
            
            // Charger les technologies sélectionnées
            if (exp.technologies) {
                selectedTechnologies = exp.technologies.map(t => t.code);
                updateSelectedDisplay();
                renderTechnoOptions();
            }
            
            // Charger les tâches
            clearTasks();
            if (exp.tasks && exp.tasks.length > 0) {
                exp.tasks.forEach(task => {
                    addTaskInput(task.task_description);
                });
            } else {
                addTaskInput();
            }
        }
    } catch (error) {
        console.error('Failed to load experience:', error);
    }
}

// ========================
// UTILS
// ========================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
