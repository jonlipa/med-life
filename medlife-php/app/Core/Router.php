<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function __construct(private Request $request)
    {
    }

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->routes['GET'][$path] = compact('handler', 'middleware');
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->routes['POST'][$path] = compact('handler', 'middleware');
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $path = $this->request->path();
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            Response::abort(404, 'shared/not_found', ['title' => 'Faqja nuk u gjet']);
            return;
        }

        if ($this->shouldBlockForSetup($method, $route['middleware'])) {
            $message = $method === 'GET'
                ? 'Kjo zone e portalit kerkon databazen aktive perpara se te vazhdoni.'
                : 'Ky veprim nuk mund te ekzekutohet pa databazen aktive. Aktivizoni MySQL dhe provoni perseri.';
            Response::setupRequired($message);
        }

        if ($method === 'POST' && !Csrf::validate((string) $this->request->input('_token'))) {
            Session::flash('flash', ['type' => 'danger', 'message' => 'CSRF token eshte i pavlefshme. Provoni perseri.']);
            Response::redirect($path);
        }

        $this->runMiddleware($route['middleware']);
        $this->invoke($route['handler']);
    }

    private function runMiddleware(array $stack): void
    {
        /** @var Auth $auth */
        $auth = App::get('auth');

        foreach ($stack as $middleware) {
            if ($middleware === 'guest' && $auth->check()) {
                Response::redirect(role_home($auth->role() ?? 'patient'));
            }

            if ($middleware === 'auth' && !$auth->check()) {
                Session::flash('flash', ['type' => 'warning', 'message' => 'Hyrni ne sistem per te vazhduar.']);
                Response::redirect('/login');
            }

            if (str_starts_with($middleware, 'role:')) {
                $requiredRole = substr($middleware, 5);
                if (!$auth->check()) {
                    Session::flash('flash', ['type' => 'warning', 'message' => 'Sesioni juaj ka perfunduar.']);
                    Response::redirect('/login');
                }

                if ($auth->role() !== $requiredRole) {
                    Session::flash('flash', ['type' => 'danger', 'message' => 'Nuk keni akses ne kete zone.']);
                    Response::redirect(role_home($auth->role() ?? 'patient'));
                }
            }
        }
    }

    private function invoke(callable $handler): void
    {
        if (is_array($handler)) {
            $reflection = new \ReflectionMethod($handler[0], $handler[1]);
            if ($reflection->getNumberOfParameters() === 0) {
                $handler();
                return;
            }

            $handler($this->request);
            return;
        }

        $reflection = new \ReflectionFunction(\Closure::fromCallable($handler));
        if ($reflection->getNumberOfParameters() === 0) {
            $handler();
            return;
        }

        $handler($this->request);
    }

    private function shouldBlockForSetup(string $method, array $middleware): bool
    {
        if (db_available()) {
            return false;
        }

        if ($method !== 'GET') {
            return true;
        }

        foreach ($middleware as $item) {
            if ($item === 'auth' || str_starts_with($item, 'role:')) {
                return true;
            }
        }

        return false;
    }
}
