<?php

namespace App\Core;

class Router {
    protected $routes = [];
    protected $params = [];
    protected $namespace = 'App\\Controllers\\';

    public function add($method, $route, $controller, $action) {
        $routeData = [
            'method' => strtoupper($method),
            'route' => $route,
            'controller' => $controller,
            'action' => $action
        ];
        
        $this->routes[] = $routeData;
        
        // Log the registered route with backtrace for debugging
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? 
            ($backtrace[1]['class'] ?? '') . ($backtrace[1]['type'] ?? '') . ($backtrace[1]['function'] ?? '') : 
            'global';
            
        error_log(sprintf(
            "[ROUTER] Registered route: %-6s %-30s => %s@%s (called from: %s)",
            $routeData['method'],
            $routeData['route'],
            $routeData['controller'],
            $routeData['action'],
            $caller
        ));
        
        return $this;
    }

    public function get($route, $controller, $action) {
        $this->add('GET', $route, $controller, $action);
    }

    public function post($route, $controller, $action) {
        $this->add('POST', $route, $controller, $action);
    }

    public function put($route, $controller, $action) {
        $this->add('PUT', $route, $controller, $action);
    }

    public function delete($route, $controller, $action) {
        $this->add('DELETE', $route, $controller, $action);
    }

    public function any($route, $controller, $action) {
        // This will match any HTTP method
        $this->add('ANY', $route, $controller, $action);
    }

    public function dispatch() {
        // Log request details
        error_log("\n" . str_repeat('=', 80));
        error_log("= NEW REQUEST: " . date('Y-m-d H:i:s'));
        error_log("= " . str_repeat('-', 78) . " =");
        error_log("= Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("= URI: " . $_SERVER['REQUEST_URI']);
        error_log("= Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'none'));
        error_log("= PHP Self: " . ($_SERVER['PHP_SELF'] ?? 'none'));
        error_log("= Query: " . ($_SERVER['QUERY_STRING'] ?? 'none'));
        error_log("= Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        error_log("= POST Data: " . json_encode($_POST));
        error_log("= Session ID: " . (session_id() ?: 'none'));
        error_log("= Session Data: " . (isset($_SESSION) ? json_encode($_SESSION) : 'not started'));
        error_log("" . str_repeat('=', 80));
        
        // Log all server variables for debugging
        error_log("=== SERVER VARIABLES ===");
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0 || in_array($key, ['REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF', 'SCRIPT_FILENAME'])) {
                error_log("$key: " . (is_string($value) ? $value : json_encode($value)));
            }
        }
        
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Check for method override for PUT/DELETE from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
            error_log("[ROUTER] Method overridden to: $method");
        }

        error_log("[ROUTER] Dispatching: $method $uri");
        error_log("[ROUTER] Available routes (" . count($this->routes) . "):");
        
        // Find matching route
        foreach ($this->routes as $i => $route) {
            $matches = $this->match($route['route'], $uri);
            $methodMatches = ($route['method'] === 'ANY' || $route['method'] === $method);
            
            $matchInfo = sprintf(
                '  %s %s => %s@%s',
                str_pad($route['method'], 6),
                str_pad($route['route'], 30),
                $route['controller'],
                $route['action']
            );
            
            if ($matches !== false && $methodMatches) {
                error_log("[ROUTER] ✓ MATCH: $matchInfo");
                
                // Found matching route, execute it
                $controller = $this->namespace . $route['controller'];
                $action = $route['action'];
                
                if (class_exists($controller)) {
                    $controllerInstance = new $controller();
                    
                    if (method_exists($controllerInstance, $action)) {
                        // Call the controller method with any route parameters
                        call_user_func_array([$controllerInstance, $action], $matches);
                        return;
                    } else {
                        error_log("[ROUTER] Error: Method $action not found in controller $controller");
                    }
                } else {
                    error_log("[ROUTER] Error: Controller $controller not found");
                }
            } else {
                $reason = [];
                if ($matches === false) $reason[] = 'path';
                if (!$methodMatches) $reason[] = 'method';
                error_log("[ROUTER]   NO MATCH (" . implode(', ', $reason) . "): $matchInfo");
            }
        }
        
        // No matching route found
        $this->notFound();
    }

    protected function match($route, $uri) {
        // Normalize paths
        $route = '/' . trim($route, '/');
        $uri = '/' . trim($uri, '/');
        
        error_log("[ROUTER] Matching route: '$route' against URI: '$uri'");
        
        // Exact match - return empty array of parameters
        if ($route === $uri) {
            error_log("[ROUTER] Exact match found");
            return [];
        }

        // Check if the route has parameters
        if (strpos($route, '{') !== false && strpos($route, '}') !== false) {
            // Split the route into segments
            $routeSegments = explode('/', $route);
            $uriSegments = explode('/', $uri);
            
            // If segment count doesn't match, this route can't match
            if (count($routeSegments) !== count($uriSegments)) {
                return false;
            }
            
            // Extract parameters
            $params = [];
            for ($i = 0; $i < count($routeSegments); $i++) {
                $routeSegment = $routeSegments[$i];
                $uriSegment = $uriSegments[$i];
                
                // Check if this segment is a parameter
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $routeSegment, $matches)) {
                    // This is a parameter segment, extract the parameter name
                    $paramName = $matches[1];
                    $params[$paramName] = $uriSegment;
                } else if ($routeSegment !== $uriSegment) {
                    // This is a static segment and doesn't match
                    return false;
                }
            }
            
            error_log("[ROUTER] Route matched with parameters: " . print_r($params, true));
            
            // Store parameters for later use
            $this->params = array_merge($this->params, $params);
            
            // Return only the parameter values for the controller method
            return array_values($params);
        }
        
        return false;
    }

    protected function getUri() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        error_log("[ROUTER] getUri() returned: '" . $uri . "'");
        return $uri;
    }

    protected function notFound() {
        http_response_code(404);
        
        // Log detailed information about the failed request
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        $availableRoutes = array_map(
            fn($r) => $r['method'] . ' ' . $r['route'], 
            $this->routes
        );
        
        error_log("404 - Route not found: $method $uri");
        error_log("Available routes: " . print_r($availableRoutes, true));
        
        // Check if the controller exists but method doesn't
        $matchedRoute = null;
        foreach ($this->routes as $route) {
            if ($route['route'] === $uri) {
                $matchedRoute = $route;
                break;
            }
        }
        
        if ($matchedRoute) {
            $controller = $this->namespace . $matchedRoute['controller'];
            $action = $matchedRoute['action'];
            
            if (!class_exists($controller)) {
                error_log("Controller not found: $controller");
                echo '404 - Controller not found: ' . $controller;
            } elseif (!method_exists($controller, $action)) {
                error_log("Method $action not found in controller $controller");
                echo '404 - Method not found: ' . $action . ' in ' . $controller;
            } else {
                echo '404 - Route not found';
            }
        } else {
            echo '404 - Route not found';
        }
        
        exit;
    }
}
