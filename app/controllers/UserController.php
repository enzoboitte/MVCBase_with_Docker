<?php

class UserController extends Controller
{
    #[CRoute('/connection', CHTTPMethod::GET)]
    public function connection(): void
    {
        $this->view('user/connection', [
            'back' => '/',
            'title' => 'Connexion'
        ]);
    }
    
}
