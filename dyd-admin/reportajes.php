<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/upload.php';
requireLogin();

$pdo = getPDO();

// --- Guardar (crear o editar) ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $id            = $_POST['id'] ?: null;
    $titulo        = trim($_POST['titulo'] ?? '');
    $resumenCorto  = trim($_POST['resumen_corto'] ?? '') ?: null;
    $desarrollo    = trim($_POST['desarrollo'] ?? '');
    $fecha         = $_POST['fecha_publicacion'] ?? '';
    $autorId       = (int) ($_POST['autor_id'] ?? 0);
    $esDestacado   = isset($_POST['es_destacado']) ? 1 : 0;

    if ($titulo === '' || $desarrollo === '' || $fecha === '' || $autorId === 0) {
        flash('danger', 'Título, contenido, fecha y autor (usa "Redacción" si no tiene autor externo) son obligatorios.');
        redirect('reportajes.php');
    }

    $rutaFoto = subirArchivo('foto_principal', 'fotos', ['jpg', 'jpeg', 'png', 'webp']);
    $rutaPdf  = subirArchivo('pdf_adjunto', 'pdfs', ['pdf']);
    if ($rutaFoto === false || $rutaPdf === false) {
        flash('danger', 'La foto debe ser jpg/png/webp y el adjunto debe ser un PDF.');
        redirect('reportajes.php');
    }

    // Solo puede haber UN reportaje destacado (portada) a la vez.
    if ($esDestacado) {
        $pdo->exec('UPDATE reportajes SET es_destacado = 0');
    }

    if ($id) {
        // Conservamos la foto/pdf actuales si no se subió uno nuevo.
        $actual = $pdo->prepare('SELECT foto_principal, pdf_adjunto, usuario_id FROM reportajes WHERE id = ?');
        $actual->execute([$id]);
        $prev = $actual->fetch();

        if (!$prev || !puedeEditarContenido((int) $prev['usuario_id'])) {
            flash('danger', 'No tienes permiso para editar este reportaje.');
            redirect('reportajes.php');
        }

        $rutaFoto = $rutaFoto ?: $prev['foto_principal'];
        $rutaPdf  = $rutaPdf ?: $prev['pdf_adjunto'];

        $stmt = $pdo->prepare(
            'UPDATE reportajes SET titulo=?, resumen_corto=?, desarrollo=?, foto_principal=?, pdf_adjunto=?,
                    fecha_publicacion=?, es_destacado=?, autor_id=? WHERE id=?'
        );
        $stmt->execute([$titulo, $resumenCorto, $desarrollo, $rutaFoto, $rutaPdf, $fecha, $esDestacado, $autorId, $id]);
        flash('success', 'Reportaje actualizado.');
    } else {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $stmt = $pdo->prepare(
            'INSERT INTO reportajes (titulo, resumen_corto, desarrollo, foto_principal, pdf_adjunto,
                    fecha_publicacion, es_destacado, autor_id, usuario_id)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([$titulo, $resumenCorto, $desarrollo, $rutaFoto, $rutaPdf, $fecha, $esDestacado, $autorId, $usuarioId]);
        flash('success', 'Reportaje publicado.');
    }
    redirect('reportajes.php');
}

// --- Eliminar ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int) $_POST['id'];

    $fila = $pdo->prepare('SELECT usuario_id FROM reportajes WHERE id = ?');
    $fila->execute([$id]);
    $dueno = $fila->fetch();

    if (!$dueno || !puedeEliminarContenido((int) $dueno['usuario_id'])) {
        flash('danger', 'No tienes permiso para eliminar este reportaje.');
        redirect('reportajes.php');
    }

    // Las fotos adicionales (reportajes_fotos) se borran solas por ON DELETE CASCADE.
    $pdo->prepare('DELETE FROM reportajes WHERE id = ?')->execute([$id]);
    flash('success', 'Reportaje eliminado.');
    redirect('reportajes.php');
}

// --- Datos para listado y formulario -----------------------------------
$reportajes = $pdo->query(
    "SELECT r.*, CONCAT(a.nombres,' ',IFNULL(a.ap_paterno,'')) AS autor_nombre,
            CONCAT(u.nombres,' ',u.ap_paterno) AS editor_nombre
       FROM reportajes r
       LEFT JOIN autores a ON a.id = r.autor_id
       LEFT JOIN usuarios u ON u.id = r.usuario_id
      ORDER BY r.fecha_publicacion DESC"
)->fetchAll();

$autores = $pdo->query('SELECT id, nombres, ap_paterno, nickname, es_nickname FROM autores ORDER BY nombres')->fetchAll();

$pageTitle    = 'Reportajes';
$pageSubtitle = 'Investigaciones y reportajes publicados';
$activeMenu   = 'reportajes';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" onclick="nuevoReportaje()">
        <i class="fa fa-plus mr-1"></i> Nuevo reportaje
    </button>
</div>
<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="reportajes-table" class="table table-striped table-hover" width="100%">
            <thead><tr><th>Título</th><th>Autor</th><th>Fecha</th><th>Editor</th><th>Destacado</th><th class="no-sort">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($reportajes as $r): ?>
                <tr>
                    <td><?= e($r['titulo']) ?></td>
                    <td><?= e(trim($r['autor_nombre']) ?: '—') ?></td>
                    <td><?= formatFecha($r['fecha_publicacion']) ?></td>
                    <td><?= e($r['editor_nombre']) ?></td>
                    <td><?= $r['es_destacado'] ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-light">No</span>' ?></td>
                    <td class="text-right text-nowrap">
                        <?php if (puedeEditarContenido((int) $r['usuario_id'])): ?>
                            <a class="btn btn-icon btn-sm" href="reportajes_fotos.php?reportaje_id=<?= (int) $r['id'] ?>" title="Fotos adicionales"><i class="fa fa-picture-o"></i></a>
                            <button class="btn btn-icon btn-sm" title="Editar"
                                onclick='editarReportaje(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fa fa-pencil"></i>
                            </button>
                        <?php endif; ?>
                        <?php if (puedeEliminarContenido((int) $r['usuario_id'])): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este reportaje? También se borrarán sus fotos adicionales.');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button class="btn btn-icon btn-sm text-danger" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                            </form>
                        <?php endif; ?>
                        <?php if (!puedeEditarContenido((int) $r['usuario_id']) && !puedeEliminarContenido((int) $r['usuario_id'])): ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($reportajes)): ?>
                <tr><td colspan="6" class="text-center text-muted">Aún no hay reportajes.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>
  $(function(){ $('#reportajes-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });
</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/lang/summernote-es-ES.min.js"></script>

<style>
/* --- Reportajes: ajustes responsivos + preview de foto principal --- */
.foto-preview-box{
    position: relative;
    width: 100%;
    max-width: 260px;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 10px;
    background: var(--dark, #121529);
    border: 1px solid rgba(255,255,255,.08);
}
.foto-preview-box img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
/* la "cosita roja" que pide el docente, arriba a la derecha de la foto */
.foto-preview-box::after{
    content: '';
    position: absolute;
    top: -47.5%;
    right: -32%;
    width: 66.7%;
    aspect-ratio: 1 / 1;
    background: #dc030c;
    border-radius: 50%;
    pointer-events: none;
}
@media (max-width: 575.98px){
    .modal-dialog{ margin: .5rem; }
}
/* Scroll garantizado dentro del modal: así, sin importar cuántos campos
   tenga el formulario (o el tamaño de pantalla), siempre se puede
   bajar y ver/marcar todo, incluido "Destacado". */
#formModal .modal-body{
    max-height: 70vh;
    overflow-y: auto;
}
/* el editor de "Contenido" queda dentro de la misma columna/ancho del textarea original */
.note-editor.note-frame{ background: #fff; }
</style>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <form method="post" action="reportajes.php" enctype="multipart/form-data" onsubmit="return validarContenido()">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" id="f-id">
        <div class="modal-header">
          <h5 class="modal-title" id="f-titulo-modal">Nuevo reportaje</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 form-group"><label>Título</label><input type="text" name="titulo" id="f-titulo" class="form-control" placeholder="Título del reportaje" required></div>
            <div class="col-md-6 form-group">
                <label>Autor</label>
                <select name="autor_id" id="f-autor_id" class="form-control" required>
                    <?php foreach ($autores as $a): ?>
                        <option value="<?= (int) $a['id'] ?>">
                            <?= e(trim($a['nombres'] . ' ' . $a['ap_paterno'])) ?>
                            <?= ($a['es_nickname'] && $a['nickname']) ? '(' . e($a['nickname']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">¿Sin autor externo? Elige "Redacción" (créalo una vez en Autores).</small>
            </div>
            <div class="col-md-6 form-group"><label>Fecha</label><input type="date" name="fecha_publicacion" id="f-fecha" class="form-control" required></div>
            <div class="col-md-12 form-group">
              <div class="form-check p-3 border rounded d-flex align-items-center" style="background: rgba(255,255,255,.03);">
                <input type="checkbox" class="form-check-input" name="es_destacado" id="f-destacado" value="1">
                <label class="form-check-label mb-0 ml-1" for="f-destacado"><i class="fa fa-star text-warning mr-1"></i> Marcar como destacado (portada)</label>
              </div>
            </div>
            <div class="col-md-12 form-group"><label>Resumen</label><textarea name="resumen_corto" id="f-resumen" class="form-control" rows="2" placeholder="Síntesis breve"></textarea></div>
            <div class="col-md-12 form-group"><label>Contenido</label><textarea name="desarrollo" id="f-desarrollo" class="form-control" rows="5" placeholder="Contenido completo"></textarea></div>
            <div class="col-md-6 form-group">
                <label>Foto principal</label>
                <input type="file" name="foto_principal" id="f-foto" class="form-control-file" accept=".jpg,.jpeg,.png,.webp" onchange="previsualizarFoto(this)">
                <div class="foto-preview-box mt-2" id="f-foto-preview" style="display:none;">
                    <img id="f-foto-preview-img" src="" alt="Vista previa de la foto principal">
                </div>
            </div>
            <div class="col-md-6 form-group"><label>PDF adjunto</label><input type="file" name="pdf_adjunto" class="form-control-file" accept=".pdf"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="f-submit">Publicar reportaje</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function () {
    $('#f-desarrollo').summernote({
        lang: 'es-ES',
        height: 220,
        placeholder: 'Contenido completo del reportaje...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });
});

// El textarea de Contenido queda oculto por Summernote, así que "required"
// no sirve (el navegador no puede enfocar un campo escondido). Validamos
// a mano antes de enviar el formulario.
function validarContenido() {
    if ($('#f-desarrollo').summernote('isEmpty')) {
        alert('El contenido del reportaje no puede quedar vacío.');
        return false;
    }
    return true;
}

function nuevoReportaje() {
    document.getElementById('f-titulo-modal').textContent = 'Nuevo reportaje';
    document.getElementById('f-submit').textContent = 'Publicar reportaje';
    document.getElementById('f-id').value = '';
    document.getElementById('f-titulo').value = '';
    document.getElementById('f-autor_id').selectedIndex = 0;
    document.getElementById('f-fecha').value = '';
    document.getElementById('f-resumen').value = '';
    $('#f-desarrollo').summernote('code', '');
    document.getElementById('f-destacado').checked = false;
    document.getElementById('f-foto').value = '';
    ocultarPreviewFoto();
    $('#formModal').modal('show');
}
function editarReportaje(r) {
    document.getElementById('f-titulo-modal').textContent = 'Editar reportaje';
    document.getElementById('f-submit').textContent = 'Guardar cambios';
    document.getElementById('f-id').value = r.id;
    document.getElementById('f-titulo').value = r.titulo || '';
    document.getElementById('f-autor_id').value = r.autor_id;
    document.getElementById('f-fecha').value = r.fecha_publicacion;
    document.getElementById('f-resumen').value = r.resumen_corto || '';
    $('#f-desarrollo').summernote('code', r.desarrollo || '');
    document.getElementById('f-destacado').checked = !!Number(r.es_destacado);
    document.getElementById('f-foto').value = '';
    if (r.foto_principal) {
        mostrarPreviewFoto(r.foto_principal);
    } else {
        ocultarPreviewFoto();
    }
    $('#formModal').modal('show');
}
// Muestra la vista previa (con la marca roja) al elegir un archivo nuevo
function previsualizarFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) { mostrarPreviewFoto(e.target.result); };
        reader.readAsDataURL(input.files[0]);
    } else {
        ocultarPreviewFoto();
    }
}
function mostrarPreviewFoto(src) {
    document.getElementById('f-foto-preview-img').src = src;
    document.getElementById('f-foto-preview').style.display = 'block';
}
function ocultarPreviewFoto() {
    document.getElementById('f-foto-preview').style.display = 'none';
    document.getElementById('f-foto-preview-img').src = '';
}
</script>

</body>
</html>
