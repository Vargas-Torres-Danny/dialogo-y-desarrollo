<?php
/**
 * EJECUTAR UNA SOLA VEZ desde el navegador (ej. http://localhost/dyd-admin/setup_admin.php)
 * para crear tu primer usuario administrador en la tabla "usuarios" de la BD
 * "revista_digital". Genera el hash de la contraseña con password_hash() de
 * PHP (mucho más seguro y confiable que pegar un hash a mano).
 *
 * IMPORTANTE: borra o renombra este archivo después de usarlo, para que
 * nadie más pueda crear usuarios desde aquí.
 */

require_once __DIR__ . '/config/database.php';

$mensaje = '';
$exito   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres    = trim($_POST['nombres'] ?? '');
    $apPaterno  = trim($_POST['ap_paterno'] ?? '');
    $apMaterno  = trim($_POST['ap_materno'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rol        = $_POST['rol'] ?? 'admin';

    if ($nombres === '' || $apPaterno === '' || $email === '' || $password === '') {
        $mensaje = 'Completa nombres, apellido paterno, correo y contraseña.';
    } else {
        $pdo = getPDO();
        $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $check->execute([$email]);

        if ($check->fetch()) {
            $mensaje = 'Ya existe un usuario con ese correo.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$nombres, $apPaterno, $apMaterno ?: null, $email, $hash, $rol]);
            $exito   = true;
            $mensaje = 'Usuario creado correctamente. Ya puedes iniciar sesión en login.php.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Crear usuario admin · Diálogo y Desarrollo</title>
    <link href="css/application.min.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="container">
    <main class="widget-login-container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-md-7 col-11">
                <section class="widget widget-login">
                    <header class="text-center"><h3>Crear primer usuario</h3></header>
                    <div class="widget-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?= $exito ? 'success' : 'danger' ?>"><?= htmlspecialchars($mensaje) ?></div>
                        <?php endif; ?>

                        <?php if (!$exito): ?>
                        <form method="post" class="row">
                            <div class="col-md-6 form-group"><label>Nombres</label><input name="nombres" class="form-control input-transparent" required></div>
                            <div class="col-md-6 form-group"><label>Apellido paterno</label><input name="ap_paterno" class="form-control input-transparent" required></div>
                            <div class="col-md-6 form-group"><label>Apellido materno</label><input name="ap_materno" class="form-control input-transparent"></div>
                            <div class="col-md-6 form-group"><label>Rol</label>
                                <select name="rol" class="form-control input-transparent">
                                    <option value="admin">Admin</option>
                                    <option value="editor">Editor</option>
                                    <option value="redactor">Redactor</option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group"><label>Correo</label><input type="email" name="email" class="form-control input-transparent" required></div>
                            <div class="col-md-12 form-group"><label>Contraseña</label><input type="password" name="password" class="form-control input-transparent" required></div>
                            <div class="col-12"><button class="btn btn-primary btn-block">Crear usuario</button></div>
                        </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-block">Ir a iniciar sesión</a>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>
