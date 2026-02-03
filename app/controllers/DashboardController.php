<?php

class DashboardController extends Controller
{
    /**
     * Dashboard principal - Vue d'ensemble financière
     */
    #[CRoute('/dashboard', CHTTPMethod::GET, middleware: ['auth'])]
    public function index(): void
    {
        $this->view('finance/dashboard', [
            'title' => 'Tableau de bord',
            'customCss' => [
                '/public/src/css/finance/dashboard.css'
            ],
            'customJs' => '/public/src/js/finance/dashboard.js'
        ]);
    }
}
