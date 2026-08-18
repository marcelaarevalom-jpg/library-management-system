<?php
require_once 'Database.php';
require_once 'Libro.php';
require_once 'Usuario.php';
require_once 'Prestamo.php';

class Biblioteca {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Gestión de Libros
    public function agregarLibro(Libro $libro) {
        $sql = "INSERT INTO libros (titulo, autor, isbn, cantidad) VALUES (:titulo, :autor, :isbn, :cantidad)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':titulo', $libro->getTitulo());
        $stmt->bindValue(':autor', $libro->getAutor());
        $stmt->bindValue(':isbn', $libro->getIsbn());
        $stmt->bindValue(':cantidad', $libro->getCantidad());
        return $stmt->execute();
    }

    public function editarLibro($id, $nuevosDatos) {
        $sql = "UPDATE libros SET titulo = :titulo, autor = :autor, isbn = :isbn, cantidad = :cantidad WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':titulo', $nuevosDatos['titulo']);
        $stmt->bindValue(':autor', $nuevosDatos['autor']);
        $stmt->bindValue(':isbn', $nuevosDatos['isbn']);
        $stmt->bindValue(':cantidad', $nuevosDatos['cantidad']);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function eliminarLibro($id) {
        $sql = "DELETE FROM libros WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function obtenerLibros() {
        $sql = "SELECT * FROM libros ORDER BY titulo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarLibro($id) {
        $sql = "SELECT * FROM libros WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Gestión de Usuarios
    public function agregarUsuario(Usuario $usuario) {
        $sql = "INSERT INTO usuarios (nombre, email, telefono) VALUES (:nombre, :email, :telefono)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nombre', $usuario->getNombre());
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':telefono', $usuario->getTelefono());
        return $stmt->execute();
    }

    public function editarUsuario($id, $nuevosDatos) {
        $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, telefono = :telefono WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nombre', $nuevosDatos['nombre']);
        $stmt->bindValue(':email', $nuevosDatos['email']);
        $stmt->bindValue(':telefono', $nuevosDatos['telefono']);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function eliminarUsuario($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function obtenerUsuarios() {
        $sql = "SELECT * FROM usuarios ORDER BY nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Gestión de Préstamos
    public function prestarLibro($libro_id, $usuario_id) {
        $libro = $this->buscarLibro($libro_id);
        if (!$libro || $libro['cantidad'] < 1) {
            return false;
        }

        $sqlInsert = "INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, estado) VALUES (:libro_id, :usuario_id, :fecha_prestamo, 'activo')";
        $stmt = $this->conn->prepare($sqlInsert);
        $stmt->bindValue(':libro_id', $libro_id);
        $stmt->bindValue(':usuario_id', $usuario_id);
        $stmt->bindValue(':fecha_prestamo', date('Y-m-d'));
        $exito = $stmt->execute();

        if ($exito) {
            $sqlUpdate = "UPDATE libros SET cantidad = cantidad - 1 WHERE id = :id";
            $stmt2 = $this->conn->prepare($sqlUpdate);
            $stmt2->bindValue(':id', $libro_id);
            $stmt2->execute();
        }

        return $exito;
    }

    public function devolverLibro($prestamo_id) {
        $sqlBuscar = "SELECT libro_id FROM prestamos WHERE id = :id";
        $stmt = $this->conn->prepare($sqlBuscar);
        $stmt->bindValue(':id', $prestamo_id);
        $stmt->execute();
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo) {
            return false;
        }

        $sqlUpdate = "UPDATE prestamos SET fecha_devolucion = :fecha, estado = 'devuelto' WHERE id = :id";
        $stmt2 = $this->conn->prepare($sqlUpdate);
        $stmt2->bindValue(':fecha', date('Y-m-d'));
        $stmt2->bindValue(':id', $prestamo_id);
        $exito = $stmt2->execute();

        if ($exito) {
            $sqlLibro = "UPDATE libros SET cantidad = cantidad + 1 WHERE id = :id";
            $stmt3 = $this->conn->prepare($sqlLibro);
            $stmt3->bindValue(':id', $prestamo['libro_id']);
            $stmt3->execute();
        }

        return $exito;
    }

    public function obtenerPrestamosActivos() {
        $sql = "SELECT prestamos.id, libros.titulo, usuarios.nombre, prestamos.fecha_prestamo
                FROM prestamos
                JOIN libros ON prestamos.libro_id = libros.id
                JOIN usuarios ON prestamos.usuario_id = usuarios.id
                WHERE prestamos.estado = 'activo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}