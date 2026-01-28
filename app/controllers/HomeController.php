<?php

class HomeController extends Controller
{
    #[CRoute('/', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'Portfolio - BOITTE Enzo'
        ]);
    }

    #[CRoute('/about', CHTTPMethod::GET)]
    public function about(): void
    {
        $this->view('home/about', [
            'title' => 'À propos'
        ]);
    }
}
