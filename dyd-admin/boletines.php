<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/upload.php';
requireLogin();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id      = $_POST['id'] ?: null;
    $numero  = trim($_POST['numero_boletin'] ?? '');
    $resumen = trim($_POST['resumen'] ?? '') ?: null;
    $fecha   = $_POST['fecha_publicacion'] ?? '';

    if ($numero === '' || $fecha === '') {
        flash('danger', 'El número de boletín y la fecha son obligatorios.');
        redirect('boletines.php');
    }

    $rutaFoto = subirArchivo('foto_portada', 'fotos', ['jpg', 'jpeg', 'png', 'webp']);
    $rutaPdf  = subirArchivo('archivo_pdf', 'pdfs', ['pdf']);
    if ($rutaFoto === false || $rutaPdf === false) {
        flash('danger', 'La portada debe ser jpg/png/webp y el archivo debe ser PDF.');
        redirect('boletines.php');
    }
    if (!$id && !$rutaPdf) {
        flash('danger', 'El PDF del boletín es obligatorio al crearlo.');
        redirect('boletines.php');
    }

    try {
        if ($id) {
            $actual = $pdo->prepare('SELECT foto_portada, archivo_pdf, usuario_id FROM boletines WHERE id = ?');
            $actual->execute([$id]);
            $prev = $actual->fetch();

            if (!$prev || !puedeEditarContenido((int) $prev['usuario_id'])) {
                flash('danger', 'No tienes permiso para editar este boletín.');
                redirect('boletines.php');
            }

            $rutaFoto = $rutaFoto ?: $prev['foto_portada'];
            $rutaPdf  = $rutaPdf ?: $prev['archivo_pdf'];

            $stmt = $pdo->prepare('UPDATE boletines SET numero_boletin=?, resumen=?, foto_portada=?, archivo_pdf=?, fecha_publicacion=? WHERE id=?');
            $stmt->execute([$numero, $resumen, $rutaFoto, $rutaPdf, $fecha, $id]);
            flash('success', 'Boletín actualizado.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO boletines (numero_boletin, resumen, foto_portada, archivo_pdf, fecha_publicacion, usuario_id) VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([$numero, $resumen, $rutaFoto, $rutaPdf, $fecha, (int) $_SESSION['usuario_id']]);
            flash('success', 'Boletín publicado.');
        }
    } catch (PDOException $e) {
        flash('danger', 'Ya existe un boletín con ese número.');
    }
    redirect('boletines.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id   = (int) $_POST['id'];
    $fila = $pdo->prepare('SELECT usuario_id FROM boletines WHERE id = ?');
    $fila->execute([$id]);
    $dueno = $fila->fetch();

    if (!$dueno || !puedeEliminarContenido((int) $dueno['usuario_id'])) {
        flash('danger', 'No tienes permiso para eliminar este boletín.');
        redirect('boletines.php');
    }

    $pdo->prepare('DELETE FROM boletines WHERE id = ?')->execute([$id]);
    flash('success', 'Boletín eliminado.');
    redirect('boletines.php');
}

$boletines = $pdo->query(
    "SELECT b.*, CONCAT(u.nombres,' ',u.ap_paterno) AS editor_nombre
       FROM boletines b JOIN usuarios u ON u.id = b.usuario_id
      ORDER BY b.fecha_publicacion DESC"
)->fetchAll();

$pageTitle    = 'Boletines';
$pageSubtitle = 'Boletines informativos periódicos';
$activeMenu   = 'boletines';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" onclick="nuevoRegistro()"><i class="fa fa-plus mr-1"></i> Nuevo boletín</button>
</div>
<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="boletines-table" class="table table-striped table-hover overflow-auto" width="100%">
            <thead><tr><th>N° Boletín</th><th>Resumen</th><th>Fecha</th><th>PDF</th><th>Publicado por</th><th class="no-sort">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($boletines as $b): ?>
                <tr>
                    <td>N° <?= e($b['numero_boletin']) ?></td>
                    <td><?= e($b['resumen']) ?></td>
                    <td><?= formatFecha($b['fecha_publicacion']) ?></td>
                    <td><a href="<?= e($b['archivo_pdf']) ?>" target="_blank">Ver PDF</a></td>
                    <td><?= e($b['editor_nombre']) ?></td>
                    <td class="text-right text-nowrap">
                        <?php if (puedeEditarContenido((int) $b['usuario_id'])): ?>
                            <button class="btn btn-icon btn-sm" title="Editar" onclick='editarRegistro(<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa fa-pencil"></i></button>
                        <?php endif; ?>
                        <?php if (puedeEliminarContenido((int) $b['usuario_id'])): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este boletín?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                <button class="btn btn-icon btn-sm text-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                            </form>
                        <?php endif; ?>
                        <?php if (!puedeEditarContenido((int) $b['usuario_id']) && !puedeEliminarContenido((int) $b['usuario_id'])): ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($boletines)): ?><tr><td colspan="6" class="text-center text-muted">Sin boletines registrados aún.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>$(function(){ $('#boletines-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="boletines.php" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="f-id">
        <div class="modal-header">
          <h5 class="modal-title" id="f-titulo-modal">Nuevo boletín</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 form-group"><label>N° de boletín</label><input type="text" name="numero_boletin" id="f-numero" class="form-control" placeholder="46" required></div>
            <div class="col-md-8 form-group"><label>Fecha</label><input type="date" name="fecha_publicacion" id="f-fecha" class="form-control" required></div>
            <div class="col-md-12 form-group"><label>Resumen</label><textarea name="resumen" id="f-resumen" class="form-control" rows="3" placeholder="Resumen del boletín"></textarea></div>
            <div class="col-md-6 form-group"><label>Portada (imagen)</label><input type="file" name="foto_portada" class="form-control-file" accept=".jpg,.jpeg,.png,.webp"></div>
            <div class="col-md-6 form-group"><label>PDF del boletín</label><input type="file" name="archivo_pdf" id="f-pdf" class="form-control-file" accept=".pdf"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="f-submit">Guardar boletín</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoRegistro() {
    document.getElementById('f-titulo-modal').textContent = 'Nuevo boletín';
    document.getElementById('f-submit').textContent = 'Guardar boletín';
    document.getElementById('f-id').value = '';
    document.getElementById('f-numero').value = '';
    document.getElementById('f-fecha').value = '';
    document.getElementById('f-resumen').value = '';
    document.getElementById('f-pdf').required = true;
    $('#formModal').modal('show');
}
function editarRegistro(b) {
    document.getElementById('f-titulo-modal').textContent = 'Editar boletín';
    document.getElementById('f-submit').textContent = 'Guardar cambios';
    document.getElementById('f-id').value = b.id;
    document.getElementById('f-numero').value = b.numero_boletin || '';
    document.getElementById('f-fecha').value = b.fecha_publicacion;
    document.getElementById('f-resumen').value = b.resumen || '';
    document.getElementById('f-pdf').required = false;
    $('#formModal').modal('show');
}
</script>

</body>
</html>
