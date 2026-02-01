let allTechnologies = [];
let selectedTechnologies = [];

document.addEventListener('DOMContentLoaded', async () => {
    await loadTechnologies();
    initTechnoSelector();
});

// Callback appelé par app.js après création réussie
async function onProjectCreated(form, result) {
    if (result.code === 201) {
        form.reset();
        selectedTechnologies = [];
        updateSelectedDisplay();
        await handleTable(document.getElementById('projectList'));
    } else {
        alert('Erreur lors de la création du projet');
    }
}

// Callback appelé par app.js après mise à jour réussie
function onProjectUpdated(form, result) {
    if (result.code === 200) {
        window.location.href = '/dashboard/projects';
    } else {
        alert('Erreur lors de la modification');
    }
}

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
    const hiddenInput = document.getElementById('project-technologies');
    
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
    
    hiddenInput.value = JSON.stringify(selectedTechnologies);
}

function removeTechno(code) {
    selectedTechnologies = selectedTechnologies.filter(c => c !== code);
    updateSelectedDisplay();
    renderTechnoOptions(document.getElementById('techno-search').value);
}




function initImageUpload() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('project-images');
    
    // Drag and drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleImageUpload(files);
        }
    });
    
    // Click upload
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleImageUpload(e.target.files);
            e.target.value = '';
        }
    });
}

async function handleImageUpload(files) {
    for (const file of files) {
        if (!file.type.startsWith('image/')) continue;
        if (file.size > 5 * 1024 * 1024) {
            alert(`L'image ${file.name} est trop volumineuse (max 5MB)`);
            continue;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const response = await fetch(`/project/${projectId}/image`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.code !== 201) {
                alert(`Erreur upload ${file.name}: ${result.message}`);
            }
        } catch (error) {
            console.error('Error uploading:', error);
        }
    }
    
    // Recharger les images
    await loadProject();
}

function renderImages(images) {
    const gallery = document.getElementById('imageGallery');
    
    if (images.length === 0) {
        gallery.innerHTML = '<div class="empty-gallery">Aucune image pour ce projet</div>';
        return;
    }
    
    gallery.innerHTML = images.map(img => `
        <div class="image-item">
            <img src="${img.image_path}" alt="Project image">
            <button class="delete-image" onclick="deleteImage(${img.id})" title="Supprimer">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    `).join('');
}

async function deleteImage(imageId) {
    if (!confirm('Supprimer cette image ?')) return;
    
    try {
        const response = await fetch(`/project/image/${imageId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.code === 200) {
            await loadProject();
        } else {
            alert('Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function loadProject() {
    try {
        const response = await fetch(`/project/${projectId}`);
        const result = await response.json();
        
        if (result.code === 200 && result.data) {
            const project = result.data;
            
            document.getElementById('project-name').value = project.name;
            document.getElementById('project-description').value = project.description;
            document.getElementById('project-link').value = project.link || '';
            
            // Charger les technologies sélectionnées
            if (project.technologies) {
                selectedTechnologies = project.technologies.map(t => t.code);
                updateSelectedDisplay();
                renderTechnoOptions();
            }
            
            // Charger les images
            renderImages(project.images || []);
        }
    } catch (error) {
        console.error('Failed to load project:', error);
    }
}