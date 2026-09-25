<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();

$pdo = getPDO();
$id  = (int) $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres   = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $pass1     = $_POST['password'] ?? '';
    $pass2     = $_POST['password_confirm'] ?? '';

    if ($nombres === '' || $apPaterno === '' || $email === '') {
        flash('danger', 'Nombre, apellido y correo son obligatorios.');
    } elseif ($pass1 !== '' && $pass1 !== $pass2) {
        flash('danger', 'Las contraseñas nuevas no coinciden.');
    } else {
        if ($pass1 !== '') {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE usuarios SET nombres=?, ap_paterno=?, email=?, password_hash=? WHERE id=?');
            $stmt->execute([$nombres, $apPaterno, $email, $hash, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuarios SET nombres=?, ap_paterno=?, email=? WHERE id=?');
            $stmt->execute([$nombres, $apPaterno, $email, $id]);
        }
        // Refrescamos los datos de sesión para que el sidebar muestre el nombre nuevo.
        $_SESSION['usuario_nombres'] = $nombres . ' ' . $apPaterno;
        $_SESSION['usuario_email']   = $email;
        flash('success', 'Perfil actualizado.');
    }
    redirect('perfil.php');
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();

$pageTitle    = 'Mi perfil';
$pageSubtitle = 'Datos de tu cuenta en el panel';
$activeMenu   = 'perfil';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="row">
  <div class="col-md-4">
    <section class="widget">
      <div class="widget-body text-center">
        <img src="img/avatar.png" class="rounded-circle mb-3" width="110" alt="Avatar">
        <h5 class="mb-0"><?= e($usuario['nombres'] . ' ' . $usuario['ap_paterno']) ?></h5>
        <p class="text-muted"><?= e(ucfirst($usuario['rol'])) ?> · Diálogo y Desarrollo</p>
      </div>
    </section>
  </div>
  <div class="col-md-8">
    <section class="widget">
      <header><h6>Datos de la cuenta</h6></header>
      <div class="widget-body">
        <form class="row" method="post" action="perfil.php">
          <div class="col-md-6 form-group"><label>Nombres</label><input type="text" name="nombres" class="form-control" value="<?= e($usuario['nombres']) ?>" required></div>
          <div class="col-md-6 form-group"><label>Apellido paterno</label><input type="text" name="ap_paterno" class="form-control" value="<?= e($usuario['ap_paterno']) ?>" required></div>
          <div class="col-md-12 form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="<?= e($usuario['email']) ?>" required></div>
          <div class="col-md-6 form-group"><label>Nueva contraseña</label><input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar"></div>
          <div class="col-md-6 form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirm" class="form-control"></div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
</body>
</html>
