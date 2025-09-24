<?php

namespace Controllers;

use MVC\Router;
use Model\BlogPost;
use Model\BlogContent;
use Model\Admin;

class AdminBlogController {
    
    // Verificar si está autenticado
    private static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
    }
    
    // Obtener conexión a base de datos (hardcodeada)
    private static function getDbConnection() {
        $db = new \mysqli('localhost', 'root', '', 'blog');
        
        if ($db->connect_error) {
            throw new \Exception("Connection failed: " . $db->connect_error);
        }
        
        $db->set_charset('utf8mb4');
        return $db;
    }
    
    // Autenticar con usuario y contraseña desde BD
    public static function authenticate() {
        // IMPORTANTE: Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Asegurar que la respuesta sea JSON
        header('Content-Type: application/json');
        
        // Destruir cualquier sesión existente y crear una nueva limpia
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Usuario y contraseña son requeridos']);
            exit;
        }
        
        try {
            // Conexión hardcodeada como en BlogController
            $db = new \mysqli('localhost', 'root', '', 'blog');
            
            if ($db->connect_error) {
                echo json_encode(['success' => false, 'error' => 'Error de conexión']);
                exit;
            }
            
            $db->set_charset('utf8mb4');
            
            // Verificar que la tabla admins existe
            $result = $db->query("SHOW TABLES LIKE 'admins'");
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Tabla admins no existe']);
                exit;
            }
            
            $adminModel = new Admin($db);
            $admin = $adminModel->authenticate($username, $password);
            
            if ($admin) {
                $_SESSION['admin_authenticated'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
            }
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno']);
        }
        
        exit; // Importante: terminar la ejecución para evitar output adicional
    }
    
    // Cerrar sesión
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['admin_authenticated']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
        header('Location: /admin/blog');
        exit;
    }
    
    public static function index(Router $router) {
        // Verificar autenticación
        if (!self::checkAuth()) {
            $router->render('admin/blog/auth', [
                'title' => 'Admin - Acceso'
            ]);
            return;
        }
        
        try {
            $db = self::getDbConnection();
            $blogModel = new BlogPost($db);
            
            $posts = $blogModel->findAll('published');
            $drafts = $blogModel->findAll('draft');
            
            $router->render('admin/blog/index_admin', [
                'title' => 'Admin Blog',
                'posts' => $posts,
                'drafts' => $drafts,
                'admin_username' => $_SESSION['admin_username'] ?? 'Admin'
            ]);
            
        } catch (\Exception $e) {
            error_log("Admin index error: " . $e->getMessage());
            $router->render('admin/blog/error', [
                'title' => 'Error',
                'error' => 'Error de conexión a la base de datos'
            ]);
        }
    }
    
    public static function create(Router $router) {
        if (!self::checkAuth()) {
            header('Location: /admin/blog');
            exit;
        }
        
        $router->render('admin/blog/create', [
            'title' => 'Crear Post',
            'post' => null
        ]);
    }
    
    public static function edit(Router $router) {
        if (!self::checkAuth()) {
            header('Location: /admin/blog');
            exit;
        }
        
        $id = $_GET['id'] ?? 0;

        try {
            $db = self::getDbConnection();
            $blogModel = new BlogPost($db);
            $contentModel = new BlogContent($db);
            
            $post = $blogModel->findById($id);
            
            if (!$post) {
                header('Location: /admin/blog');
                exit;
            }
            
            $content_blocks = $contentModel->findByPostId($id);
            
            // Transform content blocks to the expected format
            $transformed_blocks = [];
            if (!empty($content_blocks)) {
                $rows = [];
                
                // Group by row
                foreach ($content_blocks as $block) {
                    $data = $block['content_data'];
                    if (isset($data['row'])) {
                        $rowIndex = $data['row'];
                        if (!isset($rows[$rowIndex])) {
                            $rows[$rowIndex] = [];
                        }
                        $rows[$rowIndex][] = [
                            'column' => $data['column'] ?? 0,
                            'content_data' => $data['content'] ?? $data
                        ];
                    }
                }
                
                // Convert to expected format
                foreach ($rows as $rowIndex => $columns) {
                    $transformed_blocks[] = [
                        'row' => $rowIndex,
                        'columns' => $columns
                    ];
                }
            }
            
            $router->render('admin/blog/create', [
                'title' => 'Editar Post',
                'post' => $post,
                'content_blocks' => $transformed_blocks
            ]);
            
        } catch (\Exception $e) {
            error_log("Edit post error: " . $e->getMessage());
            header('Location: /admin/blog');
            exit;
        }
    }
    
public static function store(Router $router) {
        if (!self::checkAuth()) {
            header('Location: /admin/blog');
            exit;
        }
        
        try {
            $db = self::getDbConnection();
            $blogModel = new BlogPost($db);
            $contentModel = new BlogContent($db);
            
            // CORRECCIÓN 1: Cambiar 'excerpt' por 'summary' para que coincida con el formulario
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'excerpt' => trim($_POST['summary'] ?? ''), // CORREGIDO: era 'excerpt'
                'status' => $_POST['status'] ?? 'draft',
                'featured_image' => null // Se maneja abajo
            ];
            
            // CORRECCIÓN 2: Manejar la imagen destacada correctamente
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $result = $contentModel->uploadImage($_FILES['featured_image']);
                if ($result['success']) {
                    $data['featured_image'] = $result['filename'];
                }
            } elseif (isset($_POST['current_featured_image']) && !empty($_POST['current_featured_image'])) {
                // Mantener la imagen actual si no se subió una nueva
                $data['featured_image'] = $_POST['current_featured_image'];
            }
            
            // CORRECCIÓN 3: Mejorar manejo de creación vs actualización
            $isEdit = isset($_POST['id']) && !empty($_POST['id']);
            $post_id = $isEdit ? (int)$_POST['id'] : null;
            
            // SOLUCIÓN DEL PROBLEMA: Generar slug considerando si es actualización
            if ($isEdit) {
                // Para actualizaciones: pasar el ID del post para excluirlo de la verificación
                $data['slug'] = $blogModel->generateSlug($data['title'], $post_id);
            } else {
                // Para posts nuevos: generar slug normalmente
                $data['slug'] = $blogModel->generateSlug($data['title']);
            }
            
            if ($isEdit) {
                $success = $blogModel->update($post_id, $data);
                
                if (!$success) {
                    error_log("Failed to update post with ID: " . $post_id);
                    throw new \Exception("Error al actualizar el post");
                }
            } else {
                $post_id = $blogModel->create($data);
                
                if (!$post_id) {
                    error_log("Failed to create new post with data: " . json_encode($data));
                    throw new \Exception("Error al crear el post");
                }
            }
            
            // Mejorar manejo de contenido de bloques
            if (isset($_POST['content_blocks']) && !empty($_POST['content_blocks']) && $post_id) {
                // Eliminar contenido existente solo si hay nuevos bloques
                $contentModel->deleteByPostId($post_id);
                
                $content_blocks = json_decode($_POST['content_blocks'], true);
                
                if (is_array($content_blocks) && !empty($content_blocks)) {
                    foreach ($content_blocks as $rowIndex => $rowData) {
                        if (isset($rowData['columns']) && is_array($rowData['columns'])) {
                            foreach ($rowData['columns'] as $columnData) {
                                // Obtener datos del contenido correctamente
                                $blockContent = $columnData['content'] ?? $columnData['content_data'] ?? $columnData;
                                
                                // Validar que el bloque tenga contenido
                                if (empty($blockContent['content']) && empty($blockContent['text']) && 
                                    empty($blockContent['url']) && empty($blockContent['src']) && 
                                    empty($blockContent['body'])) {
                                    continue; // Saltar bloques vacíos
                                }
                                
                                $blockData = [
                                    'row' => $rowIndex,
                                    'column' => $columnData['column'] ?? 0,
                                    'content' => $blockContent
                                ];
                                
                                $content_type = substr($blockContent['type'] ?? 'text', 0, 20);
                                
                                $content_id = $contentModel->create(
                                    $post_id,
                                    $content_type,
                                    $blockData,
                                    ($rowIndex * 100) + ($columnData['column'] ?? 0)
                                );
                                
                                if (!$content_id) {
                                    error_log("Failed to create content block for post ID: " . $post_id);
                                }
                            }
                        }
                    }
                }
            }
            
            // Redirigir siempre
            header('Location: /admin/blog');
            exit;
            
        } catch (\Exception $e) {
            error_log("Store post error: " . $e->getMessage());
            header('Location: /admin/blog');
            exit;
        }
    }
    
    public static function delete(Router $router) {
        if (!self::checkAuth()) {
            header('Location: /admin/blog');
            exit;
        }
        
        $id = $_POST['id'] ?? 0;

        try {
            $db = self::getDbConnection();
            $blogModel = new BlogPost($db);
            $contentModel = new BlogContent($db);
            
            $contentModel->deleteByPostId($id);
            $blogModel->delete($id);
            
        } catch (\Exception $e) {
            error_log("Delete post error: " . $e->getMessage());
        }
        
        header('Location: /admin/blog');
    }
    
    public static function uploadImage(Router $router) {
        header('Content-Type: application/json');
        
        if (!self::checkAuth()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
            try {
                $db = self::getDbConnection();
                $contentModel = new BlogContent($db);
                
                $result = $contentModel->uploadImage($_FILES['image']);
                
                echo json_encode($result);
                
            } catch (\Exception $e) {
                error_log("Upload image error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Error uploading image: ' . $e->getMessage()]);
            }
            
            exit;
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    public static function saveContent(Router $router) {
        header('Content-Type: application/json');
        
        if (!self::checkAuth()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = self::getDbConnection();
                $contentModel = new BlogContent($db);
                
                $post_id = $_POST['post_id'] ?? 0;
                $content_type = $_POST['content_type'] ?? '';
                $content_data = json_decode($_POST['content_data'], true) ?? [];
                
                if ($post_id && $content_type) {
                    $content_id = $contentModel->create($post_id, $content_type, $content_data);
                    
                    echo json_encode([
                        'success' => true,
                        'content_id' => $content_id
                    ]);
                    exit;
                }
                
            } catch (\Exception $e) {
                error_log("Save content error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    public static function updateContent(Router $router) {
        header('Content-Type: application/json');
        
        if (!self::checkAuth()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = self::getDbConnection();
                $contentModel = new BlogContent($db);
                
                $content_id = $_POST['content_id'] ?? 0;
                $content_data = json_decode($_POST['content_data'], true) ?? [];
                
                if ($content_id) {
                    $success = $contentModel->update($content_id, $content_data);
                    
                    echo json_encode(['success' => $success]);
                    exit;
                }
                
            } catch (\Exception $e) {
                error_log("Update content error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    public static function deleteContent(Router $router) {
        header('Content-Type: application/json');
        
        if (!self::checkAuth()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = self::getDbConnection();
                $contentModel = new BlogContent($db);
                
                $content_id = $_POST['content_id'] ?? 0;
                
                if ($content_id) {
                    $success = $contentModel->delete($content_id);
                    
                    echo json_encode(['success' => $success]);
                    exit;
                }
                
            } catch (\Exception $e) {
                error_log("Delete content error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    public static function reorderContent(Router $router) {
        header('Content-Type: application/json');
        
        if (!self::checkAuth()) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = self::getDbConnection();
                $contentModel = new BlogContent($db);
                
                $post_id = $_POST['post_id'] ?? 0;
                $content_ids = json_decode($_POST['content_ids'], true) ?? [];
                
                if ($post_id && !empty($content_ids)) {
                    $success = $contentModel->reorderContent($post_id, $content_ids);
                    
                    echo json_encode(['success' => $success]);
                    exit;
                }
                
            } catch (\Exception $e) {
                error_log("Reorder content error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
}