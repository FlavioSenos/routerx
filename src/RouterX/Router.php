<?php

namespace RouterX;

use RouterX\Request;
use RouterX\Response;
use RouterX\Route;
use RouterX\Middleware\MiddlewareInterface;
use Closure;

class Router
{
    private array $routes = [];
    private string $baseUri = '';
    private ?Closure $notFoundHandler = null;
    private ?object $templateEngine = null;

    public function __construct(string $baseUri = '')
    {
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function get(string $uri, $handler): Route
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): Route
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, $handler): Route
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function delete(string $uri, $handler): Route
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function match(array $methods, string $uri, $handler): Route
    {
        $route = new Route($methods, $this->baseUri . $uri, $handler);
        $this->routes[] = $route;
        return $route;
    }

    private function addRoute(string $method, string $uri, $handler): Route
    {
        $route = new Route([$method], $this->baseUri . $uri, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function group(string $prefix, callable $callback): self
    {
        $previousBaseUri = $this->baseUri;
        $this->baseUri .= rtrim($prefix, '/');
        $callback($this);
        $this->baseUri = $previousBaseUri;
        return $this;
    }

    public function setNotFoundHandler(Closure $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function setTemplateEngine(?object $engine): void
    {
        $this->templateEngine = $engine;
    }

    public function dispatch(): void
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        $requestMethod = $request->getMethod();
        $requestUri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route->matches($requestUri, $requestMethod)) {
                $params = $route->getUriParameters($requestUri);
                $request->setParameters($params);

                try {
                    $handler = $route->getHandler();
                    foreach ($route->getMiddlewares() as $middleware) {
                        $middlewareInstance = new $middleware();
                        $handler = function ($request, $response) use ($middlewareInstance, $handler) {
                            return $middlewareInstance->process($request, $response, $handler);
                        };
                    }

                    if (is_array($handler) && count($handler) === 2 && class_exists($handler[0])) {
                        $controllerClass = $handler[0];
                        $controllerMethod = $handler[1];
                        $controller = new $controllerClass($this->templateEngine);
                        $response = call_user_func_array([$controller, $controllerMethod], [$request, $response]);
                    } elseif ($handler instanceof Closure) {
                        $response = $handler($request, $response);
                    } else {
                        throw new \Exception("Handler de rota inválido.");
                    }

                    $response->send();
                    return;

                } catch (\Exception $e) {
                    $response->setStatusCode(500)->setContent("Erro interno: " . $e->getMessage())->send();
                    return;
                }
            }
        }

        if ($this->notFoundHandler) {
            ($this->notFoundHandler)($request, $response);
            $response->send();
            return;
        } else {
            $response->setStatusCode(404)->setContent("404 - Not Found")->send();
            return;
        }
    }
}
