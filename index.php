<?php
require_once 'classes/Biblioteca.php';

$biblioteca = new Biblioteca();

$action = $_GET['action'] ?? 'libros';

// Manejar envíos de formularios (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar_libro'])) {
        $libro = new Libro($_POST['titulo'], $_POST['autor'], $_POST['isbn'], $_POST['cantidad']);
        $biblioteca->agregarLibro($libro);
        header('Location: index.php?action=libros');
        exit;
    }

    if (isset($_POST['editar_libro'])) {
        $datos = [
            'titulo' => $_POST['titulo'],
            'autor' => $_POST['autor'],
            'isbn' => $_POST['isbn'],
            'cantidad' => $_POST['cantidad'],
        ];
        $biblioteca->editarLibro($_POST['id'], $datos);
        header('Location: index.php?action=libros');
        exit;
    }

    if (isset($_POST['agregar_usuario'])) {
        $usuario = new Usuario($_POST['nombre'], $_POST['email'], $_POST['telefono']);
        $biblioteca->agregarUsuario($usuario);
        header('Location: index.php?action=usuarios');
        exit;
    }

    if (isset($_POST['editar_usuario'])) {
        $datos = [
            'nombre' => $_POST['nombre'],
            'email' => $_POST['email'],
            'telefono' => $_POST['telefono'],
        ];
        $biblioteca->editarUsuario($_POST['id'], $datos);
        header('Location: index.php?action=usuarios');
        exit;
    }
}

// Manejar acciones simples por GET (enlaces)
if ($action === 'eliminar_libro' && isset($_GET['id'])) {
    $biblioteca->eliminarLibro($_GET['id']);
    header('Location: index.php?action=libros');
    exit;
}

if ($action === 'eliminar_usuario' && isset($_GET['id'])) {
    $biblioteca->eliminarUsuario($_GET['id']);
    header('Location: index.php?action=usuarios');
    exit;
}

if ($action === 'prestar' && isset($_GET['libro_id']) && isset($_GET['usuario_id'])) {
    $biblioteca->prestarLibro($_GET['libro_id'], $_GET['usuario_id']);
    header('Location: index.php?action=prestamos');
    exit;
}

if ($action === 'devolver' && isset($_GET['prestamo_id'])) {
    $biblioteca->devolverLibro($_GET['prestamo_id']);
    header('Location: index.php?action=prestamos');
    exit;
}

$libros = $biblioteca->obtenerLibros();
$usuarios = $biblioteca->obtenerUsuarios();
$prestamosActivos = $biblioteca->obtenerPrestamosActivos();

$libroEditar = null;
if ($action === 'editar_libro' && isset($_GET['id'])) {
    $libroEditar = $biblioteca->buscarLibro($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Biblioteca</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; background: #eee; padding: 10px; }
        nav a { margin-right: 15px; text-decoration: none; color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        form { margin-bottom: 10px; }
        input, select { padding: 5px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Biblioteca Mini-App</h1>

        <nav>
            <a href="index.php">Inicio / Libros</a>
            <a href="index.php?action=usuarios">Usuarios</a>
            <a href="index.php?action=prestamos">Préstamos</a>
        </nav>

        <div id="content">

        <?php if ($action === 'libros' || $action === ''): ?>
            <h2>Libros</h2>

            <h3>Agregar nuevo libro</h3>
            <form action="index.php" method="POST">
                <input type="text" name="titulo" placeholder="Título" required>
                <input type="text" name="autor" placeholder="Autor" required>
                <input type="text" name="isbn" placeholder="ISBN">
                <input type="number" name="cantidad" placeholder="Cantidad" value="1" min="1">
                <button type="submit" name="agregar_libro">Agregar</button>
            </form>

            <table>
                <thead>
                    <tr><th>ID</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Cantidad</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td><?= $libro['id'] ?></td>
                        <td><?= htmlspecialchars($libro['titulo']) ?></td>
                        <td><?= htmlspecialchars($libro['autor']) ?></td>
                        <td><?= htmlspecialchars($libro['isbn']) ?></td>
                        <td><?= $libro['cantidad'] ?></td>
                        <td>
                            <a href="index.php?action=editar_libro&id=<?= $libro['id'] ?>">Editar</a>
                            <a href="index.php?action=eliminar_libro&id=<?= $libro['id'] ?>" onclick="return confirm('¿Eliminar este libro?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($action === 'editar_libro' && $libroEditar): ?>
            <h2>Editar libro</h2>
            <form action="index.php" method="POST">
                <input type="hidden" name="id" value="<?= $libroEditar['id'] ?>">
                <input type="text" name="titulo" value="<?= htmlspecialchars($libroEditar['titulo']) ?>" required>
                <input type="text" name="autor" value="<?= htmlspecialchars($libroEditar['autor']) ?>" required>
                <input type="text" name="isbn" value="<?= htmlspecialchars($libroEditar['isbn']) ?>">
                <input type="number" name="cantidad" value="<?= $libroEditar['cantidad'] ?>" min="0">
                <button type="submit" name="editar_libro">Guardar cambios</button>
            </form>

        <?php elseif ($action === 'usuarios'): ?>
            <h2>Usuarios</h2>

            <h3>Agregar nuevo usuario</h3>
            <form action="index.php" method="POST">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <button type="submit" name="agregar_usuario">Agregar</button>
            </form>

            <table>
                <thead>
                    <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['id'] ?></td>
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                        <td>
                            <a href="index.php?action=eliminar_usuario&id=<?= $usuario['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($action === 'prestamos'): ?>
            <h2>Préstamos activos</h2>

            <h3>Registrar nuevo préstamo</h3>
            <form action="index.php" method="GET">
                <input type="hidden" name="action" value="prestar">
                <select name="libro_id" required>
                    <option value="">-- Selecciona un libro --</option>
                    <?php foreach ($libros as $libro): ?>
                        <?php if ($libro['cantidad'] > 0): ?>
                        <option value="<?= $libro['id'] ?>"><?= htmlspecialchars($libro['titulo']) ?> (<?= $libro['cantidad'] ?> disponibles)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <select name="usuario_id" required>
                    <option value="">-- Selecciona un usuario --</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Prestar</button>
            </form>

            <table>
                <thead>
                    <tr><th>ID Préstamo</th><th>Libro</th><th>Usuario</th><th>Fecha préstamo</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($prestamosActivos as $prestamo): ?>
                    <tr>
                        <td><?= $prestamo['id'] ?></td>
                        <td><?= htmlspecialchars($prestamo['titulo']) ?></td>
                        <td><?= htmlspecialchars($prestamo['nombre']) ?></td>
                        <td><?= $prestamo['fecha_prestamo'] ?></td>
                        <td>
                            <a href="index.php?action=devolver&prestamo_id=<?= $prestamo['id'] ?>">Devolver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

        </div>
    </div>
</body>
</html>