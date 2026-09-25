<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();

$pdo = getPDO();

// --- Guardar (crear o editar) ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id         = $_POST['id'] ?: null;
    $nombres    = trim($_POST['nombres'] ?? '');
    $apPaterno  = trim($_POST['ap_paterno'] ?? '') ?: null;
    $apMaterno  = trim($_POST['ap_materno'] ?? '') ?: null;
    $nickname   = trim($_POST['nickname'] ?? '') ?: null;
    $esNickname = isset($_POST['es_nickname']) ? 1 : 0;

    if ($nombres === '') {
        flash('danger', 'El nombre del autor es obligatorio.');
    } else {
        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE autores SET nombres=?, ap_paterno=?, ap_materno=?, nickname=?, es_nickname=? WHERE id=?'
            );
            $stmt->execute([$nombres, $apPaterno, $apMaterno, $nickname, $esNickname, $id]);
            flash('success', 'Autor actualizado.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO autores (nombres, ap_paterno, ap_materno, nickname, es_nickname) VALUES (?,?,?,?,?)'
            );
            $stmt->execute([$nombres, $apPaterno, $apMaterno, $nickname, $esNickname]);
            flash('success', 'Autor creado.');
        }
    }
    redirect('autores.php');
}

// --- Eliminar ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int) $_POST['id'];
    try {
        $pdo->prepare('DELETE FROM autores WHERE id = ?')->execute([$id]);
        flash('success', 'Autor eliminado.');
    } catch (PDOException $e) {
        // Restricción de FK: hay reportajes que usan este autor.
        flash('danger', 'No se puede eliminar: este autor tiene reportajes asociados.');
    }
    redirect('autores.php');
}

// --- Listado con conteo de reportajes ----------------------------------
$autores = $pdo->query(
    'SELECT a.*, COUNT(r.id) AS total_reportajes
       FROM autores a
       LEFT JOIN reportajes r ON r.autor_id = a.id
      GROUP BY a.id
      ORDER BY a.nombres'
)->fetchAll();

$pageTitle    = 'Autores';
$pageSubtitle = 'Autores que firman los reportajes';
$activeMenu   = 'autores';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" onclick="nuevoAutor()">
        <i class="fa fa-plus mr-1"></i> Nuevo autor
    </button>
</div>
<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="autores-table" class="table table-striped table-hover overflow-auto" width="100%">
            <thead><tr><th>Nombre</th><th>Reportajes</th><th class="no-sort">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($autores as $a): ?>
                <tr>
                    <td><?= e(trim($a['nombres'] . ' ' . $a['ap_paterno'] . ' ' . $a['ap_materno'])) ?>
                        <?php if ($a['es_nickname'] && $a['nickname']): ?>
                            <span class="text-muted">(<?= e($a['nickname']) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $a['total_reportajes'] ?> reportajes</td>
                    <td class="text-right text-nowrap">
                        <button class="btn btn-icon btn-sm" title="Editar"
                            onclick='editarAutor(<?= json_encode($a, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                            <i class="fa fa-pencil"></i>
                        </button>
                        <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este autor?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button class="btn btn-icon btn-sm text-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($autores)): ?>
                <tr><td colspan="3" class="text-center text-muted">Sin autores registrados aún.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>
  $(function(){ $('#autores-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });
</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="autores.php">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="f-id">
        <div class="modal-header">
          <h5 class="modal-title" id="f-titulo">Nuevo autor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group"><label>Nombres</label><input name="nombres" id="f-nombres" class="form-control" placeholder="Nombres" required></div>
            <div class="col-md-3 form-group"><label>Ap. paterno</label><input name="ap_paterno" id="f-ap_paterno" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Ap. materno</label><input name="ap_materno" id="f-ap_materno" class="form-control"></div>
            <div class="col-md-8 form-group"><label>Nickname / seudónimo</label><input name="nickname" id="f-nickname" class="form-control" placeholder="Opcional"></div>
            <div class="col-md-4 form-group d-flex align-items-end">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="es_nickname" id="f-es_nickname" value="1">
                <label class="form-check-label" for="f-es_nickname">Firmar con nickname</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="f-submit">Guardar autor</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoAutor() {
    document.getElementById('f-titulo').textContent = 'Nuevo autor';
    document.getElementById('f-submit').textContent = 'Guardar autor';
    document.getElementById('f-id').value = '';
    document.getElementById('f-nombres').value = '';
    document.getElementById('f-ap_paterno').value = '';
    document.getElementById('f-ap_materno').value = '';
    document.getElementById('f-nickname').value = '';
    document.getElementById('f-es_nickname').checked = false;
    $('#formModal').modal('show');
}
function editarAutor(a) {
    document.getElementById('f-titulo').textContent = 'Editar autor';
    document.getElementById('f-submit').textContent = 'Guardar cambios';
    document.getElementById('f-id').value = a.id;
    document.getElementById('f-nombres').value = a.nombres || '';
    document.getElementById('f-ap_paterno').value = a.ap_paterno || '';
    document.getElementById('f-ap_materno').value = a.ap_materno || '';
    document.getElementById('f-nickname').value = a.nickname || '';
    document.getElementById('f-es_nickname').checked = !!Number(a.es_nickname);
    $('#formModal').modal('show');
}
</script>

</body>
</html>
