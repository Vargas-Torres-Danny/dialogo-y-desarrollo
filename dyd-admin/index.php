<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();

$pdo = getPDO();

$totalReportajes = (int) $pdo->query('SELECT COUNT(*) FROM reportajes')->fetchColumn();
$totalNoticias   = (int) $pdo->query('SELECT COUNT(*) FROM noticias')->fetchColumn();
$totalBoletines  = (int) $pdo->query('SELECT COUNT(*) FROM boletines')->fetchColumn();
$totalUsuarios   = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

// Actividad reciente: unimos las 5 últimas filas de cada módulo de contenido.
$actividad = $pdo->query("
    (SELECT 'Reportaje' AS tipo, r.titulo, CONCAT(a.nombres,' ',IFNULL(a.ap_paterno,'')) AS autor,
            r.fecha_publicacion AS fecha, r.id, 'reportajes' AS modulo
       FROM reportajes r LEFT JOIN autores a ON a.id = r.autor_id)
    UNION ALL
    (SELECT 'Noticia', n.titulo, CONCAT(u.nombres,' ',u.ap_paterno), n.fecha_publicacion, n.id, 'noticias'
       FROM noticias n JOIN usuarios u ON u.id = n.usuario_id)
    UNION ALL
    (SELECT 'Boletín', CONCAT('Boletín N° ', b.numero_boletin), CONCAT(u.nombres,' ',u.ap_paterno), b.fecha_publicacion, b.id, 'boletines'
       FROM boletines b JOIN usuarios u ON u.id = b.usuario_id)
    UNION ALL
    (SELECT 'Podcast', p.titulo, CONCAT(u.nombres,' ',u.ap_paterno), p.fecha_publicacion, p.id, 'podcasts'
       FROM podcasts p JOIN usuarios u ON u.id = p.usuario_id)
    UNION ALL
    (SELECT 'Video', v.titulo, CONCAT(u.nombres,' ',u.ap_paterno), v.fecha_publicacion, v.id, 'videos'
       FROM videos v JOIN usuarios u ON u.id = v.usuario_id)
    ORDER BY fecha DESC
    LIMIT 10
")->fetchAll();

$pageTitle    = 'Resumen';
$pageSubtitle = 'Vista general de la actividad de contenidos';
$activeMenu   = 'index';
require __DIR__ . '/includes/layout_top.php';
?>

<div class="row">
    <div class="col-md-3">
        <section class="widget widget-sm bg-primary text-white">
            <div class="widget-body">
                <p class="mb-xs"><i class="fi flaticon-newspaper fa-2x"></i></p>
                <h3><?= $totalReportajes ?></h3>
                <p class="fs-mini mt">Reportajes publicados</p>
            </div>
        </section>
    </div>
    <div class="col-md-3">
        <section class="widget widget-sm bg-success text-white">
            <div class="widget-body">
                <p class="mb-xs"><i class="fi flaticon-megaphone fa-2x"></i></p>
                <h3><?= $totalNoticias ?></h3>
                <p class="fs-mini mt">Noticias registradas</p>
            </div>
        </section>
    </div>
    <div class="col-md-3">
        <section class="widget widget-sm bg-warning text-white">
            <div class="widget-body">
                <p class="mb-xs"><i class="fi flaticon-paper-plane fa-2x"></i></p>
                <h3><?= $totalBoletines ?></h3>
                <p class="fs-mini mt">Boletines emitidos</p>
            </div>
        </section>
    </div>
    <div class="col-md-3">
        <section class="widget widget-sm bg-danger text-white">
            <div class="widget-body">
                <p class="mb-xs"><i class="fi flaticon-users fa-2x"></i></p>
                <h3><?= $totalUsuarios ?></h3>
                <p class="fs-mini mt">Usuarios registrados</p>
            </div>
        </section>
    </div>
</div>
<h5 class="mt-2 mb-3">Actividad reciente</h5>

<section class="widget">
    <div class="widget-body">
        <div class="table-responsive">
        <table id="recent-table" class="table table-striped table-hover overflow-auto" width="100%">
            <thead><tr><th>Tipo</th><th>Título</th><th>Autor / Editor</th><th>Fecha</th><th class="no-sort">Ir a</th></tr></thead>
            <tbody>
            <?php foreach ($actividad as $a): ?>
                <tr>
                    <td><?= e($a['tipo']) ?></td>
                    <td><?= e($a['titulo']) ?></td>
                    <td><?= e(trim($a['autor']) ?: 'Redacción') ?></td>
                    <td><?= formatFecha($a['fecha']) ?></td>
                    <td class="text-right text-nowrap">
                        <a class="btn btn-icon btn-sm" href="<?= e($a['modulo']) ?>.php" title="Ver módulo"><i class="fa fa-arrow-right"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($actividad)): ?>
                <tr><td colspan="5" class="text-center text-muted">Aún no hay contenido registrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<script>
  $(function(){ $('#recent-table').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' } }); });
</script>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
</body>
</html>
