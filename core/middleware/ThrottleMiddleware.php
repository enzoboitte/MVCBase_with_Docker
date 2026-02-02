<?php

/**
 * Middleware de limitation de taux (Rate Limiting)
 * Limite le nombre de requêtes par IP sur une période
 * Utilisation: #[CRoute('/api/login', middleware: ['throttle:10,60'])]
 * = 10 requêtes max par 60 secondes
 */
class ThrottleMiddleware extends Middleware
{
    private string $cacheDir;
    
    public function __construct()
    {
        $this->cacheDir = ROOT . '/storage/cache/throttle';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function handle(callable $next): mixed
    {
        $maxAttempts = (int)($this->params['middleware'][0] ?? 60);
        $decaySeconds = (int)($this->params['middleware'][1] ?? 60);
        
        $ip = $this->getClientIp();
        $key = md5($ip . $_SERVER['REQUEST_URI']);
        $cacheFile = $this->cacheDir . '/' . $key . '.json';
        
        $data = $this->loadCache($cacheFile);
        $now = time();
        
        // Nettoyer les anciennes entrées
        $data = array_filter($data, fn($timestamp) => $timestamp > ($now - $decaySeconds));
        
        if (count($data) >= $maxAttempts) {
            $retryAfter = max($data) - ($now - $decaySeconds);
            
            header("Retry-After: $retryAfter");
            header("X-RateLimit-Limit: $maxAttempts");
            header("X-RateLimit-Remaining: 0");
            
            $this->jsonError(429, "Trop de requêtes. Réessayez dans $retryAfter secondes.");
        }
        
        // Ajouter cette requête
        $data[] = $now;
        $this->saveCache($cacheFile, $data);
        
        // Headers informatifs
        header("X-RateLimit-Limit: $maxAttempts");
        header("X-RateLimit-Remaining: " . ($maxAttempts - count($data)));
        
        return $next();
    }
    
    private function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '0.0.0.0';
    }
    
    private function loadCache(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }
    
    private function saveCache(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data));
    }
}
