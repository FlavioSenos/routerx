<?php

namespace RouterX;

class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $queryParams;
    private array $postData;
    private string $body;
    private array $uriParameters = [];

    private function __construct() {}

    public static function createFromGlobals(): self
    {
        $instance = new self();
        $instance->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $instance->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $instance->queryParams = $_GET;
        $instance->postData = $_POST;
        $instance->headers = getallheaders();
        $instance->body = file_get_contents('php://input');
        return $instance;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeader(string $name, $default = null): ?string
    {
        return $this->headers[$name] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getQueryParam(string $name, $default = null)
    {
        return $this->queryParams[$name] ?? $default;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getPost(string $name, $default = null)
    {
        return $this->postData[$name] ?? $default;
    }

    public function getPostData(): array
    {
        return $this->postData;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getUriParameter(string $name, $default = null)
    {
        return $this->uriParameters[$name] ?? $default;
    }

    public function setParameters(array $params): void
    {
        $this->uriParameters = $params;
    }
}