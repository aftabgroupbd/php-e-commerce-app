<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A very small router.
 *
 * Routes are declared in routes/web.php like this:
 *
 *     $router->get('/products/{id}', [ProductController::class, 'show']);
 *
 * The router matches the current request, runs the route middleware and
 * then calls the controller method. That is the whole "front controller"
 * pattern: every request enters through public/index.php.
 */
final class Router
{
    /** @var array<int, array{method:string, regex:string, handler:array{0:class-string,1:string}, middleware:array<int,class-string>}> */
    private array $routes = [];

    /**
     * @param array{0:class-string, 1:string} $handler [ControllerClass::class, 'method']
     * @param array<int, class-string>        $middleware
     */
    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /**
     * @param array{0:class-string, 1:string} $handler
     * @param array<int, class-string>        $middleware
     */
    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /**
     * @param array{0:class-string, 1:string} $handler
     * @param array<int, class-string>        $middleware
     */
    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        // "/products/{id}" becomes "#^/products/(?P<id>[^/]+)$#"
        $regex = '#^' . preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path) . '$#';

        $this->routes[] = [
            'method'     => $method,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Find the matching route and execute it.
     *
     * @throws HttpException when no route matches
     */
    public function dispatch(Request $request, Container $container): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (!preg_match($route['regex'], $request->path(), $matches)) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                $middleware::handle();
            }

            $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            [$class, $method] = $route['handler'];
            $controller = $container->make($class);

            $controller->{$method}($request, ...array_values($parameters));

            return;
        }

        throw new HttpException(404);
    }
}
