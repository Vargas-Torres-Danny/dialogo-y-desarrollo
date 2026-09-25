<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Desde este formulario público SOLO se puede crear Redactor o Editor.
// Un Admin únicamente se crea a mano desde "Usuarios" ya logueado como Admin.
$rolesPermitidos = ['redactor' => 'Redactor', 'editor' => 'Editor'];

$error = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres   = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $apMaterno = trim($_POST['ap_materno'] ?? '') ?: null;
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $rolPedido = $_POST['rol'] ?? 'redactor';

    // Nunca confiamos en lo que llega por POST para decidir el rol: si
    // alguien intenta mandar "admin" a mano, se cae a "redactor".
    $rol = array_key_exists($rolPedido, $rolesPermitidos) ? $rolPedido : 'redactor';

    if ($nombres === '' || $apPaterno === '' || $email === '' || $password === '') {
        $error = 'Nombres, apellido paterno, correo y contraseña son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo válido.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        $pdo  = getPDO();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->prepare(
                'INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol) VALUES (?,?,?,?,?,?)'
            )->execute([$nombres, $apPaterno, $apMaterno, $email, $hash, $rol]);
            $exito = true;
        } catch (PDOException $e) {
            $error = 'Ya existe un usuario con ese correo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Crear cuenta · Diálogo y Desarrollo</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="css/application.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>

<body class="login-page">
    <div class="container">
        <main id="content" class="widget-login-container" role="main">
            <div class="row justify-content-center">
                <div class="col-xl-5 col-md-8 col-11">
                    <section class="widget widget-login animated fadeInUp">
                        <header class="text-center">
                            <h3>Diálogo y Desarrollo</h3>
                            <p class="text-white-50 mb-0">Crear cuenta</p>
                        </header>
                        <div class="widget-body">

                            <?php if ($exito): ?>
                                <div class="alert alert-success">Tu cuenta se creó correctamente. Ya puedes iniciar sesión.</div>
                                <a href="login.php" class="btn btn-primary btn-block mt-lg">Iniciar sesión</a>
                            <?php else: ?>
                                <p class="widget-login-info">
                                    Crea tu cuenta para colaborar con contenido. Las cuentas de Administrador solo las crea otro Administrador desde el panel.
                                </p>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <form method="post" action="registro.php" class="mt-lg">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Nombres</label>
                                            <input name="nombres" class="form-control input-transparent" required
                                                value="<?= e($_POST['nombres'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Apellido paterno</label>
                                            <input name="ap_paterno" class="form-control input-transparent" required
                                                value="<?= e($_POST['ap_paterno'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Apellido materno</label>
                                            <input name="ap_materno" class="form-control input-transparent"
                                                value="<?= e($_POST['ap_materno'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Rol</label>
                                            <select name="rol" class="form-control input-transparent">
                                                <?php foreach ($rolesPermitidos as $valor => $etiqueta): ?>
                                                    <option value="<?= e($valor) ?>" <?= (($_POST['rol'] ?? 'redactor') === $valor) ? 'selected' : '' ?>>
                                                        <?= e($etiqueta) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label>Correo</label>
                                            <input type="email" name="email" class="form-control input-transparent"
                                                placeholder="correo@dialogoydesarrollo.com.pe" required
                                                value="<?= e($_POST['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label>Contraseña</label>
                                            <input type="password" name="password" class="form-control input-transparent"
                                                minlength="8" placeholder="Mínimo 8 caracteres" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block mt-2">Crear cuenta</button>
                                </form>
                                <p class="text-center mt-lg mb-0"><a href="login.php">Ya tengo cuenta, iniciar sesión</a></p>
                            <?php endif; ?>

                        </div>
                    </section>
                </div>
            </div>

            <footer class="page-footer">
                © 2026 Diálogo y Desarrollo — Cusco, Perú
            </footer>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js"></script>
    <script src="js/vendor-shim.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/app.js"></script>
</body>

</html>
