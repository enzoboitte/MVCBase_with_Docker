<?php

/**
 * Interface pour les middlewares
 */
interface MiddlewareInterface
{
    /**
     * Traite la requête
     * @param callable $next La fonction pour passer au middleware suivant
     * @return mixed
     */
    public function handle(callable $next): mixed;
    
    /**
     * Définir les paramètres du middleware
     * @param array $params Les paramètres (route, middleware)
     */
    public function setParams(array $params): void;
}

/**
 * Classe de base abstraite pour les middlewares
 */
abstract class Middleware implements MiddlewareInterface
{
    protected array $params = [];
    
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
    
    /**
     * Redirige vers une URL
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
    
    /**
     * Retourne une réponse JSON d'erreur
     */
    protected function jsonError(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['code' => $code, 'message' => $message]);
        exit;
    }
    
    /**
     * Arrête l'exécution avec un code HTTP
     */
    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        echo $message;
        exit;
    }
}

class Router
{
    private static array $routes = [];
    private static array $globalMiddleware = [];
    private static array $middlewareAliases = [];

    /**
     * Enregistrer un middleware global (exécuté sur toutes les routes)
     */
    public static function addGlobalMiddleware(string $middlewareClass): void
    {
        self::$globalMiddleware[] = $middlewareClass;
    }

    /**
     * Enregistrer un alias pour un middleware
     * Ex: Router::aliasMiddleware('auth', AuthMiddleware::class);
     */
    public static function aliasMiddleware(string $alias, string $middlewareClass): void
    {
        self::$middlewareAliases[$alias] = $middlewareClass;
    }

    /**
     * Résoudre un middleware (alias ou nom de classe)
     */
    private static function resolveMiddleware(string $middleware): string
    {
        return self::$middlewareAliases[$middleware] ?? $middleware;
    }

    /**
     * Enregistrer une route (méthode legacy)
     */
    public static function add(string $path, string $controller, string $action, CHTTPMethod $method = CHTTPMethod::GET): void
    {
        self::addRoute($path, $controller, $action, $method);
    }

    /**
     * Enregistrer une route avec méthode HTTP et middlewares
     */
    public static function addRoute(
        string $path, 
        string $controller, 
        string $action, 
        CHTTPMethod $method = CHTTPMethod::GET,
        array $middleware = []
    ): void
    {
        self::$routes[] = [
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'method' => $method,
            'middleware' => $middleware
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
                
                // Collecter tous les middlewares (globaux + route)
                $middlewares = array_merge(
                    self::$globalMiddleware,
                    $route['middleware'] ?? []
                );
                
                // Créer la chaîne de middlewares
                self::runMiddlewareChain($middlewares, $route, $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo "Page non trouvée";
    }

    /**
     * Exécuter la chaîne de middlewares puis le contrôleur
     */
    private static function runMiddlewareChain(array $middlewares, array $route, array $params): void
    {
        // Fonction finale qui exécute le contrôleur
        $controllerExecution = function() use ($route, $params) {
            self::execute($route, $params);
        };
        
        // Construire la chaîne de middlewares en partant de la fin
        $next = $controllerExecution;
        
        foreach (array_reverse($middlewares) as $middlewareDefinition) {
            $next = self::createMiddlewareCallback($middlewareDefinition, $next, $params);
        }
        
        // Exécuter la chaîne
        $next();
    }

    /**
     * Créer un callback pour un middleware
     */
    private static function createMiddlewareCallback(string $middlewareDefinition, callable $next, array $params): callable
    {
        return function() use ($middlewareDefinition, $next, $params) {
            // Parser le middleware (supporte "middleware:param1,param2")
            $parts = explode(':', $middlewareDefinition, 2);
            $middlewareName = $parts[0];
            $middlewareParams = isset($parts[1]) ? explode(',', $parts[1]) : [];
            
            // Résoudre l'alias si nécessaire
            $middlewareClass = self::resolveMiddleware($middlewareName);
            
            if (!class_exists($middlewareClass)) {
                throw new Exception("Middleware '$middlewareClass' non trouvé");
            }
            
            $middleware = new $middlewareClass();
            
            if (!$middleware instanceof MiddlewareInterface) {
                throw new Exception("'$middlewareClass' doit implémenter MiddlewareInterface");
            }
            
            // Passer les paramètres de route et de middleware
            $middleware->setParams([
                'route' => $params,
                'middleware' => $middlewareParams
            ]);
            
            return $middleware->handle($next);
        };
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

#[Attribute(Attribute::TARGET_CLASS)]
class CMiddleware
{
    public function __construct(
        public readonly array $middleware = [],
    ) {}
}

/**
 * Attribut pour exclure une méthode des middlewares de classe
 * Rend la route publique (sans auth)
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CPublic
{
    public function __construct() {}
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
        
        // Récupérer les middlewares de classe
        $classMiddleware = [];
        $classAttributes = $reflection->getAttributes(CMiddleware::class);
        if (!empty($classAttributes)) {
            $classMiddlewareAttr = $classAttributes[0]->newInstance();
            $classMiddleware = $classMiddlewareAttr->middleware;
        }
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(CRoute::class);
            
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                
                // Vérifier si la méthode est marquée comme publique
                $isPublic = !empty($method->getAttributes(CPublic::class));
                
                // Fusionner les middlewares (sauf si route publique)
                $middleware = $isPublic ? $route->middleware : array_merge($classMiddleware, $route->middleware);
                
                Router::addRoute(
                    $route->path,
                    $className,
                    $method->getName(),
                    $route->method,
                    $middleware
                );
            }
        }
    }
}
