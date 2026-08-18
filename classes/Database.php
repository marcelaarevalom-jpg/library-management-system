<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'biblioteca';
    private $username = 'root';
    private $password = 'Marcela1!';
    public $conn;

    // Método para obtener la conexión a la base de datos
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        return $this->conn;
    }
}