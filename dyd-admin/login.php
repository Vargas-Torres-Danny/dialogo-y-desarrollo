<?php
require_once __DIR__ . '/includes/auth.php';

// Si ya hay sesión activa, directo al dashboard.
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Ingresa tu correo y contraseña.';
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            iniciarSesionUsuario($usuario);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Iniciar sesión · Diálogo y Desarrollo</title>
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
                            <p class="text-white-50 mb-0">Panel de contenidos</p>
                        </header>
                        <div class="widget-body">
                            <p class="widget-login-info">
                                Ingresa con tu cuenta para administrar el contenido del sitio.
                            </p>

                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <form class="login-form mt-lg" method="post" action="login.php">
                                <div role="group" class="form-group">
                                    <label for="email" class="d-block">Correo electrónico</label>
                                    <div>
                                        <div role="group" class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fi flaticon-id-card text-white"></i>
                                                </div>
                                            </div>
                                            <input id="email" name="email" type="email" required="required"
                                                placeholder="tucorreo@dialogoydesarrollo.com.pe"
                                                class="form-control input-transparent pl-3">
                                        </div>
                                    </div>
                                </div>
                                <div role="group" class="form-group">
                                    <label for="password" class="d-block">Contraseña</label>
                                    <div>
                                        <div role="group" class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fi flaticon-lock text-white"></i>
                                                </div>
                                            </div>
                                            <input id="password" name="password" type="password" required="required"
                                                placeholder="Contraseña" class="form-control input-transparent pl-3">
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix">
                                    <div class="btn-toolbar">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <span class="auth-btn-circle">
                                                <i class="fa fa-caret-right"></i>
                                            </span>
                                            Iniciar sesión</button>
                                    </div>
                                </div>
                            </form>
                            <p class="text-center mt-lg mb-1"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
                            <p class="text-center mb-0"><a href="registro.php">¿No tienes cuenta? Crear una</a></p>
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
