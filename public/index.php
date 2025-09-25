<?php 

require_once __DIR__ . '/../includes/app.php';

use Controllers\PagesController;
use Controllers\BlogController;
use Controllers\AdminBlogController;
use MVC\Router;

$router = new Router();

// Public pages
$router->get('/', [PagesController::class, 'inicio']);
$router->get('/nosotros', [PagesController::class, 'nosotros']);
$router->get('/smartbots', [PagesController::class, 'smartbots']);
$router->get('/contacto', [PagesController::class, 'contacto']);
$router->get('/privacidad', [PagesController::class, 'privacidad']);
$router->get('/condiciones', [PagesController::class, 'condiciones']);

// Blog público
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/post', [BlogController::class, 'show']);

// NUEVO: Rutas de autenticación admin
// Rutas de autenticación admin
$router->get('/admin/auth', [AdminBlogController::class, 'authenticate']); // AGREGAR ESTA
$router->post('/admin/auth', [AdminBlogController::class, 'authenticate']);
$router->get('/admin/logout', [AdminBlogController::class, 'logout']);

// Rutas del blog admin (ya protegidas en el controller)
$router->get('/admin/blog', [AdminBlogController::class, 'index']);
$router->get('/admin/blog/create', [AdminBlogController::class, 'create']);
$router->get('/admin/blog/edit', [AdminBlogController::class, 'edit']);
$router->post('/admin/blog/store', [AdminBlogController::class, 'store']);
$router->post('/admin/blog/delete', [AdminBlogController::class, 'delete']);

// Rutas AJAX para contenido (ya protegidas en el controller)
$router->post('/admin/blog/upload-image', [AdminBlogController::class, 'uploadImage']);
$router->post('/admin/blog/save-content', [AdminBlogController::class, 'saveContent']);
$router->post('/admin/blog/update-content', [AdminBlogController::class, 'updateContent']);
$router->post('/admin/blog/delete-content', [AdminBlogController::class, 'deleteContent']);
$router->post('/admin/blog/reorder-content', [AdminBlogController::class, 'reorderContent']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->routesVerify();