<?php

class DashboardController extends Controller
{
    #[CRoute('/dashboard', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('dashboard/index', [
            'back' => '/',
            'title' => 'Tableau de bord'
        ]);
    }

    #[CRoute('/dashboard/diploma/edit/{id}', CHTTPMethod::GET)]
    public function editDiploma(string $id): void
    {
        $this->view('dashboard/edit_diploma', [
            'back' => '/dashboard',
            'title' => 'Modifier Diplôme',
            'diplomaId' => $id
        ]);
    }
}