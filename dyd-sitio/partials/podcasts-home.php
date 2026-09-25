<?php
/**
 * PARTIAL: Episodios de podcast recientes.
 * Uso: <?php $limitePodcasts = 4; include __DIR__ . '/partials/podcasts-home.php'; ?>
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$limitePodcasts = $limitePodcasts ?? 4;

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT * FROM podcasts ORDER BY fecha_publicacion DESC LIMIT ?');
$stmt->bindValue(1, $limitePodcasts, PDO::PARAM_INT);
$stmt->execute();
$podcastsHome = $stmt->fetchAll();
?>

<?php foreach ($podcastsHome as $p): ?>
    <!-- TODO: reemplaza por tu tarjeta de diseño real -->
    <div class="podcast-card">
        <img src="assets/img/podcast.png" alt="">
        <p><?= e($p['titulo']) ?></p>
        <a href="<?= e($p['url_embed']) ?>" target="_blank" rel="noopener">Escuchar</a>
    </div>
<?php endforeach; ?>

<?php if (empty($podcastsHome)): ?>
    <p>Aún no hay episodios publicados.</p>
<?php endif; ?>
