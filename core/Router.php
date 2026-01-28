<?php

class Router
{
    private static array $routes = [];

    /**
     * Enregistrer une route (méthode legacy)
     */
    public static function add(string $path, string $controller, string $action, CHTTPMethod $method = CHTTPMethod::GET): void
    {
        self::addRoute($path, $controller, $action, $method);
    }

    /**
     * Enregistrer une route avec méthode HTTP
     */
    public static function addRoute(string $path, string $controller, string $action, CHTTPMethod $method = CHTTPMethod::GET): void
    {
        self::$routes[] = [
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'method' => $method
        ];
    }

    /**
     * Récupérer l'URI courante
     */
    private static function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        return $uri === '' ? '/' : $uri;
    }

    /**
     * Récupérer la méthode HTTP courante
     */
    private static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Dispatcher la requête vers le bon contrôleur
     */
    public static function dispatch(): void
    {
        $uri = self::getUri();
        $httpMethod = self::getMethod();

        foreach (self::$routes as $route) {
            $pattern = self::convertToRegex($route['path']);
            
            if (preg_match($pattern, $uri, $matches) && $route['method']->value === $httpMethod) {
                array_shift($matches); // Retirer le match complet
                self::execute($route, $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo "Page non trouvée";
    }

    /**
     * Convertir une route en regex
     * Exemple: /user/{id} devient /user/([^/]+)
     */
    private static function convertToRegex(string $route): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Exécuter le contrôleur
     */
    private static function execute(array $target, array $params): void
    {
        $controllerName = $target['controller'];
        $action = $target['action'];

        if (!class_exists($controllerName)) {
            throw new Exception("Controller '$controllerName' non trouvé");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            throw new Exception("Action '$action' non trouvée dans '$controllerName'");
        }

        call_user_func_array([$controller, $action], $params);
    }
}

enum CHTTPMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}

#[Attribute(Attribute::TARGET_METHOD)]
class CRoute
{
    public function __construct(
        public readonly string $path,
        public readonly CHTTPMethod $method = CHTTPMethod::GET,
        public readonly array $middleware = [],
    ) {}
}

class RouteScanner
{
    /**
     * Scanner tous les contrôleurs pour trouver les routes via attributs
     */
    public static function scan(string $controllersPath): void
    {
        $files = glob($controllersPath . '/*Controller.php');
        
        foreach ($files as $file) {
            $className = basename($file, '.php');
            
            if (!class_exists($className)) {
                require_once $file;
            }
            
            if (class_exists($className)) {
                self::scanController($className);
            }
        }
    }

    /**
     * Scanner un contrôleur pour ses attributs CRoute
     */
    private static function scanController(string $className): void
    {
        $reflection = new ReflectionClass($className);
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(CRoute::class);
            
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                Router::addRoute(
                    $route->path,
                    $className,
                    $method->getName(),
                    $route->method
                );
            }
        }
    }
}
