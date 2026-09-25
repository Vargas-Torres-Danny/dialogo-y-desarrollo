<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();
requireAdmin();

$pdo = getPDO();

// --- Guardar (crear o editar) ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id        = $_POST['id'] ?: null;
    $nombres   = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $apMaterno = trim($_POST['ap_materno'] ?? '') ?: null;
    $email     = trim($_POST['email'] ?? '');
    $rol       = $_POST['rol'] ?? 'redactor';
    $password  = $_POST['password'] ?? '';

    if ($nombres === '' || $apPaterno === '' || $email === '' || (!$id && $password === '')) {
        flash('danger', 'Nombres, apellido paterno, correo y contraseña (para usuarios nuevos) son obligatorios.');
        redirect('usuarios.php');
    }

    if ($id) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'UPDATE usuarios SET nombres=?, ap_paterno=?, ap_materno=?, email=?, rol=?, password_hash=? WHERE id=?'
            );
            $stmt->execute([$nombres, $apPaterno, $apMaterno, $email, $rol, $hash, $id]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE usuarios SET nombres=?, ap_paterno=?, ap_materno=?, email=?, rol=? WHERE id=?'
            );
            $stmt->execute([$nombres, $apPaterno, $apMaterno, $email, $rol, $id]);
        }
        flash('success', 'Usuario actualizado.');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol) VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([$nombres, $apPaterno, $apMaterno, $email, $hash, $rol]);
            flash('success', 'Usuario creado.');
        } catch (PDOException $e) {
            flash('danger', 'Ya existe un usuario con ese correo.');
        }
    }
    redirect('usuarios.php');
}

// --- Eliminar ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int) $_POST['id'];
    if ($id === (int) $_SESSION['usuario_id']) {
        flash('danger', 'No puedes eliminar tu propio usuario mientras tienes la sesión activa.');
    } else {
        try {
            $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
            flash('success', 'Usuario eliminado.');
        } catch (PDOException $e) {
            flash('danger', 'No se puede eliminar: este usuario tiene contenido publicado asociado.');
        }
    }
    redirect('usuarios.php');
}

$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY nombres')->fetchAll();

$rolBadge = ['admin' => 'badge-secondary', 'editor' => 'badge-info', 'redactor' => 'badge-light'];

$pageTitle    = 'Usuarios';
$pageSubtitle = 'Cuentas con acceso al panel administrativo';
$activeMenu   = 'usuarios';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" onclick="nuevoUsuario()">
        <i class="fa fa-plus mr-1"></i> Nuevo usuario
    </button>
</div>
<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="usuarios-table" class="table table-striped table-hover overflow-auto" width="100%">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Desde</th><th class="no-sort">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= e($u['nombres'] . ' ' . $u['ap_paterno']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge <?= $rolBadge[$u['rol']] ?? 'badge-light' ?>"><?= e(ucfirst($u['rol'])) ?></span></td>
                    <td><?= formatFecha(substr($u['created_at'], 0, 10)) ?></td>
                    <td class="text-right text-nowrap">
                        <button class="btn btn-icon btn-sm" title="Editar"
                            onclick='editarUsuario(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                            <i class="fa fa-pencil"></i>
                        </button>
                        <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-icon btn-sm text-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>
  $(function(){ $('#usuarios-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });
</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="usuarios.php">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="f-id">
        <div class="modal-header">
          <h5 class="modal-title" id="f-titulo">Nuevo usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group"><label>Nombres</label><input name="nombres" id="f-nombres" class="form-control" required></div>
            <div class="col-md-6 form-group"><label>Ap. paterno</label><input name="ap_paterno" id="f-ap_paterno" class="form-control" required></div>
            <div class="col-md-6 form-group"><label>Ap. materno</label><input name="ap_materno" id="f-ap_materno" class="form-control"></div>
            <div class="col-md-6 form-group"><label>Correo</label><input type="email" name="email" id="f-email" class="form-control" placeholder="correo@dialogoydesarrollo.com.pe" required></div>
            <div class="col-md-6 form-group"><label>Rol</label>
                <select name="rol" id="f-rol" class="form-control">
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                    <option value="redactor">Redactor</option>
                </select>
            </div>
            <div class="col-md-6 form-group"><label>Contraseña</label><input type="password" name="password" id="f-password" class="form-control" placeholder="••••••••"></div>
          </div>
          <p class="text-muted small mb-0" id="f-password-hint">Déjala en blanco para no cambiarla.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="f-submit">Crear usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoUsuario() {
    document.getElementById('f-titulo').textContent = 'Nuevo usuario';
    document.getElementById('f-submit').textContent = 'Crear usuario';
    document.getElementById('f-id').value = '';
    document.getElementById('f-nombres').value = '';
    document.getElementById('f-ap_paterno').value = '';
    document.getElementById('f-ap_materno').value = '';
    document.getElementById('f-email').value = '';
    document.getElementById('f-rol').value = 'redactor';
    document.getElementById('f-password').value = '';
    document.getElementById('f-password').required = true;
    document.getElementById('f-password-hint').style.display = 'none';
    $('#formModal').modal('show');
}
function editarUsuario(u) {
    document.getElementById('f-titulo').textContent = 'Editar usuario';
    document.getElementById('f-submit').textContent = 'Guardar cambios';
    document.getElementById('f-id').value = u.id;
    document.getElementById('f-nombres').value = u.nombres || '';
    document.getElementById('f-ap_paterno').value = u.ap_paterno || '';
    document.getElementById('f-ap_materno').value = u.ap_materno || '';
    document.getElementById('f-email').value = u.email || '';
    document.getElementById('f-rol').value = u.rol || 'redactor';
    document.getElementById('f-password').value = '';
    document.getElementById('f-password').required = false;
    document.getElementById('f-password-hint').style.display = 'block';
    $('#formModal').modal('show');
}
</script>

</body>
</html>
