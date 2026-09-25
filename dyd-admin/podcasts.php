<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id      = $_POST['id'] ?: null;
    $titulo  = trim($_POST['titulo'] ?? '');
    $urlEmbed = trim($_POST['url_embed'] ?? '');
    $fecha   = $_POST['fecha_publicacion'] ?? '';

    if ($titulo === '' || $urlEmbed === '' || $fecha === '') {
        flash('danger', 'Título, enlace de audio y fecha son obligatorios.');
        redirect('podcasts.php');
    }

    if ($id) {
        $actual = $pdo->prepare('SELECT usuario_id FROM podcasts WHERE id = ?');
        $actual->execute([$id]);
        $prev = $actual->fetch();

        if (!$prev || !puedeEditarContenido((int) $prev['usuario_id'])) {
            flash('danger', 'No tienes permiso para editar este episodio.');
            redirect('podcasts.php');
        }

        $stmt = $pdo->prepare('UPDATE podcasts SET titulo=?, url_embed=?, fecha_publicacion=? WHERE id=?');
        $stmt->execute([$titulo, $urlEmbed, $fecha, $id]);
        flash('success', 'Episodio actualizado.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO podcasts (titulo, url_embed, fecha_publicacion, usuario_id) VALUES (?,?,?,?)');
        $stmt->execute([$titulo, $urlEmbed, $fecha, (int) $_SESSION['usuario_id']]);
        flash('success', 'Episodio publicado.');
    }
    redirect('podcasts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id   = (int) $_POST['id'];
    $fila = $pdo->prepare('SELECT usuario_id FROM podcasts WHERE id = ?');
    $fila->execute([$id]);
    $dueno = $fila->fetch();

    if (!$dueno || !puedeEliminarContenido((int) $dueno['usuario_id'])) {
        flash('danger', 'No tienes permiso para eliminar este episodio.');
        redirect('podcasts.php');
    }

    $pdo->prepare('DELETE FROM podcasts WHERE id = ?')->execute([$id]);
    flash('success', 'Episodio eliminado.');
    redirect('podcasts.php');
}

$podcasts = $pdo->query(
    "SELECT p.*, CONCAT(u.nombres,' ',u.ap_paterno) AS editor_nombre
       FROM podcasts p JOIN usuarios u ON u.id = p.usuario_id
      ORDER BY p.fecha_publicacion DESC"
)->fetchAll();

$pageTitle    = 'Podcasts';
$pageSubtitle = 'Episodios de audio publicados';
$activeMenu   = 'podcasts';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" onclick="nuevoRegistro()"><i class="fa fa-plus mr-1"></i> Nuevo episodio</button>
</div>
<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="podcasts-table" class="table table-striped table-hover overflow-auto" width="100%">
            <thead><tr><th>Título</th><th>Audio</th><th>Fecha</th><th>Publicado por</th><th class="no-sort">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($podcasts as $p): ?>
                <tr>
                    <td><?= e($p['titulo']) ?></td>
                    <td><a href="<?= e($p['url_embed']) ?>" target="_blank" rel="noopener">Escuchar</a></td>
                    <td><?= formatFecha($p['fecha_publicacion']) ?></td>
                    <td><?= e($p['editor_nombre']) ?></td>
                    <td class="text-right text-nowrap">
                        <?php if (puedeEditarContenido((int) $p['usuario_id'])): ?>
                            <button class="btn btn-icon btn-sm" title="Editar" onclick='editarRegistro(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fa fa-pencil"></i></button>
                        <?php endif; ?>
                        <?php if (puedeEliminarContenido((int) $p['usuario_id'])): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este episodio?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-icon btn-sm text-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                            </form>
                        <?php endif; ?>
                        <?php if (!puedeEditarContenido((int) $p['usuario_id']) && !puedeEliminarContenido((int) $p['usuario_id'])): ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($podcasts)): ?><tr><td colspan="5" class="text-center text-muted">Sin episodios registrados aún.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>$(function(){ $('#podcasts-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="podcasts.php">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="f-id">
        <div class="modal-header">
          <h5 class="modal-title" id="f-titulo-modal">Nuevo episodio</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 form-group"><label>Título del episodio</label><input type="text" name="titulo" id="f-titulo" class="form-control" placeholder="Título" required></div>
            <div class="col-md-12 form-group"><label>Enlace de audio</label><input type="url" name="url_embed" id="f-url" class="form-control" placeholder="https://..." required></div>
            <div class="col-md-6 form-group"><label>Fecha</label><input type="date" name="fecha_publicacion" id="f-fecha" class="form-control" required></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="f-submit">Guardar episodio</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoRegistro() {
    document.getElementById('f-titulo-modal').textContent = 'Nuevo episodio';
    document.getElementById('f-submit').textContent = 'Guardar episodio';
    document.getElementById('f-id').value = '';
    document.getElementById('f-titulo').value = '';
    document.getElementById('f-url').value = '';
    document.getElementById('f-fecha').value = '';
    $('#formModal').modal('show');
}
function editarRegistro(p) {
    document.getElementById('f-titulo-modal').textContent = 'Editar episodio';
    document.getElementById('f-submit').textContent = 'Guardar cambios';
    document.getElementById('f-id').value = p.id;
    document.getElementById('f-titulo').value = p.titulo || '';
    document.getElementById('f-url').value = p.url_embed || '';
    document.getElementById('f-fecha').value = p.fecha_publicacion;
    $('#formModal').modal('show');
}
</script>

</body>
</html>
