<?php

/**
 * Middleware de vérification de rôle
 * Utilisation: #[CRoute('/admin', middleware: ['role:admin'])]
 */
class RoleMiddleware extends Middleware
{
    public function handle(callable $next): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $requiredRole = $this->params['middleware'][0] ?? null;
        
        if (!$requiredRole) {
            throw new Exception("RoleMiddleware nécessite un rôle en paramètre");
        }
        
        $userRole = $_SESSION['user']['role'] ?? null;
        
        if ($userRole !== $requiredRole) {
            if ($this->isApiRequest()) {
                $this->jsonError(403, 'Accès interdit');
            }
            $this->abort(403, 'Accès interdit');
        }
        
        return $next();
    }
    
    private function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }
}
