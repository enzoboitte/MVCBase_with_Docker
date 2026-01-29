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
}