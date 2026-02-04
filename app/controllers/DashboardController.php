<?php

#[CMiddleware(middleware: ['auth'])]
class DashboardController extends Controller
{
    #[CRoute('/', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('dashboard/index', [
            'title' => 'Finance Manager - Tableau de bord'
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