<?php
/**
 * PARTIAL: Noticias recientes.
 * Uso: <?php $limiteNoticias = 3; include __DIR__ . '/partials/noticias-home.php'; ?>
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$limiteNoticias = $limiteNoticias ?? 3;

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT * FROM noticias ORDER BY fecha_publicacion DESC LIMIT ?');
$stmt->bindValue(1, $limiteNoticias, PDO::PARAM_INT);
$stmt->execute();
$noticiasHome = $stmt->fetchAll();
?>

<?php foreach ($noticiasHome as $n): ?>
    <!-- TODO: reemplaza por tu tarjeta de diseño real -->
    <div class="noticia-card">
        <a href="<?= e($n['link_externo']) ?>" target="_blank" rel="noopener">
            <img src="<?= e(imagenUrl($n['foto'])) ?>" alt="<?= e($n['titulo']) ?>">
        </a>
        <span class="fecha"><?= formatFechaSitio($n['fecha_publicacion']) ?></span>
        <h4><a href="<?= e($n['link_externo']) ?>" target="_blank" rel="noopener"><?= e($n['titulo']) ?></a></h4>
        <a class="btn-leer" href="<?= e($n['link_externo']) ?>" target="_blank" rel="noopener">Leer</a>
    </div>
<?php endforeach; ?>

<?php if (empty($noticiasHome)): ?>
    <p>Aún no hay noticias registradas.</p>
<?php endif; ?>
