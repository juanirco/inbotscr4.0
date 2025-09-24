<?php

namespace MVC;

class Router
{
    public array $getRoutes = [];
    public array $postRoutes = [];

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function routesVerify()
    {
        session_start();
        $inactivity_timeout = 3600;
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $inactivity_timeout)) {
            $_SESSION = [];
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    
        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?') ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];
    
        if ($method === 'GET') {
            $fn = $this->matchRoute($currentUrl, $this->getRoutes);
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }
    
        if ($fn) {
            call_user_func($fn, $this);
        } else {
            $this->show404();
        }
    }

    private function matchRoute($currentUrl, $routes) {
        // Exact match first
        if (isset($routes[$currentUrl])) {
            return $routes[$currentUrl];
        }

        // Check for blog post pattern /blog/{slug}
        if (preg_match('/^\/blog\/([^\/]+)$/', $currentUrl, $matches)) {
            $_GET['slug'] = $matches[1];
            return $routes['/blog/post'] ?? null;
        }

        return null;
    }

    private function show404() {
        // Enviar header HTTP 404
        http_response_code(404);
        
        // Intentar mostrar página 404 personalizada
        if (file_exists(__DIR__ . '/views/errors/404.php')) {
            $this->render('errors/404', [
                'title' => 'Página no encontrada'
            ]);
        } else {
            // Fallback si no existe la vista de error
            echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #666; }
    </style>
</head>
<body>
    <h1>404 - Página no encontrada</h1>
    <p>La página que buscas no existe.</p>
    <a href="/">Volver al inicio</a>
</body>
</html>';
        }
    }

    public function render($view, $datos = [])
    {
        foreach ($datos as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include_once __DIR__ . "/views/$view.php";
        $content = ob_get_clean();
        include_once __DIR__ . '/views/layout.php';
    }
}