<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/upload.php';
requireLogin();

$pdo          = getPDO();
$reportajeId  = (int) ($_GET['reportaje_id'] ?? $_POST['reportaje_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM reportajes WHERE id = ?');
$stmt->execute([$reportajeId]);
$reportaje = $stmt->fetch();

if (!$reportaje) {
    flash('danger', 'Ese reportaje no existe.');
    redirect('reportajes.php');
}

if (!puedeEditarContenido((int) $reportaje['usuario_id'])) {
    flash('danger', 'No tienes permiso para gestionar las fotos de este reportaje.');
    redirect('reportajes.php');
}

// --- Agregar foto -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar') {
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $orden       = (int) ($_POST['orden'] ?? 0);
    $ruta        = subirArchivo('foto', 'fotos', ['jpg', 'jpeg', 'png', 'webp']);

    if (!$ruta) {
        flash('danger', 'Selecciona una foto válida (jpg, png o webp).');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO reportajes_fotos (reportaje_id, url_foto, orden, descripcion) VALUES (?,?,?,?)'
        );
        $stmt->execute([$reportajeId, $ruta, $orden, $descripcion]);
        flash('success', 'Foto agregada.');
    }
    redirect('reportajes_fotos.php?reportaje_id=' . $reportajeId);
}

// --- Eliminar foto --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $fotoId = (int) $_POST['foto_id'];
    $pdo->prepare('DELETE FROM reportajes_fotos WHERE id = ? AND reportaje_id = ?')->execute([$fotoId, $reportajeId]);
    flash('success', 'Foto eliminada.');
    redirect('reportajes_fotos.php?reportaje_id=' . $reportajeId);
}

$fotos = $pdo->prepare('SELECT * FROM reportajes_fotos WHERE reportaje_id = ? ORDER BY orden, id');
$fotos->execute([$reportajeId]);
$fotos = $fotos->fetchAll();

$pageTitle    = 'Fotos del reportaje';
$pageSubtitle = $reportaje['titulo'];
$activeMenu   = 'reportajes';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="mb-3">
    <a href="reportajes.php" class="btn btn-default btn-sm"><i class="fa fa-arrow-left mr-1"></i> Volver a reportajes</a>
</div>

<div class="row">
    <div class="col-md-5">
        <section class="widget">
            <header><h6>Agregar foto</h6></header>
            <div class="widget-body">
                <form method="post" action="reportajes_fotos.php" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="reportaje_id" value="<?= $reportajeId ?>">
                    <div class="form-group"><label>Foto</label><input type="file" name="foto" class="form-control-file" accept=".jpg,.jpeg,.png,.webp" required></div>
                    <div class="form-group"><label>Descripción / pie de foto</label><input type="text" name="descripcion" class="form-control" placeholder="Opcional"></div>
                    <div class="form-group"><label>Orden</label><input type="number" name="orden" class="form-control" value="<?= count($fotos) ?>"></div>
                    <button class="btn btn-primary btn-block">Agregar foto</button>
                </form>
            </div>
        </section>
    </div>
    <div class="col-md-7">
        <section class="widget">
            <header><h6>Galería (<?= count($fotos) ?>)</h6></header>
            <div class="widget-body">
                <?php if (empty($fotos)): ?>
                    <p class="text-muted">Este reportaje aún no tiene fotos adicionales.</p>
                <?php endif; ?>
                <div class="row">
                <?php foreach ($fotos as $f): ?>
                    <div class="col-6 mb-3">
                        <div class="card">
                            <img src="<?= e($f['url_foto']) ?>" class="card-img-top" alt="" style="height:120px;object-fit:cover;">
                            <div class="card-body p-2">
                                <p class="small mb-1"><?= e($f['descripcion'] ?: '(sin descripción)') ?></p>
                                <form method="post" onsubmit="return confirm('¿Eliminar esta foto?');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="reportaje_id" value="<?= $reportajeId ?>">
                                    <input type="hidden" name="foto_id" value="<?= (int) $f['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-block">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
</body>
</html>
