<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getPDO();

$mesFiltro = $_GET['mes'] ?? null; // formato esperado: YYYY-MM
$porPagina = 9;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
$offset = ($paginaActual - 1) * $porPagina;

$condicionMes = '';
$parametros = [];
if ($mesFiltro && preg_match('/^\d{4}-\d{2}$/', $mesFiltro)) {
    $condicionMes = "WHERE DATE_FORMAT(r.fecha_publicacion, '%Y-%m') = ?";
    $parametros[] = $mesFiltro;
}

// Total de reportajes (respetando el filtro de mes, si hay uno) para saber
// cuántas páginas de 9 hacen falta.
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM reportajes r $condicionMes");
$stmtTotal->execute($parametros);
$totalReportajes = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalReportajes / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas); // por si piden una página que ya no existe
$offset = ($paginaActual - 1) * $porPagina;

$stmt = $pdo->prepare(
    "SELECT r.*, CONCAT(a.nombres,' ',IFNULL(a.ap_paterno,'')) AS autor_nombre
       FROM reportajes r
       LEFT JOIN autores a ON a.id = r.autor_id
       $condicionMes
      ORDER BY r.fecha_publicacion DESC
      LIMIT $porPagina OFFSET $offset"
);
$stmt->execute($parametros);
$reportajes = $stmt->fetchAll();

/** Arma la URL de una página de paginación, conservando el filtro de mes si hay uno. */
function urlPagina(int $pagina, ?string $mes): string
{
    $params = ['pagina' => $pagina];
    if ($mes) {
        $params['mes'] = $mes;
    }
    return 'reportajes.php?' . http_build_query($params);
}
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
          <!--<a class="navbar-brand" href="index.php">
              <span class="fa fa-video-camera"></span> V-Conference
          </a>
           if logo is image enable this   -->
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
                      <a class="nav-link" href="sobre-dyd.php">Sobre D&D</a>
                  </li>               
                  <li class="ml-2">
                      <a href="contacto.php" class="btn btn-style btn-outline-secondary">Contacto</a>
                  </li>
              </ul>
          </div>
          <!-- toggle switch for light and dark theme --
          <div class="mobile-position">
              <nav class="navigation">
                  <div class="theme-switch-wrapper">
                      <label class="theme-switch" for="checkbox">
                          <input type="checkbox" id="checkbox">
                          <div class="mode-container">
                              <i class="gg-sun"></i>
                              <i class="gg-moon"></i>
                          </div>
                      </label>
                  </div>
              </nav>
          </div>
          <!-- //toggle switch for light and dark theme -->
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
                    <?php if ($mesFiltro):
                        $mesesEs = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
                                    '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
                        [$anioFiltro, $mesNum] = explode('-', $mesFiltro);
                    ?>
                        <p class="mb-0">
                            Mostrando: <strong><?= e($mesesEs[$mesNum] ?? $mesNum) ?> <?= e($anioFiltro) ?></strong>
                            — <a href="reportajes.php">ver todos</a>
                        </p>
                    <?php endif; ?>
                    <div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.php">Inicio</a>
                            </li>
                            <li class="active">
                                 Reportajes
                            </li>
                        </ul>
                    </div>
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>
<div class="grids-block-5 py-5">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php foreach ($reportajes as $r): ?>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>" class="d-block corner-ribbon"><img src="<?= e(imagenUrl($r['foto_principal'])) ?>" alt="<?= e($r['titulo']) ?>" class="img-fluid" /></a>
                    <div class="blog-info">
                        <h5><?= formatFechaSitio($r['fecha_publicacion']) ?></h5>
                        <h4><a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>" class="d-block"><?= e($r['titulo']) ?></a></h4>
                        <a href="reportaje-detalle.php?id=<?= (int) $r['id'] ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span> </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($reportajes)): ?>
                    <div class="col-12 text-center py-5">
                        <p>Aún no hay reportajes publicados. Crea el primero desde tu panel admin.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <ul>
                    <?php if ($paginaActual > 1): ?>
                        <li class="prev"><a href="<?= e(urlPagina($paginaActual - 1, $mesFiltro)) ?>"> Ant</a></li>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                        <li><a href="<?= e(urlPagina($p, $mesFiltro)) ?>" class="<?= $p === $paginaActual ? 'active' : '' ?>"><?= $p ?></a></li>
                    <?php endfor; ?>
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <li class="next"><a href="<?= e(urlPagina($paginaActual + 1, $mesFiltro)) ?>"> Sig </a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            <!-- La paginación se calcula sola: 9 reportajes por página. Si
                 filtras por mes (?mes=YYYY-MM), la paginación respeta ese
                 filtro. -->
        </div>
</div>
<!-- // grids block 5 -->
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
            <!--<a href="#youtube" class="youtube"><span class="fa fa-youtube"></span></a>
            <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin"></span></a>-->
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
            <!--<!--<li><a href="#url">Celulares</a></li>-->
            <!--<li><a href="#url">Celulares</a></li>-->
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
    // When the user scrolls down 20px from the top of the document, show the button
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

    // When the user clicks on the button, scroll to the top of the document
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

<!-- js for portfolio lightbox -->
<script src="assets/js/lightbox-plus-jquery.min.js"></script>

<!-- responsive tabs -->
<script src="assets/js/easyResponsiveTabs.js"></script>
<!--Plug-in Initialisation-->
<script type="text/javascript">
  $(document).ready(function () {
    //Horizontal Tab
    $('#parentHorizontalTab').easyResponsiveTabs({
      type: 'default', //Types: default, vertical, accordion
      width: 'auto', //auto or any width like 600px
      fit: true, // 100% fit in a container
      tabidentify: 'hor_1', // The tab groups identifier
      activate: function (event) { // Callback function if tab is switched
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
        0: {
          items: 2,
          nav: false
        },
        480: {
          items: 2,
          nav: false
        },
        568: {
          items: 3,
          nav: false
        },
        1000: {
          items: 5,
          nav: false
        }
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
        0: {
          items: 1,
          nav: true
        },
        768: {
          items: 2,
          nav: false
        },
        1000: {
          items: 3,
          nav: true,
          loop: false
        }
      }
    })
  })
</script>
<!-- //script -->

<!-- script for teams -->
<script>
  $(document).ready(function () {
    $('.owl-carousel').owlCarousel({
      loop: true,
      margin: 0,
      responsiveClass: true,
      responsive: {
        0: {
          items: 1,
          nav: true
        },
        400: {
          items: 2,
          nav: true,
          margin: 20
        },
        768: {
          items: 3,
          nav: true,
          margin: 20
        },
        1000: {
          items: 4,
          nav: true,
          loop: true,
          margin: 25
        }
      }
    })
  })
</script>
<!-- //script for teams-->

<!-- Script for counter -->
<script>
  (() => {
    // Specify the deadline date
    const deadlineDate = new Date('January 27, 2025 23:59:59').getTime();

    // Cache all countdown boxes into consts
    const countdownDays = document.querySelector('.countdown__days .number');
    const countdownHours = document.querySelector('.countdown__hours .number');
    const countdownMinutes = document.querySelector('.countdown__minutes .number');
    const countdownSeconds = document.querySelector('.countdown__seconds .number');

    // Update the count down every 1 second (1000 milliseconds)
    setInterval(() => {
      // Get current date and time
      const currentDate = new Date().getTime();

      // Calculate the distance between current date and time and the deadline date and time
      const distance = deadlineDate - currentDate;

      // Calculations the data for remaining days, hours, minutes and seconds
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Insert the result data into individual countdown boxes
      countdownDays.innerHTML = days;
      countdownHours.innerHTML = hours;
      countdownMinutes.innerHTML = minutes;
      countdownSeconds.innerHTML = seconds;
    }, 1000);
  })();
</script>
<!-- //Script for counter -->

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
<!-- disable body scroll which navbar is in active -->

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

  //Main navigation Active Class Add Remove
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