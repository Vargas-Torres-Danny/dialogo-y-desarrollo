<?php
/**
 * PARTIAL: Reportajes recientes.
 * Úsalo así en tu página, EN EL LUGAR donde estaban las 4 tarjetas
 * de reportajes copiadas a mano en el HTML original:
 *
 *   <?php $limiteReportajes = 4; include __DIR__ . '/partials/reportajes-home.php'; ?>
 *
 * Dentro de este archivo hay un <?php foreach (...) ?> — pega tu tarjeta
 * de diseño (la del HTML del docente) UNA sola vez ahí adentro,
 * reemplazando texto fijo por las variables de PHP ($r['titulo'], etc).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$limiteReportajes = $limiteReportajes ?? 4;

$pdo = getPDO();
$stmt = $pdo->prepare(
    "SELECT r.*, CONCAT(a.nombres,' ',IFNULL(a.ap_paterno,'')) AS autor_nombre
       FROM reportajes r
       LEFT JOIN autores a ON a.id = r.autor_id
      ORDER BY r.fecha_publicacion DESC
      LIMIT ?"
);
$stmt->bindValue(1, $limiteReportajes, PDO::PARAM_INT);
$stmt->execute();
$reportajesHome = $stmt->fetchAll();
?>

<?php foreach ($reportajesHome as $r): ?>
    <!--
      ================================================================
      TODO: reemplaza este bloque de ejemplo por TU tarjeta de diseño
      real (la del HTML que copiaste), cambiando solo el texto fijo
      por las variables de abajo. Bórralo cuando termines.
      ================================================================
    -->
    <div class="reportaje-card">
        <a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>">
            <img src="<?= e(imagenUrl($r['foto_principal'])) ?>" alt="<?= e($r['titulo']) ?>">
        </a>
        <span class="fecha"><?= formatFechaSitio($r['fecha_publicacion']) ?></span>
        <h3><a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>"><?= e($r['titulo']) ?></a></h3>
        <p><?= e($r['resumen_corto']) ?></p>
        <a class="btn-leer" href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>">Leer</a>
    </div>
<?php endforeach; ?>

<?php if (empty($reportajesHome)): ?>
    <p>Aún no hay reportajes publicados.</p>
<?php endif; ?>
