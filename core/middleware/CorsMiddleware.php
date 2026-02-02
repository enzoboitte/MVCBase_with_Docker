<?php

/**
 * Middleware CORS
 * Ajoute les headers CORS pour les requêtes cross-origin
 */
class CorsMiddleware extends Middleware
{
    public function handle(callable $next): mixed
    {
        // Origines autorisées (à configurer selon vos besoins)
        $allowedOrigins = $this->params['middleware'] ?? ['*'];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $allowOrigin = in_array('*', $allowedOrigins) ? '*' : $origin;
            
            header("Access-Control-Allow-Origin: $allowOrigin");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Max-Age: 86400");
        }
        
        // Gérer les requêtes OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        
        return $next();
    }
}
