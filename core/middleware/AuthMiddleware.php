<?php

/**
 * Middleware d'authentification
 * Vérifie si l'utilisateur est connecté
 */
class AuthMiddleware extends Middleware
{
    public function handle(callable $next): mixed
    {
        // Démarrer la session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            // Pour les requêtes API, retourner une erreur JSON
            if ($this->isApiRequest()) {
                $this->jsonError(401, 'Non authentifié');
            }
            
            // Pour les pages web, rediriger vers la connexion
            $this->redirect('/login');
        }
        
        // Utilisateur connecté, continuer
        return $next();
    }
    
    /**
     * Vérifie si c'est une requête API (attend du JSON)
     */
    private function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        return str_contains($accept, 'application/json') 
            || str_contains($contentType, 'application/json')
            || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api');
    }
}
