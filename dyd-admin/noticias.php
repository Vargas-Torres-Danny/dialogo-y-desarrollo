<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/upload.php';
requireLogin();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id          = $_POST['id'] ?: null;
    $titulo      = trim($_POST['titulo'] ?? '');
    $linkExterno = trim($_POST['link_externo'] ?? '') ?: null;
    $fecha       = $_POST['fecha_publicacion'] ?? '';

    if ($titulo === '' || $fecha === '') {
        flash('danger', 'Título y fecha son obligatorios.');
        redirect('noticias.php');
    }

    $rutaFoto = subirArchivo('foto', 'fotos', ['jpg', 'jpeg', 'png', 'webp']);
    if ($rutaFoto === false) {
        flash('danger', 'La foto debe ser jpg, png o webp.');
        redirect('noticias.php');
    }

    if ($id) {
        $actual = $pdo->prepare('SELECT foto, usuario_id FROM noticias WHERE id = ?');
        $actual->execute([$id]);
        $prev = $actual->fetch();

        if (!$prev || !puedeEditarContenido((int) $prev['usuario_id'])) {
            flash('danger', 'No tienes permiso para editar esta noticia.');
            redirect('noticias.php');
        }

        if ($rutaFoto === null) {
            $stmt = $pdo->prepare('UPDATE noticias SET titulo=?, link_externo=?, fecha_publicacion=? WHERE id=?');
            $stmt->execute([$titulo, $linkExterno, $fecha, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE noticias SET titulo=?, foto=?, link_externo=?, fecha_publicacion=? WHERE id=?');
            $stmt->execute([$titulo, $rutaFoto, $linkExterno, $fecha, $id]);
        }
        flash('success', 'Noticia actualizada.');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO noticias (titulo, foto, link_externo, fecha_publicacion, usuario_id) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$titulo, $rutaFoto, $linkExterno, $fecha, (int) $_SESSION['usuario_id']]);
        flash('success', 'Noticia registrada.');
    }
    redirect('noticias.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id   = (int) $_POST['id'];
    $fila = $pdo->prepare('SELECT usuario_id FROM noticias WHERE id = ?');
    $fila->execute([$id]);
    $dueno = $fila->fetch();

    if (!$dueno || !puedeEliminarContenido((int) $dueno['usuario_id'])) {
        flash('danger', 'No tienes permiso para eliminar esta noticia.');
        redirect('noticias.php');
    }

    $pdo->prepare('DELETE FROM noticias WHERE id = ?')->execute([$id]);
    flash('success', 'Noticia eliminada.');
    redirect('noticias.php');
}

$noticias = $pdo->query(
    "SELECT n.*, CONCAT(u.nombres,' ',u.ap_paterno) AS editor_nombre
       FROM noticias n JOIN usuarios u ON u.id = n.usuario_id
      ORDER BY n.fecha_publicacion DESC"
)->fetchAll();

$pageTitle    = 'Noticias';
$pageSubtitle = 'Enlaces a noticias externas relevantes';
$activeMenu   = 'noticias';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" onclick="nuevoRegistro()"><i class="fa fa-plus mr-1"></i> Nueva noticia</button>
</div>
<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="noticias-table" class="table table-striped table-hover overflow-auto" width="100%">
            <thead><tr><th>Título</th><th>Enlace</th><th>Fecha</th><th>Registrado por</th><th class="no-sort">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($noticias as $n): ?>
                <tr>
                    <td><?= e($n['titulo']) ?></td>
                    <td><?php if ($n['link_externo']): ?><a href="<?= e($n['link_externo']) ?>" target="_blank" rel="noopener">Ver enlace</a><?php else: ?>—<?php endif; ?></td>
                    <td><?= formatFecha($n['fecha_publicacion']) ?></td>
                    <td><?= e($n['editor_nombre']) ?></td>
                    <td class="text-right text-nowrap">
                        <?php if (puedeEditarContenido((int) $n['usuario_id'])): ?>
                            <button class="btn btn-icon btn-sm" title="Editar" onclick='editarRegistro(<?= json_encode($n, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa fa-pencil"></i></button>
                        <?php endif; ?>
                        <?php if (puedeEliminarContenido((int) $n['usuario_id'])): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar esta noticia?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                                <button class="btn btn-icon btn-sm text-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                            </form>
                        <?php endif; ?>
                        <?php if (!puedeEditarContenido((int) $n['usuario_id']) && !puedeEliminarContenido((int) $n['usuario_id'])): ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($noticias)): ?><tr><td colspan="5" class="text-center text-muted">Sin noticias registradas aún.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>$(function(){ $('#noticias-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="noticias.php" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="f-id">
        <div class="modal-header">
          <h5 class="modal-title" id="f-titulo-modal">Nueva noticia</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 form-group"><label>Título</label><input type="text" name="titulo" id="f-titulo" class="form-control" placeholder="Título de la noticia" required></div>
            <div class="col-md-12 form-group"><label>Enlace</label><input type="url" name="link_externo" id="f-link" class="form-control" placeholder="https://..."></div>
            <div class="col-md-6 form-group"><label>Fecha</label><input type="date" name="fecha_publicacion" id="f-fecha" class="form-control" required></div>
            <div class="col-md-6 form-group"><label>Foto (opcional)</label><input type="file" name="foto" class="form-control-file" accept=".jpg,.jpeg,.png,.webp"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="f-submit">Guardar noticia</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoRegistro() {
    document.getElementById('f-titulo-modal').textContent = 'Nueva noticia';
    document.getElementById('f-submit').textContent = 'Guardar noticia';
    document.getElementById('f-id').value = '';
    document.getElementById('f-titulo').value = '';
    document.getElementById('f-link').value = '';
    document.getElementById('f-fecha').value = '';
    $('#formModal').modal('show');
}
function editarRegistro(n) {
    document.getElementById('f-titulo-modal').textContent = 'Editar noticia';
    document.getElementById('f-submit').textContent = 'Guardar cambios';
    document.getElementById('f-id').value = n.id;
    document.getElementById('f-titulo').value = n.titulo || '';
    document.getElementById('f-link').value = n.link_externo || '';
    document.getElementById('f-fecha').value = n.fecha_publicacion;
    $('#formModal').modal('show');
}
</script>

</body>
</html>
