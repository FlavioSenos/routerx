# RouterX: Um Router PHP Simples, Flexível e Poderoso

RouterX é uma biblioteca de roteamento PHP leve e fácil de usar, projetada para gerenciar as rotas da sua aplicação de forma amigável, com suporte a middlewares, integração com templates e capacidade de atender a APIs RESTful. Ela oferece um controle granular sobre como suas URLs são mapeadas para a lógica da sua aplicação.

## Por Que RouterX?

Em um mundo de frameworks complexos, RouterX se destaca pela sua simplicidade e foco. Ela oferece os recursos essenciais para um roteamento eficiente sem a sobrecarga de um framework completo, permitindo que você construa sua aplicação com a arquitetura que desejar.

## Funcionalidades Atuais

* **Rotas Amigáveis:** Defina URLs limpas e descritivas (ex: `/produtos/detalhes/{id}`).

* **Suporte a Middlewares:** Intercepte requisições e respostas para adicionar lógica pré- e pós-controlador (autenticação, logging, CORS, etc.).

* **Integração com Template Engines:** Permite que você passe **qualquer motor de templates** para seus controladores e renderize facilmente suas views.

* **Pronta para APIs:** Retorne respostas JSON de forma simples, ideal para construir APIs RESTful.

* **Parâmetros de URI:** Capture slugs e IDs diretamente da URL.

* **Query Parameters:** Acesse facilmente parâmetros de consulta, incluindo identificadores de campanha (UTM).

* **Grupos de Rotas:** Organize suas rotas com prefixos de URI e middlewares compartilhados.

## Instalação

RouterX é uma biblioteca de roteamento PHP leve e fácil de usar, projetada para gerenciar as rotas da sua aplicação de forma amigável, com suporte a middlewares, integração com templates e capacidade de atender a APIs RESTful. Ela oferece um controle granular sobre como suas URLs são mapeadas para a lógica da sua aplicação.

~~~bash
composer require flaviosenos/routerx
~~~

## Como Usar

### 1. Inicialização e Configuração Básica

Crie sua instância do Router e, opcionalmente, configure seu motor de templates (passando-o para o `Router` e depois para os seus controladores).

~~~php
<?php

require_once __DIR__ . '/vendor/autoload.php'; // Inclui o autoloader do Composer

use RouterX\Router;
use RouterX\Request;
use RouterX\Response;

// Exemplo de configuração de um motor de templates (pode ser Twig, Blade, Smarty, etc.)
// Isso é apenas um exemplo de como você passaria um engine, não é obrigatório para o RouterX funcionar.
// class MyCustomTemplateEngine { public function render(string $template, array #data = []): string { /* ... */ } }
// $myTemplateEngine = new MyCustomTemplateEngine();

// Instancia o Router
$router = new Router();
// Opcional: Se você usa um motor de templates, pode passá-lo para o Router
// $router->setTemplateEngine($myTemplateEngine);
~~~

### 2. Definindo Rotas

Você pode definir rotas para diferentes métodos HTTP e com parâmetros na URI.

~~~php
// Rota GET simples
$router->get('/', function (Request $request, Response $response) {
    return $response->setContent("Bem-vindo à Home da RouterX!");
});

// Rota com parâmetro de URI (ID ou Slug)
$router->get('/blog/{slug}', [App\Controllers\BlogController::class, 'showPost']);

// Rota para API (POST)
$router->post('/api/pedidos', [App\Controllers\OrderController::class, 'createOrder']);
~~~

### 3. Usando Grupos de Rotas

Agrupe rotas com prefixos e middlewares compartilhados.

~~~php
$router->group('/admin', function (Router $router) {
    // Rotas dentro de /admin, por exemplo: /admin/dashboard
    $router->get('/dashboard', [App\Controllers\AdminController::class, 'dashboard']);

    // Todas as rotas neste grupo podem usar o AuthMiddleware
    $router->get('/users', [App\Controllers\AdminController::class, 'listUsers'])
           ->addMiddleware(App\Middlewares\AuthMiddleware::class);
});
~~~

### 4. Middlewares

Crie middlewares implementando a `RouterX\Middleware\MiddlewareInterface`.

~~~php
<?php

namespace App\Middlewares;

use RouterX\Middleware\MiddlewareInterface;
use RouterX\Request;
use RouterX\Response;
use Closure;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Response $response, Closure $next): Response
    {
        // Exemplo: Verificar um token de autenticação
        $token = $request->getHeader('Authorization');
        if (empty($token) || $token !== 'Bearer meu_token_secreto') {
            return $response->json(['error' => 'Não autorizado'], 401);
        }

        // Chama o próximo middleware ou o controlador
        return $next($request, $response);
    }
}
~~~

Aplique middlewares às rotas:


~~~php
=======
>>>>>>> 0ca87ee (Commit inicial)
$router->post('/api/produtos', [App\Controllers\ProductController::class, 'store'])
       ->addMiddleware(App\Middlewares\AuthMiddleware::class)
       ->addMiddleware(App\Middlewares\LogMiddleware::class);
~~~

5. Controladores e Interagindo com Request/Response
Seus controladores recebem objetos Request e Response. Se você passou um motor de templates para o Router, ele será injetado no construtor do seu controlador.


~~~php
=======
```php
>>>>>>> 0ca87ee (Commit inicial)
<?php

namespace App\Controllers;

use RouterX\Request;
use RouterX\Response;

// Note que não há dependência específica de um template engine aqui,
// apenas um tipo genérico 'object' ou 'mixed'.
class ProductController
{
    private ?object $templateEngine;

    public function __construct(?object $templateEngine = null)
    {
        $this->templateEngine = $templateEngine;
    }

    public function show(Request $request, Response $response): Response
    {
        // Obtém um parâmetro da URI (ex: /produtos/{id})
        $productId = $request->getUriParameter('id');

        // Obtém um parâmetro de query string (ex: /produtos?sort=price)
        $sortBy = $request->getQueryParam('sort', 'name');

        // Renderiza conteúdo usando o template engine (se disponível)
        if (isset($this->templateEngine)) { // Use isset para verificar se o templateEngine foi injetado
            // Exemplo de uso (adapte para o seu motor de templates)
            // return $response->setContent($this->templateEngine->render('product_detail.html', ['id' => $productId, 'sortBy' => $sortBy]));
            return $response->setContent("Renderizando produto $productId com ordenação por $sortBy usando um template engine.");
        }

        // Retorna JSON para API
        return $response->json(['id' => $productId, 'sortBy' => $sortBy, 'message' => 'Detalhes do produto']);
    }

    public function listFilteredProducts(Request $request, Response $response): Response
    {
        // Capturando múltiplos slugs para filtros (ex: /produtos/eletrodomesticos/eletrolux)
        $categorySlug = $request->getUriParameter('categorySlug');
        $brandSlug = $request->getUriParameter('brandSlug'); // Pode ser null se não informado

        // ... lógica de filtragem de produtos ...

        return $response->json([
            'category' => $categorySlug,
            'brand' => $brandSlug,
            'products' => [] // array de produtos filtrados
        ]);
    }
}
~~~

6. Despachando a Requisição
No seu ponto de entrada (public/index.php), chame dispatch().

~~~php
=======
```php
>>>>>>> 0ca87ee (Commit inicial)
// ... (rotas definidas) ...

// Define um handler para 404 (rota não encontrada)
$router->setNotFoundHandler(function (Request $request, Response $response) {
    $response->setStatusCode(404)->setContent("<h1>Página Não Encontrada</h1>")->send();
});

// Inicia o roteamento
$router->dispatch();
~~~


Melhorias Futuras (Roadmap)
RouterX está em constante evolução! Consideramos as seguintes melhorias para o futuro:

Nomear Rotas e Geração de URLs: Permitir que as rotas sejam nomeadas para gerar URLs de forma programática (ex: Router::url('product.show', ['id' => 123])), garantindo que os links permaneçam consistentes mesmo se a estrutura da URI mudar.

Asserções de Parâmetros de Rota: Adicionar suporte para validação de tipos de parâmetros na URI (ex: /users/{id:\d+} para garantir que id seja um número).

Cache de Rotas: Implementar um mecanismo de cache para compilar as rotas em produção, otimizando o desempenho e evitando o reprocessamento a cada requisição.

Integração com PSR-7/PSR-15: Adotar os padrões PSR-7 (HTTP Messages) e PSR-15 (HTTP Handlers) para as classes Request e Response, promovendo maior interoperabilidade com outras bibliotecas e middlewares do ecossistema PHP.

Tratamento de Exceções Avançado: Oferecer um sistema mais robusto para capturar e tratar diferentes tipos de exceções (ex: 405 Method Not Allowed, 403 Forbidden) com handlers específicos.

Suporte a Invokable Controllers: Permitir que classes com o método __invoke() sejam usadas como handlers de rota.

Testes Automatizados: Expandir a cobertura de testes para garantir a estabilidade e confiabilidade da biblioteca em todas as suas funcionalidades.


## Contribuição
Contribuições são bem-vindas! Sinta-se à vontade para abrir [issues](https://github.com/flaviosenos/routerx/issues) ou [pull requests](https://github.com/flaviosenos/routerx/pulls) em nosso repositório no GitHub.


## Licença
Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](https://github.com/flaviosenos/routerx/blob/main/LICENSE) para mais detalhes.
