<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getPDO();

$reportajes = $pdo->query(
    "SELECT r.*, CONCAT(a.nombres,' ',IFNULL(a.ap_paterno,'')) AS autor_nombre
       FROM reportajes r
       LEFT JOIN autores a ON a.id = r.autor_id
      ORDER BY r.es_destacado DESC, r.fecha_publicacion DESC"
)->fetchAll();
// El destacado solo existe si de verdad hay uno marcado como tal en el
// panel; si no hay ninguno, no se muestra ninguna sección de destacado.
$reportajeDestacado = ($reportajes && $reportajes[0]['es_destacado']) ? $reportajes[0] : null;
$reportajesRecientes = $reportajeDestacado
    ? array_slice($reportajes, 1, 3)
    : array_slice($reportajes, 0, 3);

$noticiasRecientes = $pdo->query(
    'SELECT * FROM noticias ORDER BY fecha_publicacion DESC LIMIT 3'
)->fetchAll();

$boletinReciente = $pdo->query(
    'SELECT * FROM boletines ORDER BY fecha_publicacion DESC LIMIT 1'
)->fetch();

$podcastsRecientes = $pdo->query(
    'SELECT * FROM podcasts ORDER BY fecha_publicacion DESC LIMIT 4'
)->fetchAll();

$videos = $pdo->query('SELECT * FROM videos ORDER BY fecha_publicacion DESC')->fetchAll();
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

    <title>DDP Noticias - Diálogo y Desarrollo Perú</title>

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
                  <li class="nav-item active">
                      <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="#actualidad">Actualidad</a>
                  </li>
				  <li class="nav-item @@about__active">
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
                    <h2 class="title-big">Reportajes</h2>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="w3l-video w3l-homeblock3 " id="video">
    <!-- /video-6-->
    <?php if ($reportajeDestacado): ?>
    <div class="container-fluid">
        <div class="video-grids-info row">
            <div class="video-gd-right col-lg-6 p-0">
                <div class="position-relative">
                    <a href="reportaje-detalle.php?id=<?= (int) $reportajeDestacado['id'] ?>" class="corner-ribbon"><img src="<?= e(imagenUrl($reportajeDestacado['foto_principal'], 'assets/images/video.jpg')) ?>" alt="<?= e($reportajeDestacado['titulo']) ?>" class="img-fluid"></a>
                </div>
            </div>
            <div class="video-gd-left col-lg-6 p-lg-5 p-4 align-self">
                <div class="p-xl-4 p-0 video-wrap">
                    <h5><?= formatFechaSitio($reportajeDestacado['fecha_publicacion']) ?></h5>
					<h3 class="title-big text-left mb-4"><a href="reportaje-detalle.php?id=<?= (int) $reportajeDestacado['id'] ?>"><?= e($reportajeDestacado['titulo']) ?></a></h3>
                    <p><?= e($reportajeDestacado['resumen_corto']) ?></p>
					<a href="reportaje-detalle.php?id=<?= (int) $reportajeDestacado['id'] ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<div class="grids-block-5 py-1">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php foreach ($reportajesRecientes as $r): ?>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>" class="d-block corner-ribbon"><img src="<?= e(imagenUrl($r['foto_principal'])) ?>" alt="<?= e($r['titulo']) ?>" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5><?= formatFechaSitio($r['fecha_publicacion']) ?></h5>
                        <h4><a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>" class="d-block"><?= e($r['titulo']) ?></a></h4>
                        <a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="pagination">
                <ul>
                    <li><a href="reportajes.php">Ver todos</a></li>
                </ul>
            </div>
        </div>
</div>
<section class="breadcrumb-area py-sm-5 py-1">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Noticias Recientes</h2><a class="anchor" id="actualidad"></a>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="grids-block-5 py-5">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php foreach ($noticiasRecientes as $n): ?>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a target="_blank" href="<?= e($n['link_externo']) ?>" class="d-block corner-ribbon"><img src="<?= e(imagenUrl($n['foto'])) ?>" alt="<?= e($n['titulo']) ?>" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5><?= formatFechaSitio($n['fecha_publicacion']) ?></h5>
                        <h4><a target="_blank" href="<?= e($n['link_externo']) ?>" class="d-block"><?= e($n['titulo']) ?></a></h4>
                        <a target="_blank" href="<?= e($n['link_externo']) ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($noticiasRecientes)): ?>
                    <div class="col-12 text-center py-3"><p class="text-muted">Aún no hay noticias registradas.</p></div>
                <?php endif; ?>
            </div>
            <div class="pagination">
                <ul>
                    <li><a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru">Ver todos</a></li>
                </ul>
            </div>
        </div>
</div>

<!-- middle grid -->
<?php if ($boletinReciente): ?>
<section class="w3l-homeblock5 py-0">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-8 align-self">
                <h3 class="title-big mb-4">Boletín NTEP</h3>
                <p><?= e($boletinReciente['resumen']) ?></p>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0">
                        <span>Nº <?= e($boletinReciente['numero_boletin']) ?></span>
                        <h4><?= formatFechaSitio($boletinReciente['fecha_publicacion']) ?></h4>
                    </div>
                    <div class="col-6 p-0">
                        <span><a target="_blank" href="<?= e(imagenUrl($boletinReciente['archivo_pdf'])) ?>" class="facebook"><span class="fa fa-download"></span></a></span>
                        <h4>Ver Boletín</h4>
                    </div>
					<center><a href="boletines.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
                </div>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <img src="<?= e(imagenUrl($boletinReciente['foto_portada'])) ?>" class="img-fluid radius-image" alt="Boletín N° <?= e($boletinReciente['numero_boletin']) ?>">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<!-- //middle grid -->
<section class="w3l-homeblock3 py-5">
    <div class="container py-lg-5 py-md-4">
        <h3 class="title-big mb-5 text-center">Podcast</h3>
        <div class="row">
            <?php foreach ($podcastsRecientes as $p): ?>
            <div class="col-lg-3 col-sm-6 mt-lg-0 mt-5">
                <div class="area-box">
                    <div class="embed-responsive embed-responsive-16by9 corner-ribbon corner-ribbon-sm">
                        <iframe class="embed-responsive-item" src="<?= embedUrl($p['url_embed']) ?>" allow="autoplay; fullscreen" allowfullscreen style="width:100%;aspect-ratio:16/9;border:0;"></iframe>
                    </div>
                    <p class="mt-2"><?= e($p['titulo']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($podcastsRecientes)): ?>
                <div class="col-12 text-center"><p class="text-muted">Aún no hay episodios publicados.</p></div>
            <?php endif; ?>
        </div>
		<center><a href="podcasts.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
    </div>
</section>

<section class="w3l-team" id="team">
	<div class="teams1 py-5 mb-3">
		<div class="container py-lg-3 pb-lg-5 pb-4">
			<div class="teams1-content">
                <h3 class="title-big text-center mb-5">Especiales</h3>
					<div class="owl-carousel owl-especiales owl-theme text-center">
                    <?php foreach ($videos as $v): ?>
                    <div class="item">
                        <div class="embed-responsive embed-responsive-16by9 corner-ribbon corner-ribbon-sm">
                            <iframe class="embed-responsive-item" src="<?= embedUrl($v['url_embed']) ?>" allow="autoplay; fullscreen" allowfullscreen style="width:100%;aspect-ratio:16/9;border:0;"></iframe>
                        </div>
                        <p class="mt-3"><?= e($v['titulo']) ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($videos)): ?>
                        <div class="item"><p class="text-muted">Aún no hay videos especiales publicados.</p></div>
                    <?php endif; ?>
					</div>
			</div>
		</div>
	</div>
</section>
<section class="w3l-banner py-0" id="work">
    <div class="midd-w3 py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mt-lg-0 mt-lg-5 about-right-faq align-self">
                    <h5 class="title-small mb-2">DDP Noticias</h5>
                    <h3 class="title-banner">Diálogo y Desarrollo Perú</h3>
                    <p class="mt-4">Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                        <a href="sobre-dyd.php" class="btn btn-style btn-primary mt-md-5 mt-4">Nosotros</a>
                 </div>
                <div class="col-md-6 left-wthree-img mt-lg-0 mt-4">
                    <div class="position-relative">
                        <img src="assets/images/bannerimg.jpg" alt="" class="img-fluid">
                        <a href="#small-dialog-banner" class="popup-with-zoom-anim play-view text-center position-absolute">
                            <span class="video-play-icon"><span class="fa fa-play"></span></span>
                        </a>
                        <div id="small-dialog-banner" class="zoom-anim-dialog mfp-hide">
                            <iframe src="https://www.youtube.com/embed/2jI6fHBtRJU" allow="autoplay; fullscreen" allowfullscreen=""></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- middle -->
<div class="middle py-5">
    <div class="container py-xl-5 py-lg-3">
        <div class="welcome-left text-center py-md-5 py-3">
            <h3 class="title-big">Síguenos en nuestras Redes Sociales</h3>
            <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square fa-2x"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokg.png"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram fa-2x"></span></a>
          </div>
        </div>
    </div>
</div>
<!-- //middle -->


<!-- footer block -->
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
            <li><a href="index.php#actualidad">Noticias</a></li>
            <li><a href="index.php#team">Videos</a></li>
            <li><a href="podcasts.php">Podcast</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
          <div class="properties">
            <h6 class="footer-title-29">Contacto</h6>
            <ul>
            <li><a href="contacto.php">info@dialogoydesarrollo.com.pe</a></li>
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

<!-- Template JavaScript -->
<script src="assets/js/jquery-3.3.1.min.js"></script>

<script src="assets/js/theme-change.js"></script><!-- theme switch js (light and dark)-->

<!-- responsive tabs -->
<script src="assets/js/easyResponsiveTabs.js"></script>
<!--Plug-in Initialisation-->
<script type="text/javascript">
  $(document).ready(function () {
    $('#parentHorizontalTab').easyResponsiveTabs({
      type: 'default',
      width: 'auto',
      fit: true,
      tabidentify: 'hor_1',
      activate: function (event) {
        var $tab = $(this);
        var $info = $('#nested-tabInfo');
        var $name = $('span', $info);
        $name.text($tab.text());
        $info.show();
      }
    });
  });
</script>

<script src="assets/js/owl.carousel.js"></script>
<!-- logos for customers -->
<script>
  $(document).ready(function () {
    $('.owl-logos').owlCarousel({
      loop: true,
      margin: 0,
      nav: false,
      responsiveClass: true,
      autoplay: true,
      autoplayTimeout: 5000,
      autoplaySpeed: 1000,
      autoplayHoverPause: false,
      responsive: {
        0: { items: 2, nav: false },
        480: { items: 2, nav: false },
        568: { items: 3, nav: false },
        1000: { items: 5, nav: false }
      }
    })
  })
</script>
<!-- //logos owlcarousel -->

<!-- for tesimonials carousel slider -->
<script>
  $(document).ready(function () {
    $("#owl-demo1").owlCarousel({
      loop: true,
      margin: 20,
      responsiveClass: true,
      responsive: {
        0: { items: 1, nav: true },
        768: { items: 2, nav: false },
        1000: { items: 3, nav: true, loop: false }
      }
    })
  })
</script>
<!-- //script -->

<!-- script for teams (excluye Especiales y logos, que tienen su propia config) -->
<script>
  $(document).ready(function () {
    $('.owl-carousel:not(.owl-especiales):not(.owl-logos)').owlCarousel({
      loop: true,
      margin: 0,
      responsiveClass: true,
      responsive: {
        0: { items: 1, nav: true },
        400: { items: 2, nav: true, margin: 20 },
        768: { items: 3, nav: true, margin: 20 },
        1000: { items: 4, nav: true, loop: true, margin: 25 }
      }
    })
  })
</script>
<!-- //script for teams-->

<!-- script para Especiales -->
<script>
  $(window).on('load', function () {
    var totalEspeciales = <?= (int) count($videos) ?>;
    $('.owl-especiales').owlCarousel({
      loop: totalEspeciales > 4,
      margin: 15,
      nav: true,
      dots: true,
      responsiveClass: true,
      responsive: {
        0: { items: 1 },
        480: { items: 2 },
        768: { items: 3 },
        992: { items: 4 }
      }
    })
  })
</script>
<!-- //script for Especiales -->


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

    $('.popup-with-move-anim').magnificPopup({
      type: 'inline',
      fixedContentPos: false,
      fixedBgPos: true,
      overflowY: 'auto',
      closeBtnInside: true,
      preloader: false,
      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-slide-bottom'
    });
  });
</script>

<!-- disable body scroll which navbar is in active -->
<script>
  $(function () {
    $('.navbar-toggler').click(function () {
      $('body').toggleClass('noscroll');
    })
  });
</script>

<!--/MENU-JS-->
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
<!--//MENU-JS-->

<script src="assets/js/bootstrap.min.js"></script>

</body>

</html>
