<?php
/**
 * PARTIAL: Último boletín NTEP.
 * Uso: <?php include __DIR__ . '/partials/boletin-home.php'; ?>
 * Deja disponible la variable $boletinReciente (o null si no hay ninguno).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getPDO();
$boletinReciente = $pdo->query(
    'SELECT * FROM boletines ORDER BY fecha_publicacion DESC LIMIT 1'
)->fetch();
?>

<?php if ($boletinReciente): ?>
    <!-- TODO: reemplaza por tu bloque de diseño real del Boletín NTEP -->
    <div class="boletin-card">
        <img src="<?= e(imagenUrl($boletinReciente['foto_portada'])) ?>" alt="Boletín N° <?= e($boletinReciente['numero_boletin']) ?>">
        <p><?= e($boletinReciente['resumen']) ?></p>
        <span>Nº <?= e($boletinReciente['numero_boletin']) ?></span>
        <span><?= formatFechaSitio($boletinReciente['fecha_publicacion']) ?></span>
        <a class="btn-ver" href="<?= e(imagenUrl($boletinReciente['archivo_pdf'])) ?>" target="_blank">Ver Boletín</a>
    </div>
<?php else: ?>
    <p>Aún no hay boletines publicados.</p>
<?php endif; ?>
