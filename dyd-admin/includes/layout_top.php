<?php
/**
 * Antes de incluir este archivo, definir:
 *   $pageTitle     (string) título de la pestaña y del <h1>
 *   $pageSubtitle  (string) subtítulo pequeño junto al <h1>
 *   $activeMenu    (string) uno de: reportajes, noticias, boletines, podcasts,
 *                  videos, imagenes, archivos, autores, usuarios, perfil, index
 */
$pageTitle    = $pageTitle ?? '';
$pageSubtitle = $pageSubtitle ?? '';
$activeMenu   = $activeMenu ?? '';

function navActive(string $key, string $active): string
{
    return $key === $active ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title><?= htmlspecialchars($pageTitle) ?> · Diálogo y Desarrollo</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="css/application.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>

<body class="">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="js/vendor-shim.js"></script>
<script src="js/settings.js"></script>
<script src="js/app.js"></script>

<nav class="page-controls navbar navbar-dashboard">
    <div class="container-fluid">
        <div class="navbar-header mobile-hidden">
            <a href="index.php" class="d-lg-none mr-3" style="font-weight:600;color:#495057;white-space:nowrap;">
                <span class="fw-thin">Diálogo</span> <b>y Desarrollo</b>
            </a>
            <form class="navbar-form d-none d-lg-block" role="search">
                <div class="form-group">
                    <div class="input-group input-group-no-border mr-3">
                        <input class="form-control" id="main-search" type="text" placeholder="Buscar en el panel">
                        <span class="input-group-append">
                            <span class="input-group-text"><i class="fi flaticon-search fw-bold"></i></span>
                        </span>
                    </div>
                </div>
            </form>
            <ul class="nav navbar-nav float-right">
                <li class="dropdown nav-item">
                    <a href="#" role="button" class="dropdown-toggle dropdown-toggle-notifications nav-link small"
                       id="user-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="thumb-sm avatar float-left">
                            <img class="rounded-circle" src="img/avatar.png" alt="Usuario">
                        </span>
                        &nbsp; <?= htmlspecialchars(nombreUsuarioActual()) ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right py-0" aria-labelledby="user-dropdown-toggle">
                        <a class="dropdown-item" href="perfil.php"><i class="fa fa-user mr-2"></i>Mi perfil</a>
                        <a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out mr-2"></i>Cerrar sesión</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<nav id="sidebar" class="sidebar" role="navigation">
    <div class="js-sidebar-content">
        <header class="logo d-none d-md-block">
            <a href="index.php"><span class="fw-thin">Diálogo</span> <b>y Desarrollo</b></a>
        </header>
        <ul class="sidebar-nav">
            <li class="<?= navActive('index', $activeMenu) ?>">
                <a href="index.php">
                    <span class="icon"><i class="fi flaticon-home"></i></span>
                    Resumen
                </a>
            </li>
        </ul>
        <h5 class="sidebar-nav-title">Contenido</h5>
        <ul class="sidebar-nav">
            <li class="<?= navActive('reportajes', $activeMenu) ?>">
                <a href="reportajes.php">
                    <span class="icon"><i class="fi flaticon-newspaper"></i></span>
                    Reportajes
                </a>
            </li>
            <li class="<?= navActive('noticias', $activeMenu) ?>">
                <a href="noticias.php">
                    <span class="icon"><i class="fi flaticon-megaphone"></i></span>
                    Noticias
                </a>
            </li>
            <li class="<?= navActive('boletines', $activeMenu) ?>">
                <a href="boletines.php">
                    <span class="icon"><i class="fi flaticon-paper-plane"></i></span>
                    Boletines
                </a>
            </li>
            <li class="<?= navActive('podcasts', $activeMenu) ?>">
                <a href="podcasts.php">
                    <span class="icon"><i class="fi flaticon-microphone"></i></span>
                    Podcasts
                </a>
            </li>
            <li class="<?= navActive('videos', $activeMenu) ?>">
                <a href="videos.php">
                    <span class="icon"><i class="fi flaticon-video-camera"></i></span>
                    Videos
                </a>
            </li>
        </ul>
        <h5 class="sidebar-nav-title">Medios</h5>
        <ul class="sidebar-nav">
            <li class="<?= navActive('imagenes', $activeMenu) ?>">
                <a href="imagenes.html">
                    <span class="icon"><i class="fi flaticon-picture"></i></span>
                    Imágenes
                </a>
            </li>
            <li class="<?= navActive('archivos', $activeMenu) ?>">
                <a href="archivos.html">
                    <span class="icon"><i class="fi flaticon-attachment"></i></span>
                    Archivos
                </a>
            </li>
        </ul>
        <h5 class="sidebar-nav-title">Administración</h5>
        <ul class="sidebar-nav">
            <li class="<?= navActive('autores', $activeMenu) ?>">
                <a href="autores.php">
                    <span class="icon"><i class="fi flaticon-user"></i></span>
                    Autores
                </a>
            </li>
            <?php if (esAdmin()): ?>
            <li class="<?= navActive('usuarios', $activeMenu) ?>">
                <a href="usuarios.php">
                    <span class="icon"><i class="fi flaticon-users"></i></span>
                    Usuarios
                </a>
            </li>
            <?php endif; ?>
            <li class="<?= navActive('perfil', $activeMenu) ?>">
                <a href="perfil.php">
                    <span class="icon"><i class="fi flaticon-id-card"></i></span>
                    Mi perfil
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="content-wrap">
<main id="content" class="content" role="main">
<ol class="breadcrumb">
    <li class="breadcrumb-item">DIÁLOGO Y DESARROLLO</li>
    <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
</ol>
<h1 class="page-title"><?= htmlspecialchars($pageTitle) ?> <small><small><?= htmlspecialchars($pageSubtitle) ?></small></small></h1>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
