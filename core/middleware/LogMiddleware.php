<?php

/**
 * Middleware de logging
 * Log toutes les requêtes dans un fichier
 * Utilisation: #[CRoute('/api/user', middleware: ['log'])]
 */
class LogMiddleware extends Middleware
{
    private string $logDir;
    
    public function __construct()
    {
        $this->logDir = ROOT . '/storage/logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    public function handle(callable $next): mixed
    {
        $startTime = microtime(true);
        
        // Exécuter la requête
        $result = $next();
        
        // Log après exécution
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $this->logRequest($duration);
        
        return $result;
    }
    
    private function logRequest(float $duration): void
    {
        $logFile = $this->logDir . '/requests-' . date('Y-m-d') . '.log';
        
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? 'unknown';
        
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $_SESSION['user']['id'] ?? 'guest';
        
        $logEntry = sprintf(
            "[%s] %s %s %s - User: %s - Duration: %sms - IP: %s - UA: %s\n",
            date('Y-m-d H:i:s'),
            $method,
            $uri,
            http_response_code(),
            $userId,
            $duration,
            $ip,
            $userAgent
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
