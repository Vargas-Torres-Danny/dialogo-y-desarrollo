<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getPDO();
$id  = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT r.*, CONCAT(a.nombres,' ',IFNULL(a.ap_paterno,'')) AS autor_nombre
       FROM reportajes r
       LEFT JOIN autores a ON a.id = r.autor_id
      WHERE r.id = ?"
);
$stmt->execute([$id]);
$reportaje = $stmt->fetch();

if (!$reportaje) {
    http_response_code(404);
    $pageTitle404 = 'Reportaje no encontrado';
}

// Fotos adicionales de la galería (tabla reportajes_fotos)
$fotos = [];
if ($reportaje) {
    $fs = $pdo->prepare('SELECT * FROM reportajes_fotos WHERE reportaje_id = ? ORDER BY orden, id');
    $fs->execute([$id]);
    $fotos = $fs->fetchAll();
}

// Sidebar: últimos reportajes (excluyendo el actual)
$ultimos = $pdo->prepare(
    'SELECT id, titulo, fecha_publicacion FROM reportajes WHERE id <> ? ORDER BY fecha_publicacion DESC LIMIT 5'
);
$ultimos->execute([$id]);
$ultimos = $ultimos->fetchAll();

// Sidebar: archivos por mes (meses con al menos un reportaje)
$archivos = $pdo->query(
    "SELECT DATE_FORMAT(fecha_publicacion, '%Y-%m') AS ym, COUNT(*) AS total
       FROM reportajes
      GROUP BY ym
      ORDER BY ym DESC
      LIMIT 12"
)->fetchAll();
$mesesEs = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
            '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
?>
<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $reportaje ? 'DyD Perú | ' . e($reportaje['titulo']) : 'Reportaje no encontrado' ?></title>

    <!-- Google fonts -->
    
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    
    <!-- Template CSS -->
    
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <link rel="stylesheet" href="assets/css/custom.css">
  </head>
  <body>

<!-- header -->
<header id="site-header" class="fixed-top">
  <div class="container">
      <nav class="navbar navbar-expand-lg stroke">
      <a class="navbar-brand" href="#index.html">
          <img src="assets/images/logo.png" alt="Your logo" title="Your logo" style="height:75px;" />
      </a> 
          <button class="navbar-toggler  collapsed bg-gradient" type="button" data-toggle="collapse"
              data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
              <span class="navbar-toggler-icon fa icon-close fa-times"></span>
              </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
              <ul class="navbar-nav ml-auto">
                  <li class="nav-item">
                      <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="index.php#actualidad">Actualidad</a>
                  </li>
                  <li class="nav-item active">
                      <a class="nav-link" href="reportajes.php">Reportajes</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="podcasts.php">Podcast</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="boletines.php">Boletín NTEP</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="alianzas.php">Alianzas</a>
                  </li>
                  <li class="nav-item @@contact__active">
                      <a class="nav-link" href="sobre-dyd.php">Sobre D&amp;D</a>
                  </li>               
                  <li class="ml-2">
                      <a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a>
                  </li>
              </ul>
          </div>
      </nav>
  </div>
</header>
<!-- //header -->

<section class="breadcrumb-area py-sm-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big"><?= $reportaje ? 'Reportajes' : 'No encontrado' ?></h2>
                    <div class="breadcrumb">
                        <ul>
                            <li><a href="index.php">Inicio</a></li>
                            <li><a href="reportajes.php">Reportajes</a></li>
                            <?php if ($reportaje): ?>
                                <li class="active"><?= e(mb_strimwidth($reportaje['titulo'], 0, 40, '…')) ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!$reportaje): ?>
<section class="py-5 text-center">
    <div class="container py-5">
        <h3>No encontramos este reportaje.</h3>
        <p><a href="reportajes.php" class="btn btn-style btn-primary mt-3">Volver a reportajes</a></p>
    </div>
</section>
<?php else: ?>

<section class="w3l-blog mt-lg-5">
    <div class="text-element-9 py-5 mt-lg-5">
        <div class="container py-lg-3">
            <div class="row grid-text-9">
                <div class="col-lg-8">
                    <div class="blog-single-post">
                        <div class="post-content">
                            <h2 class="title-single mb-3"><?= e($reportaje['titulo']) ?></h2>
                        </div>
                        <div class="blo-singl mb-4">
                            <ul class="blog-single-author-date d-flex align-items-center">
                                <li><?= formatFechaSitio($reportaje['fecha_publicacion']) ?></li>
                                <li class="ml-3">Por <?= e(trim($reportaje['autor_nombre']) ?: 'Redacción') ?></li>
                            </ul>
                        </div>
                        <?php if ($reportaje['foto_principal']): ?>
                        <div class="single-post-image mb-4 text-center corner-ribbon">
                            <img src="<?= e(imagenUrl($reportaje['foto_principal'])) ?>" class="img-fluid w-100 radius-image" alt="<?= e($reportaje['titulo']) ?>" />
                        </div>
                        <?php endif; ?>
                        <div class="single-post-content">
                            <?= nl2br(e($reportaje['desarrollo'])) ?>
                        </div>

                        <?php if (!empty($fotos)): ?>
                        <div class="row mt-5">
                            <?php foreach ($fotos as $f): ?>
                                <div class="col-md-6 mb-4">
                                    <img src="<?= e(imagenUrl($f['url_foto'])) ?>" class="img-fluid radius-image" alt="<?= e($f['descripcion']) ?>">
                                    <?php if ($f['descripcion']): ?><p class="small text-muted mt-2"><?= e($f['descripcion']) ?></p><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($reportaje['pdf_adjunto']): ?>
                            <p class="mt-4">
                                <a target="_blank" href="<?= e(imagenUrl($reportaje['pdf_adjunto'])) ?>" class="btn btn-style btn-primary">Ver PDF adjunto</a>
                            </p>
                        <?php endif; ?>

                        <nav class="post-navigation row mb-5 py-4">
                            <div class="post-prev col-md-6 pr-sm-5">
                                <span class="nav-title"><span class="fa fa-arrow-left mr-2"></span> <a href="reportajes.php">Reportajes</a></span>
                            </div>
                        </nav>
                    </div>
                </div>

                <!--Ultimas Noticias-->
                <div class="col-lg-4 left-text-9 mt-lg-0 mt-5 pl-lg-4">
                    <div class="left-top-9 mt-5 pt-sm-3">
                        <h6 class="heading-small-text-9 mb-3">Últimas noticias</h6>
                        <?php foreach ($ultimos as $u): ?>
                        <a href="reportaje-detalle.php?id=<?= (int) $u['id'] ?>" class="p-post d-block py-2">
                            <h6 class="text-left-inner-9"><?= e($u['titulo']) ?></h6>
                            <span class="sub-inner-text-9"><?= formatFechaSitio($u['fecha_publicacion']) ?></span>
                        </a>
                        <?php endforeach; ?>
                        <?php if (empty($ultimos)): ?>
                            <p class="small text-muted">No hay más reportajes todavía.</p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($archivos)): ?>
                    <div class="categories mt-5 pt-sm-3">
                        <h6 class="heading-small-text-9">Archivos</h6>
                        <ul>
                            <?php foreach ($archivos as $a): [$anio, $mes] = explode('-', $a['ym']); ?>
                            <li>
                                <a href="reportajes.php?mes=<?= e($a['ym']) ?>"><?= e($mesesEs[$mes] ?? $mes) ?> <?= e($anio) ?> (<?= (int) $a['total'] ?>)</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <!--Fin Ultimas Noticias-->
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<section class="w3l-footer-29-main py-5" id="footer">
  <div class="footer-29 py-md-3">
    <div class="container">
      <div class="row footer-top-29">
        <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
          <h6 class="footer-title-29">Quiénes Somos</h6>
          <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
          <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokp.png"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram"></span></a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
          <ul>
            <h6 class="footer-title-29">Contenido</h6>
            <li><a href="reportajes.php">Reportajes</a></li>
            <li><a href="boletines.php">Boletín NTEP</a></li>
            <li><a href="podcasts.php">Podcast</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
          <div class="properties">
            <h6 class="footer-title-29">Contacto</h6>
            <ul>
            <li><a href="#url">info@dialogoydesarrollo.com.pe</a></li>
          </ul>
          </div>
        </div>
      </div>
        <div class="bottom-copies text-center">
            <p class="copy-footer-29">© 2026 Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" href="https://www.wsperu.info">WebSolutions</a></p>
        </div>
    </div>
  </div>
  <!-- move top -->
  <button onclick="topFunction()" id="movetop" title="Go to top">
    <span class="fa fa-angle-up"></span>
  </button>
  <script>
    window.onscroll = function () { scrollFunction() };
    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("movetop").style.display = "block";
      } else {
        document.getElementById("movetop").style.display = "none";
      }
    }
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
  <!-- /move top -->
</section>
<!-- //footer block -->

<script src="assets/js/jquery-3.3.1.min.js"></script>
<script src="assets/js/theme-change.js"></script>
<script src="assets/js/lightbox-plus-jquery.min.js"></script>
<script src="assets/js/easyResponsiveTabs.js"></script>
<script src="assets/js/owl.carousel.js"></script>
<script src="assets/js/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({
      type: 'inline',
      fixedContentPos: false,
      fixedBgPos: true,
      overflowY: 'auto',
      closeBtnInside: true,
      preloader: false,
      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in'
    });
  });
</script>

<script>
  $(function () {
    $('.navbar-toggler').click(function () {
      $('body').toggleClass('noscroll');
    })
  });
</script>

<script>
  $(window).on("scroll", function () {
    var scroll = $(window).scrollTop();
    if (scroll >= 80) {
      $("#site-header").addClass("nav-fixed");
    } else {
      $("#site-header").removeClass("nav-fixed");
    }
  });
  $(".navbar-toggler").on("click", function () {
    $("header").toggleClass("active");
  });
  $(document).on("ready", function () {
    if ($(window).width() > 991) { $("header").removeClass("active"); }
    $(window).on("resize", function () {
      if ($(window).width() > 991) { $("header").removeClass("active"); }
    });
  });
</script>

<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>
