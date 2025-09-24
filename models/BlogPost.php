<?php

namespace Model;

class BlogPost {
    public $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function findAll($status = 'published', $limit = null) {
        $sql = "SELECT id, title, slug, excerpt, featured_image, created_at FROM blog_posts WHERE status = ? ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $this->db->prepare($sql);
        if ($limit) {
            $stmt->bind_param('si', $status, $limit);
        } else {
            $stmt->bind_param('s', $status);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findBySlug($slug) {
        $sql = "SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $sql = "SELECT * FROM blog_posts WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $sql = "INSERT INTO blog_posts (title, slug, excerpt, featured_image, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssss', 
            $data['title'], 
            $data['slug'], 
            $data['excerpt'], 
            $data['featured_image'], 
            $data['status']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE blog_posts SET title = ?, slug = ?, excerpt = ?, featured_image = ?, status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssssi', 
            $data['title'], 
            $data['slug'], 
            $data['excerpt'], 
            $data['featured_image'], 
            $data['status'], 
            $id
        );
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM blog_posts WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // MÉTODO CORREGIDO: generateSlug ahora acepta parámetro para excluir post actual
    public function generateSlug($title, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $this->ensureUniqueSlug($slug, 0, $excludeId);
    }

    // MÉTODO SOLUCIONADO: ensureUniqueSlug excluye el post actual en actualizaciones
    private function ensureUniqueSlug($slug, $count = 0, $excludeId = null) {
        $testSlug = $count > 0 ? $slug . '-' . $count : $slug;
        
        if ($excludeId) {
            // CASO 1: Actualización - excluir el post actual de la verificación
            $sql = "SELECT id FROM blog_posts WHERE slug = ? AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $testSlug, $excludeId);
        } else {
            // CASO 2: Nuevo post - verificar normalmente
            $sql = "SELECT id FROM blog_posts WHERE slug = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $testSlug);
        }
        
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            // El slug ya existe, probar con siguiente número
            return $this->ensureUniqueSlug($slug, $count + 1, $excludeId);
        }
        
        return $testSlug;
    }
}