<?php

class UserController extends Controller
{
    #[CRoute('/user/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $this->json([
            'userId' => $id,
            'name' => 'Utilisateur ' . $id
        ]);
}

    #[CRoute('/user/{id}/edit', CHTTPMethod::GET)]
    public function edit(string $id): void
    {
        $this->view('user/edit', [
            'title' => 'Modifier utilisateur',
            'userId' => $id
        ]);
    }
}
