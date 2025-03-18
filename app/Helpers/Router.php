<?php

namespace App\Helpers;

class Router {
    private static $routes = [];
    
    public static function get($path, $callback) {
        self::$routes['GET'][$path] = $callback;
    }
    
    public static function post($path, $callback) {
        self::$routes['POST'][$path] = $callback;
    }
    
    public static function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash if not root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        // First check for exact match
        if (isset(self::$routes[$method][$uri])) {
            $callback = self::$routes[$method][$uri];
            
            if (is_callable($callback)) {
                return call_user_func($callback);
            }
            
            if (is_array($callback)) {
                list($controller, $action) = $callback;
                $controllerInstance = new $controller();
                return $controllerInstance->$action();
            }
        }
        
        // If no exact match, check for routes with parameters
        foreach (self::$routes[$method] as $route => $callback) {
            // Skip routes without parameters
            if (strpos($route, '{') === false) {
                continue;
            }
            
            // Convert route pattern to regex
            $pattern = preg_replace('/{([a-zA-Z0-9_]+)}/', '([^/]+)', $route);
            $pattern = "#^$pattern$#";
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                
                if (is_callable($callback)) {
                    return call_user_func_array($callback, $matches);
                }
                
                if (is_array($callback)) {
                    list($controller, $action) = $callback;
                    $controllerInstance = new $controller();
                    return call_user_func_array([$controllerInstance, $action], $matches);
                }
            }
        }
        
        // Route not found
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
} 