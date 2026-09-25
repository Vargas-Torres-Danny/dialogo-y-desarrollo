<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$pdo   = getPDO();
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = '';
$exito = false;
$usuario = null;

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE reset_token = ? AND reset_expira > NOW() LIMIT 1');
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
}

$enlaceValido = $token !== '' && $usuario;

if (!$enlaceValido) {
    $error = 'Este enlace no es válido o ya expiró. Solicita uno nuevo.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($pass1) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Las dos contraseñas no coinciden.';
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?')
            ->execute([$hash, $usuario['id']]);
        $exito = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Restablecer contraseña · Diálogo y Desarrollo</title>
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
                <div class="col-xl-4 col-md-7 col-10">
                    <section class="widget widget-login animated fadeInUp">
                        <header class="text-center">
                            <h3>Diálogo y Desarrollo</h3>
                            <p class="text-white-50 mb-0">Nueva contraseña</p>
                        </header>
                        <div class="widget-body">

                            <?php if ($exito): ?>
                                <div class="alert alert-success">Tu contraseña se actualizó correctamente.</div>
                                <a href="login.php" class="btn btn-primary btn-block mt-lg">Iniciar sesión</a>

                            <?php elseif (!$enlaceValido): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <a href="recuperar.php" class="btn btn-primary btn-block mt-lg">Solicitar un enlace nuevo</a>

                            <?php else: ?>
                                <p class="widget-login-info">Hola <?= e($usuario['nombres']) ?>, elige tu nueva contraseña.</p>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <form class="login-form mt-lg" method="post" action="restablecer.php">
                                    <input type="hidden" name="token" value="<?= e($token) ?>">
                                    <div role="group" class="form-group">
                                        <label for="password" class="d-block">Nueva contraseña</label>
                                        <div>
                                            <div role="group" class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <i class="fi flaticon-lock text-white"></i>
                                                    </div>
                                                </div>
                                                <input id="password" name="password" type="password" required="required"
                                                    minlength="8" placeholder="Mínimo 8 caracteres"
                                                    class="form-control input-transparent pl-3">
                                            </div>
                                        </div>
                                    </div>
                                    <div role="group" class="form-group">
                                        <label for="password2" class="d-block">Confirmar contraseña</label>
                                        <div>
                                            <div role="group" class="input-group">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <i class="fi flaticon-lock text-white"></i>
                                                    </div>
                                                </div>
                                                <input id="password2" name="password2" type="password" required="required"
                                                    minlength="8" placeholder="Repite la contraseña"
                                                    class="form-control input-transparent pl-3">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix">
                                        <div class="btn-toolbar">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <span class="auth-btn-circle">
                                                    <i class="fa fa-caret-right"></i>
                                                </span>
                                                Guardar nueva contraseña</button>
                                        </div>
                                    </div>
                                </form>
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
