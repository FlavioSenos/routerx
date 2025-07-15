<?php

namespace RouterX;

use RouterX\Middleware\MiddlewareInterface;

class Route
{
    private array $methods;
    private string $uri;
    private $handler;
    private array $middlewares = [];
    private array $uriParams = [];

    public function __construct(array $methods, string $uri, $handler)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->uri = $this->parseUri($uri);
        $this->handler = $handler;
    }

    public function addMiddleware(string $middlewareClassName): self
    {
        if (!class_exists($middlewareClassName) || !in_array(MiddlewareInterface::class, class_implements($middlewareClassName))) {
            throw new \InvalidArgumentException("Middleware '$middlewareClassName' inválido. Deve implementar " . MiddlewareInterface::class);
        }
        $this->middlewares[] = $middlewareClassName;
        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function matches(string $requestUri, string $requestMethod): bool
    {
        if (!in_array(strtoupper($requestMethod), $this->methods)) {
            return false;
        }

        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $this->uri);
        $pattern = '#^' . $pattern . '$#';

        return (bool) preg_match($pattern, $requestUri);
    }

    public function getUriParameters(string $requestUri): array
    {
        $params = [];
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $this->uri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            array_shift($matches);
            foreach ($this->uriParams as $index => $paramName) {
                if (isset($matches[$index])) {
                    $params[$paramName] = $matches[$index];
                }
            }
        }
        return $params;
    }

    private function parseUri(string $uri): string
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $uri, $matches);
        $this->uriParams = $matches[1];
        return $uri;
    }
}