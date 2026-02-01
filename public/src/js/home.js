/**
 * HOME PAGE - JavaScript
 * Animations et interactions pour la page d'accueil
 */

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initSmoothScroll();
    initTypingEffect();

    initCompetences();
    initDiplomas();
    initProjects();
});

/**
 * Navbar - Effet au scroll
 */
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        // Ajouter classe scrolled
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });
}

/**
 * Animations au scroll avec Intersection Observer
 */
function initScrollAnimations() {
    const elements = document.querySelectorAll('.fade-in');
    
    if (elements.length === 0) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    elements.forEach(element => {
        observer.observe(element);
    });
}

/**
 * Smooth scroll pour les ancres
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const navHeight = document.querySelector('.navbar')?.offsetHeight || 0;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Effet de typing pour le titre (optionnel)
 */
function initTypingEffect() {
    const typingElement = document.querySelector('.typing-effect');
    if (!typingElement) return;

    const words = ['Développeur Web', 'Créateur', 'Passionné'];
    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typeSpeed = 100;

    function type() {
        const currentWord = words[wordIndex];
        
        if (isDeleting) {
            typingElement.textContent = currentWord.substring(0, charIndex - 1);
            charIndex--;
            typeSpeed = 50;
        } else {
            typingElement.textContent = currentWord.substring(0, charIndex + 1);
            charIndex++;
            typeSpeed = 100;
        }

        if (!isDeleting && charIndex === currentWord.length) {
            isDeleting = true;
            typeSpeed = 2000; // Pause à la fin du mot
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
            typeSpeed = 500; // Pause avant le nouveau mot
        }

        setTimeout(type, typeSpeed);
    }

    type();
}

/**
 * Animation des compétences (progress bars)
 */
function initSkillBars() {
    const skillBars = document.querySelectorAll('.skill-progress');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bar = entry.target;
                const width = bar.getAttribute('data-width');
                bar.style.width = width + '%';
                observer.unobserve(bar);
            }
        });
    }, { threshold: 0.5 });

    skillBars.forEach(bar => {
        bar.style.width = '0';
        observer.observe(bar);
    });
}

async function initCompetences() 
{
    try {
        const response = await fetch('/competence/grouped');
        const result = await response.json();
        
        if (result.data && result.data.length > 0) {
            const grid = document.getElementById('skills-grid');
            grid.innerHTML = '';
            
            const icons = {
                'fa-desktop': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
                'fa-server': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
                'fa-cogs': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"></path><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path><path d="M2 2l7.586 7.586"></path><circle cx="11" cy="11" r="2"></circle></svg>'
            };
            
            result.data.forEach((category, index) => {
                const card = document.createElement('div');
                card.className = 'skill-card fade-in visible';
                card.style.animationDelay = `${index * 0.1}s`;
                
                const skillTags = category.skills.map(skill => 
                    `<span class="skill-tag ${skill.code}">${skill.libelle}</span>`
                ).join('');
                
                card.innerHTML = `
                    <div class="skill-icon">
                        ${icons[category.icon] || icons['fa-desktop']}
                    </div>
                    <h3>${category.name}</h3>
                    <p>${category.description || ''}</p>
                    <div class="skill-tags">
                        ${skillTags || '<span class="skill-tag">Aucune compétence</span>'}
                    </div>
                `;
                
                grid.appendChild(card);
            });
        }
    } catch (error) {
        console.error('Failed to load skills:', error);
    }
}

async function initDiplomas() 
{
    try {
        const response = await fetch('/diploma');
        const result = await response.json();
        
        if (result.data && result.data.length > 0) {
            const timeline = document.getElementById('education-timeline');
            timeline.innerHTML = '';
            
            result.data.forEach((diploma, index) => {
                const item = document.createElement('div');
                const side = index % 2 === 0 ? 'left' : 'right';
                item.className = `timeline-item timeline-${side} fade-in visible`;
                item.style.animationDelay = `${index * 0.15}s`;
                
                const years = diploma.end_at && diploma.end_at !== 'Act.' 
                    ? `${diploma.start_at} - ${diploma.end_at}`
                    : `${diploma.start_at} - En cours`;
                
                item.innerHTML = `
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h3>${diploma.name}</h3>
                            <span class="timeline-years">${years}</span>
                        </div>
                        <div class="timeline-school">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            ${diploma.school} • ${diploma.country}
                        </div>
                        <p class="timeline-description">${diploma.description}</p>
                    </div>
                `;
                
                timeline.appendChild(item);
            });
        }
    } catch (error) {
        console.error('Failed to load education:', error);
    }
}

/**
 * Chargement dynamique des projets
 */
async function initProjects() 
{
    try {
        const response = await fetch('/project/full');
        const result = await response.json();
        
        const grid = document.getElementById('projects-grid');
        if (!grid) return;
        
        grid.innerHTML = '';
        
        if (result.data && result.data.length > 0) {
            result.data.forEach((project, index) => {
                const card = document.createElement('article');
                card.className = 'project-card fade-in visible';
                card.style.animationDelay = `${index * 0.1}s`;
                
                // Générer les tags technologies
                const techTags = project.technologies.map(t => 
                    `<span>${t.libelle}</span>`
                ).join('');
                
                // Générer le lien avec l'icône appropriée
                let linkHtml = '';
                if (project.link) {
                    const linkInfo = getProjectLinkInfo(project.link);
                    linkHtml = `
                        <div class="project-links">
                            <a href="${escapeHtml(project.link)}" class="project-link" target="_blank" rel="noopener noreferrer">
                                ${linkInfo.icon}
                                ${linkInfo.label}
                            </a>
                        </div>
                    `;
                }
                
                // Image du projet ou placeholder
                const imageHtml = project.image 
                    ? `<img src="${project.image}" alt="${escapeHtml(project.name)}">`
                    : '<span class="project-placeholder"></span>';
                
                card.innerHTML = `
                    <div class="project-image">
                        ${imageHtml}
                    </div>
                    <div class="project-content">
                        <h3>${escapeHtml(project.name)}</h3>
                        <p>${escapeHtml(project.description)}</p>
                        <div class="project-tech">
                            ${techTags || '<span>Aucune technologie</span>'}
                        </div>
                        ${linkHtml}
                    </div>
                `;
                
                // Rendre la carte cliquable pour ouvrir la popup
                card.style.cursor = 'pointer';
                card.addEventListener('click', (e) => {
                    // Ne pas ouvrir la popup si on clique sur un lien
                    if (e.target.closest('a')) return;
                    openProjectPopup(project);
                });
                
                grid.appendChild(card);
            });
        } else {
            // Message si pas de projets
            grid.innerHTML = `
                <article class="project-card fade-in visible">
                    <div class="project-image">
                        <span class="project-placeholder"></span>
                    </div>
                    <div class="project-content">
                        <h3>Projets à venir</h3>
                        <p>De nouveaux projets passionnants sont en cours de développement...</p>
                        <div class="project-tech">
                            <span>Coming Soon</span>
                        </div>
                    </div>
                </article>
            `;
        }
    } catch (error) {
        console.error('Failed to load projects:', error);
    }
}

/**
 * Détecte le type de lien et retourne l'icône et le label appropriés
 */
function getProjectLinkInfo(url) {
    // GitHub
    if (url.includes('github.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>',
            label: 'GitHub'
        };
    }
    
    // GitLab
    if (url.includes('gitlab.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.65 14.39L12 22.13 1.35 14.39a.84.84 0 0 1-.3-.94l1.22-3.78 2.44-7.51A.42.42 0 0 1 4.82 2a.43.43 0 0 1 .58 0 .42.42 0 0 1 .11.18l2.44 7.49h8.1l2.44-7.51A.42.42 0 0 1 18.6 2a.43.43 0 0 1 .58 0 .42.42 0 0 1 .11.18l2.44 7.51L23 13.45a.84.84 0 0 1-.35.94z"></path></svg>',
            label: 'GitLab'
        };
    }
    
    // Bitbucket
    if (url.includes('bitbucket.org')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.778 1.213a.768.768 0 00-.768.892l3.263 19.81c.084.5.515.868 1.022.873H19.95a.772.772 0 00.77-.646l3.27-20.03a.768.768 0 00-.768-.891L.778 1.213zM14.52 15.53H9.522L8.17 8.466h7.561l-1.211 7.064z"/></svg>',
            label: 'Bitbucket'
        };
    }
    
    // CodePen
    if (url.includes('codepen.io')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"></polygon><line x1="12" y1="22" x2="12" y2="15.5"></line><polyline points="22 8.5 12 15.5 2 8.5"></polyline><polyline points="2 15.5 12 8.5 22 15.5"></polyline><line x1="12" y1="2" x2="12" y2="8.5"></line></svg>',
            label: 'CodePen'
        };
    }
    
    // StackBlitz
    if (url.includes('stackblitz.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10.797 14.182H3.635L16.728 0l-3.525 9.818h7.162L7.272 24l3.525-9.818z"/></svg>',
            label: 'StackBlitz'
        };
    }
    
    // Replit
    if (url.includes('replit.com') || url.includes('repl.it')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5h14v14H5z"/><path d="M12 5v14"/></svg>',
            label: 'Replit'
        };
    }
    
    // Heroku
    if (url.includes('heroku.com') || url.includes('herokuapp.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.61 0H3.39C2.62 0 2 .62 2 1.39v21.22c0 .77.62 1.39 1.39 1.39h17.22c.77 0 1.39-.62 1.39-1.39V1.39c0-.77-.62-1.39-1.39-1.39zM8 20H5V10h3v10zm0-12H5V4h3v4zm10 12h-3v-6l-3 3-3-3v6H6V4h3l3 4 3-4h3v16z"/></svg>',
            label: 'Heroku'
        };
    }
    
    // Vercel
    if (url.includes('vercel.app') || url.includes('vercel.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 22.525H0l12-21.05 12 21.05z"/></svg>',
            label: 'Vercel'
        };
    }
    
    // Netlify
    if (url.includes('netlify.app') || url.includes('netlify.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16.934 8.519a1.044 1.044 0 0 1 .303.23l2.349-1.045-2.192-2.171-.491 2.954zM12.06 6.546a1.305 1.305 0 0 1 .209.574l3.497 1.482a1.044 1.044 0 0 1 .355-.177l.574-3.55-2.13-2.234-2.505 3.852v.053zm11.933 5.491l-3.748-3.748-2.548 1.044 6.264 2.662s.053.011.032.042zm-.627.606l-6.013-2.569a1.044 1.044 0 0 1-.7.407l-.647 3.957a1.044 1.044 0 0 1 .303.553l3.382.502 3.675-2.85zM16.88 10.79a1.045 1.045 0 0 1-.491.512l.647 4.063a1.045 1.045 0 0 1 .303.209l3.852-2.986-4.311-1.798z"/></svg>',
            label: 'Netlify'
        };
    }
    
    // NPM
    if (url.includes('npmjs.com')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M0 7.334v8h6.666v1.332H12v-1.332h12v-8H0zm6.666 6.664H5.334v-4H3.999v4H1.335V8.667h5.331v5.331zm4 0v1.336H8.001V8.667h5.334v5.332h-2.669v-.001zm12.001 0h-1.33v-4h-1.336v4h-1.335v-4h-1.33v4h-2.671V8.667h8.002v5.331z"/></svg>',
            label: 'NPM'
        };
    }
    
    // YouTube
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        return {
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>',
            label: 'YouTube'
        };
    }
    
    // Icône par défaut pour les autres liens (lien externe)
    return {
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>',
        label: getHostname(url)
    };
}

/**
 * Extrait le hostname d'une URL
 */
function getHostname(url) {
    try {
        return new URL(url).hostname.replace('www.', '');
    } catch {
        return 'Voir le site';
    }
}

/**
 * Échappe les caractères HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Ouvre la popup avec les détails d'un projet
 */
function openProjectPopup(project) {
    // Titre
    document.getElementById('popup-project-title').textContent = project.name;
    
    // Description
    document.getElementById('popup-project-description').textContent = project.description;
    
    // Images (carrousel simple si plusieurs)
    const imagesContainer = document.getElementById('popup-project-images');
    if (project.images && project.images.length > 0) {
        imagesContainer.innerHTML = project.images.map(img => 
            `<img src="${img.image_path}" alt="${escapeHtml(project.name)}">`
        ).join('');
        imagesContainer.style.display = 'flex';
    } else if (project.image) {
        imagesContainer.innerHTML = `<img src="${project.image}" alt="${escapeHtml(project.name)}">`;
        imagesContainer.style.display = 'flex';
    } else {
        imagesContainer.innerHTML = '';
        imagesContainer.style.display = 'none';
    }
    
    // Technologies
    const techsContainer = document.getElementById('popup-project-techs');
    if (project.technologies && project.technologies.length > 0) {
        techsContainer.innerHTML = project.technologies.map(t => 
            `<span class="popup-tech-tag" style="border-left: 3px solid ${t.color || '#2563eb'}">${t.libelle}</span>`
        ).join('');
    } else {
        techsContainer.innerHTML = '';
    }
    
    // Lien
    const linkContainer = document.getElementById('popup-project-link');
    if (project.link) {
        const linkInfo = getProjectLinkInfo(project.link);
        linkContainer.innerHTML = `
            <a href="${escapeHtml(project.link)}" class="popup-project-btn" target="_blank" rel="noopener noreferrer">
                ${linkInfo.icon}
                ${linkInfo.label}
            </a>
        `;
    } else {
        linkContainer.innerHTML = '';
    }
    
    // Ouvrir la popup (utilise openPopup de app.js)
    openPopup('project-popup');
}