<?php

namespace Model;

class Admin {
    protected $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Autenticar usuario admin
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function authenticate($username, $password) {
        // Validar inputs
        if (empty($username) || empty($password)) {
            return false;
        }

        // Buscar usuario en la base de datos
        $sql = "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->db->error);
            return false;
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }

        $admin = $result->fetch_assoc();
        
        // Comparar contraseña directamente (texto plano)
        if ($password === $admin['password']) {
            // Retornar datos del admin (sin la contraseña)
            return [
                'id' => $admin['id'],
                'username' => $admin['username']
            ];
        }

        return false;
    }

    /**
     * Verificar si existe un admin por ID
     * @param int $admin_id
     * @return bool
     */
    public function exists($admin_id) {
        $sql = "SELECT id FROM admins WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }

    /**
     * Obtener admin por ID
     * @param int $admin_id
     * @return array|false
     */
    public function findById($admin_id) {
        $sql = "SELECT id, username, created_at FROM admins WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }

        return $result->fetch_assoc();
    }

    /**
     * Crear un nuevo admin (para uso manual)
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function create($username, $password) {
        $sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $username, $password);
        return $stmt->execute();
    }
}