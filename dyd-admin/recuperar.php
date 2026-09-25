<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo válido.';
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Si el correo existe, generamos el token y mandamos el enlace.
        // Si no existe, igual mostramos el mismo mensaje de éxito (no
        // confirmamos ni negamos si un correo está registrado).
        if ($usuario) {
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $tokenGuardado = false;

            try {
                $pdo->prepare('UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE id = ?')
                    ->execute([$token, $expira, $usuario['id']]);
                $tokenGuardado = true;
            } catch (PDOException $e) {
                $error = 'Faltan las columnas reset_token/reset_expira en "usuarios". Corre sql/agregar_recuperacion_password.sql en phpMyAdmin.';
            }

            if ($tokenGuardado) {
                $baseUrl = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://')
                    . $_SERVER['HTTP_HOST']
                    . rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
                $link = $baseUrl . '/restablecer.php?token=' . $token;

                try {
                    require_once __DIR__ . '/includes/mailer.php';
                    enviarCorreo(
                        $usuario['email'],
                        $usuario['nombres'],
                        'Restablece tu contraseña · Diálogo y Desarrollo',
                        '<p>Hola ' . e($usuario['nombres']) . ',</p>'
                        . '<p>Recibimos una solicitud para restablecer tu contraseña del panel de Diálogo y Desarrollo.</p>'
                        . '<p><a href="' . e($link) . '">Haz clic aquí para crear una nueva contraseña</a> (el enlace vale por 1 hora).</p>'
                        . '<p>Si tú no pediste esto, puedes ignorar este correo tranquilamente.</p>'
                    );
                } catch (Exception $e) {
                    // Este error solo debería verse mientras estás configurando
                    // el envío de correos en local; en producción no se muestra
                    // este detalle al usuario final.
                    $error = 'No se pudo enviar el correo. Revisa config/mail.php y que la carpeta libs/PHPMailer exista. Detalle: ' . $e->getMessage();
                }
            }
        }

        if ($error === '') {
            $enviado = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Recuperar contraseña · Diálogo y Desarrollo</title>
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
                            <p class="text-white-50 mb-0">Recuperar contraseña</p>
                        </header>
                        <div class="widget-body">

                            <?php if ($enviado): ?>
                                <div class="alert alert-success">
                                    Si <strong><?= e($email) ?></strong> está registrado, te enviamos un correo con un enlace para crear una nueva contraseña. Revisa también la carpeta de spam.
                                </div>
                                <a href="login.php" class="btn btn-primary btn-block mt-lg">Volver a iniciar sesión</a>
                            <?php else: ?>
                                <p class="widget-login-info">
                                    Ingresa el correo con el que te registraste y te mandamos un enlace para crear una nueva contraseña.
                                </p>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <form class="login-form mt-lg" method="post" action="recuperar.php">
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
                                    <div class="clearfix">
                                        <div class="btn-toolbar">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <span class="auth-btn-circle">
                                                    <i class="fa fa-caret-right"></i>
                                                </span>
                                                Enviar enlace</button>
                                        </div>
                                    </div>
                                </form>
                                <p class="text-center mt-lg mb-0"><a href="login.php">Volver a iniciar sesión</a></p>
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
