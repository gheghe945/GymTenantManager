<?php
/**
 * Router class for handling URI routing
 */
class Router {
    /**
     * Collection of registered routes
     *
     * @var array
     */
    private $routes = [];

    /**
     * Register a new route
     *
     * @param string $route Route URI pattern
     * @param string $controller Controller and method (ControllerName@methodName)
     * @return void
     */
    public function register($route, $controller) {
        $this->routes[$route] = $controller;
    }

    /**
     * Dispatch the request to the appropriate controller
     *
     * @param string $uri Current URI
     * @return void
     */
    public function dispatch($uri) {
        // Check if user is not logged in and trying to access protected page
        if (!isLoggedIn() && $uri !== 'login' && $uri !== '') {
            redirect('login');
        }
        
        $found = false;
        
        // Try to match exact routes first
        if (array_key_exists($uri, $this->routes)) {
            $this->executeController($this->routes[$uri]);
            $found = true;
        } else {
            // Try to match routes with parameters
            foreach ($this->routes as $route => $controller) {
                if ($route !== '') {
                    $pattern = '#^' . $route . '$#';
                    if (preg_match($pattern, $uri, $matches)) {
                        // Remove the first match (the full match)
                        array_shift($matches);
                        $this->executeController($controller, $matches);
                        $found = true;
                        break;
                    }
                }
            }
        }
        
        // If no route matches, show 404
        if (!$found) {
            http_response_code(404);
            echo "404 - Page not found";
        }
    }

    /**
     * Execute the controller with parameters
     *
     * @param string $controller Controller and method string
     * @param array $params Parameters to pass to the method
     * @return void
     */
    private function executeController($controller, $params = []) {
        list($controllerName, $method) = explode('@', $controller);
        
        $controllerInstance = new $controllerName();
        
        // Check if the controller has required middleware
        if (method_exists($controllerInstance, 'getMiddleware')) {
            $middlewares = $controllerInstance->getMiddleware();
            
            foreach ($middlewares as $middleware => $actions) {
                if (in_array($method, $actions) || $actions[0] === '*') {
                    $middlewareInstance = new $middleware();
                    $middlewareInstance->handle();
                }
            }
        }
        
        call_user_func_array([$controllerInstance, $method], $params);
    }
}
