# Sitio público — Diálogo y Desarrollo (conectado a `revista_digital`)

Esta carpeta es un **esqueleto**: tiene la parte que se conecta a la base de
datos ya lista, pero le falta el diseño real (HTML/CSS del sitio que te dio
el docente). Tú pones el diseño, esto ya sabe traer los datos.

## 1. Instalación

1. Copia esta carpeta `dyd-sitio` dentro de `htdocs`, **al lado** de `dyd-admin`
   (no adentro), así:
   ```
   htdocs/
     dyd-admin/   <- tu panel de administración
     dyd-sitio/   <- esta carpeta
   ```
   Esto es importante porque `config/database.php` de aquí asume que las
   imágenes que subes desde el panel están en `../dyd-admin/uploads/`.

2. Copia el código fuente del docente (HTML, CSS, JS, imágenes) dentro de
   `assets/` (css → `assets/css`, js → `assets/js`, imágenes → `assets/img`)
   y ajusta las rutas si es necesario.

## 2. Cómo está organizado

```
dyd-sitio/
  config/database.php      <- conexión a revista_digital (misma BD del admin)
  includes/helpers.php     <- funciones: e(), formatFechaSitio(), imagenUrl()
  partials/                <- un archivo por sección dinámica de la portada
    reportajes-home.php    <- últimos reportajes
    noticias-home.php      <- últimas noticias
    boletin-home.php       <- boletín más reciente
    podcasts-home.php      <- últimos podcasts
  index.php                <- portada (pega aquí el HTML del docente)
  reportajes.php           <- listado completo de reportajes
  reportaje-detalle.php    <- una sola página para TODOS los reportajes
                               (reportaje-detalle.php?id=5), reemplaza los
                               .html individuales por artículo del sitio
                               original
  boletines.php            <- listado completo de boletines
  podcasts.php             <- listado completo de podcasts
  assets/                  <- aquí van el css/js/imágenes que copies
```

## 3. Cómo pegar el código del docente (paso a paso)

Para **cada** archivo `.php` de este proyecto:

1. Abre el `.html` equivalente que descargaste del sitio real
   (`index.html` → `index.php`, `reportajes-1.html` → `reportajes.php`, etc).
2. Copia el `<head>` (los `<link>` de CSS, fuentes, favicon) y pégalo donde
   dice `PEGA AQUÍ EL <head> ORIGINAL`.
3. Copia el header/menú de navegación y pégalo donde dice
   `PEGA AQUÍ EL HEADER / MENÚ ORIGINAL`.
4. Busca el bloque donde el HTML original repite varias tarjetas iguales
   (por ejemplo 4 tarjetas de reportajes copiadas y pegadas a mano) y
   bórralas TODAS, dejando solo el `<?php include ... ?>` que ya está ahí.
5. Entra al `partials/*.php` correspondiente y dentro del `<?php foreach
   (...): ?> ... <?php endforeach; ?>` reemplaza el HTML de ejemplo (marcado
   con `<!-- TODO -->`) por **tu** tarjeta real (mismas clases CSS que
   copiaste), cambiando solo el texto fijo por las variables PHP que ya
   están puestas (`$r['titulo']`, `$r['fecha_publicacion']`, etc).
6. Copia el footer y pégalo al final.

Repite lo mismo para `reportajes.php`, `boletines.php` y `podcasts.php` con
sus respectivos `.html` (`reportajes-1.html`, `boletines.html`, etc).

## 4. Cómo se conecta con el panel admin

- Todo lo que crees/edites/borres desde `dyd-admin` (reportajes, noticias,
  boletines, podcasts) aparece automáticamente aquí, porque ambos proyectos
  leen la **misma base de datos** `revista_digital`.
- Las fotos y PDFs que subes desde el panel se guardan físicamente en
  `dyd-admin/uploads/`; la función `imagenUrl()` de `includes/helpers.php`
  arma la ruta correcta hacia allá automáticamente.

## 5. Cuando tengas tu index.html copiado

Súbeme el `index.html` real que descargaste (con sus clases CSS) y te ayudo
a insertar los `<?php include ?>` en el lugar exacto sin romper el diseño.
