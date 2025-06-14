<?php

namespace App\Core;

class Router
{
    protected $routes = [];
    protected $namespace = 'App\\Controllers\\';

    public function add($method, $route, $controller, $action)
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'route'      => $route,
            'controller' => $controller,
            'action'     => $action
        ];
        return $this;
    }

    public function get($route, $controller, $action) { return $this->add('GET', $route, $controller, $action); }
    public function post($route, $controller, $action) { return $this->add('POST', $route, $controller, $action); }
    public function put($route, $controller, $action) { return $this->add('PUT', $route, $controller, $action); }
    public function delete($route, $controller, $action) { return $this->add('DELETE', $route, $controller, $action); }
    public function any($route, $controller, $action) { return $this->add('ANY', $route, $controller, $action); }

    protected function getUri()
    {
        return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    }

    public function dispatch()
    {


        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\\{([a-zA-Z_][a-zA-Z0-9_]*)\\}/', '(?P<$1>[^/]+)', $route['route']);
            $pattern = '#^' . trim($pattern, '/') . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $controllerName = $this->namespace . $route['controller'];
                $action = $route['action'];

                if (class_exists($controllerName)) {
                    try {
                        $request = new Request();
                        $response = new Response();

                        $reflectionClass = new \ReflectionClass($controllerName);
                        $constructor = $reflectionClass->getConstructor();
                        $dependencies = [];

                        if ($constructor) {
                            foreach ($constructor->getParameters() as $param) {
                                $paramType = $param->getType();
                                if ($paramType && !$paramType->isBuiltin()) {
                                    $className = $paramType->getName();
                                    switch ($className) {
                                        case self::class:
                                            $dependencies[] = $this;
                                            break;
                                        case Session::class:
                                            $dependencies[] = Session::getInstance();
                                            break;
                                        case Request::class:
                                            $dependencies[] = $request;
                                            break;
                                        case Response::class:
                                            $dependencies[] = $response;
                                            break;
                                        default:
                                            if (class_exists($className)) {
                                                $dependencies[] = new $className();
                                            } else {
                                                $dependencies[] = null;
                                            }
                                            break;
                                    }
                                } elseif ($param->isDefaultValueAvailable()) {
                                    $dependencies[] = $param->getDefaultValue();
                                } else {
                                    $dependencies[] = null;
                                }
                            }
                        }

                        $controllerInstance = $reflectionClass->newInstanceArgs($dependencies);

                        if (method_exists($controllerInstance, $action)) {
                            call_user_func_array([$controllerInstance, $action], array_merge([$request, $response], $params));
                            return;
                        }
                    } catch (\ReflectionException $e) {
                        error_log('Router DI Error: ' . $e->getMessage());
                    }
                }
            }
        }

        $this->notFound();
    }

    protected function notFound()
    {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
        exit();
    }
}

