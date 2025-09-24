<?php

namespace Controllers;

use MVC\Router;
use Model\BlogPost;
use Model\BlogContent;

class BlogController {
    
    public static function index(Router $router) {
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $blogModel = new BlogPost($db);
        
        $posts = $blogModel->findAll('published');
        
        // CORREGIDO: Usar WebP si existe, sino original
        foreach ($posts as &$post) {
            if ($post['featured_image']) {
                $post['featured_image'] = BlogContent::getBestImageUrl($post['featured_image']);
            }
        }
        
        $router->render('blog/index_blog', [
            'title' => 'Blog',
            'description' => 'Descubre las últimas tendencias en smartbots, inteligencia artificial y automatización empresarial.',
            'posts' => $posts
        ]);
    }
    
    public static function show(Router $router) {
        $slug = $_GET['slug'] ?? '';
        
        if (empty($slug)) {
            header('Location: /blog');
            exit;
        }
        
        $db = new \mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        $blogModel = new BlogPost($db);
        $contentModel = new BlogContent($db);
        
        $post = $blogModel->findBySlug($slug);
        
        if (!$post) {
            header('HTTP/1.0 404 Not Found');
            $router->render('errors/404', [
                'title' => 'Post no encontrado'
            ]);
            return;
        }
        
        // CORREGIDO: Usar WebP si existe, sino original
        if ($post['featured_image']) {
            $post['featured_image'] = BlogContent::getBestImageUrl($post['featured_image']);
        }
        
        $content_blocks = $contentModel->findByPostId($post['id']);
        $post['content'] = $contentModel->renderContent($content_blocks);
        
        $router->render('blog/post', [
            'title' => $post['title'],
            'description' => $post['excerpt'],
            'post' => $post
        ]);
    }
}