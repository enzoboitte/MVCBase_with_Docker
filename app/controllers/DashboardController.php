<?php

#[CMiddleware(middleware: ['auth'])]
class DashboardController extends Controller
{
    #[CRoute('/', CHTTPMethod::GET)]
    #[CPublic]
    public function index(): void
    {
        $this->view('dashboard/index', [
            'title' => 'Portfolio - BOITTE Enzo'
        ]);
    }


    #[CRoute('/dashboard/diploma', CHTTPMethod::GET)]
    public function diploma(): void
    {
        $this->view('dashboard/diploma', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Diplômes'
        ]);
    }

    #[CRoute('/dashboard/diploma/edit/{id}', CHTTPMethod::GET)]
    public function editDiploma(string $id): void
    {
        $this->view('dashboard/edit_diploma', [
            'back' => '/dashboard/diploma',
            'title' => 'Tableau de bord - Modifier Diplôme',
            'diplomaId' => $id
        ]);
    }


    #[CRoute('/dashboard/contact', CHTTPMethod::GET)]
    public function contact(): void
    {
        $this->view('dashboard/contact', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Messages de contact'
        ]);
    }


    #[CRoute('/dashboard/technologies', CHTTPMethod::GET)]
    public function technologies(): void
    {
        $this->view('dashboard/techno', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Technologies'
        ]);
    }

    #[CRoute('/dashboard/technologies/edit/{code}', CHTTPMethod::GET)]
    public function editTechno(string $code): void
    {
        $this->view('dashboard/edit_techno', [
            'back' => '/dashboard/technologies',
            'title' => 'Tableau de bord - Modifier Technologie',
            'technoCode' => $code
        ]);
    }


    #[CRoute('/dashboard/competences', CHTTPMethod::GET)]
    public function competences(): void
    {
        $this->view('dashboard/competence', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Compétences'
        ]);
    }

    #[CRoute('/dashboard/competences/edit/{id}', CHTTPMethod::GET)]
    public function editCompetence(string $id): void
    {
        $this->view('dashboard/edit_competence', [
            'back' => '/dashboard/competences',
            'title' => 'Tableau de bord - Modifier Compétence',
            'competenceId' => $id
        ]);
    }


    #[CRoute('/dashboard/projects', CHTTPMethod::GET)]
    public function projects(): void
    {
        $this->view('dashboard/project', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Projets'
        ]);
    }

    #[CRoute('/dashboard/projects/edit/{id}', CHTTPMethod::GET)]
    public function editProject(string $id): void
    {
        $this->view('dashboard/edit_project', [
            'back' => '/dashboard/projects',
            'title' => 'Tableau de bord - Modifier Projet',
            'projectId' => $id
        ]);
    }


    #[CRoute('/dashboard/experiences', CHTTPMethod::GET)]
    public function experiences(): void
    {
        $this->view('dashboard/experience', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Expériences'
        ]);
    }

    #[CRoute('/dashboard/experiences/edit/{id}', CHTTPMethod::GET)]
    public function editExperience(string $id): void
    {
        $this->view('dashboard/edit_experience', [
            'back' => '/dashboard/experiences',
            'title' => 'Tableau de bord - Modifier Expérience',
            'experienceId' => $id
        ]);
    }

    #[CRoute('/dashboard/users', CHTTPMethod::GET)]
    public function users(): void
    {
        $this->view('dashboard/user', [
            'back' => '/dashboard',
            'title' => 'Tableau de bord - Utilisateurs'
        ]);
    }

    #[CRoute('/dashboard/users/edit/{id}', CHTTPMethod::GET)]
    public function editUser(string $id): void
    {
        $this->view('dashboard/edit_user', [
            'back' => '/dashboard/users',
            'title' => 'Tableau de bord - Modifier Utilisateur',
            'userId' => $id
        ]);
    }
}