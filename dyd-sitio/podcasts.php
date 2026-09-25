<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getPDO();
$podcasts = $pdo->query('SELECT * FROM podcasts ORDER BY fecha_publicacion DESC')->fetchAll();
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

    <title>Podcast · DDP Noticias - Diálogo y Desarrollo Perú</title>

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
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="reportajes.php">Reportajes</a>
                  </li>
                  <li class="nav-item active">
                      <a class="nav-link" href="podcasts.php">Podcast</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="boletines.php">Boletín NTEP</a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="alianzas.php">Alianzas</a>
                  </li>
                  <li class="nav-item @@contact__active">
                      <a class="nav-link" href="sobre-dyd.php">Sobre D&D</a>
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
                    <h2 class="title-big">Podcast</h2>
                    <div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.php">Inicio</a>
                            </li>
                            <li class="active">
                                 Podcast
                            </li>
                        </ul>
                    </div>
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>

<!-- Esta página no existía en el sitio original del docente (el enlace
     "Podcast" apuntaba a about.html por error). Se construyó siguiendo
     el mismo estilo "area-box" que ya usa la sección Podcast de la
     portada, para que se vea igual de fiel al resto del sitio. -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="row">
            <?php foreach ($podcasts as $p): ?>
                <div class="col-lg-3 col-sm-6 mt-lg-0 mt-5">
                    <div class="area-box">
                        <div class="embed-responsive embed-responsive-16by9 corner-ribbon corner-ribbon-sm">
                            <iframe class="embed-responsive-item" src="<?= embedUrl($p['url_embed']) ?>" allow="autoplay; fullscreen" allowfullscreen style="width:100%;aspect-ratio:16/9;border:0;"></iframe>
                        </div>
                        <p class="mt-3"><?= e($p['titulo']) ?></p>
                        <p class="small text-muted mb-0"><?= formatFechaSitio($p['fecha_publicacion']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($podcasts)): ?>
                <div class="col-12 text-center py-5">
                    <p>Aún no hay episodios publicados. Crea el primero desde tu panel admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

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
            <li><a href="#url">Noticias</a></li>
            <li><a href="#url">Videos</a></li>
            <li><a href="#url">Posdcast.</a></li>
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
    window.onscroll = function () {
      scrollFunction()
    };

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
    if ($(window).width() > 991) {
      $("header").removeClass("active");
    }
    $(window).on("resize", function () {
      if ($(window).width() > 991) {
        $("header").removeClass("active");
      }
    });
  });
</script>
<!--//MENU-JS-->

<script src="assets/js/bootstrap.min.js"></script>

</body>

</html>
