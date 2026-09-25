<?php
/** Extrae el ID de video de cualquier formato de URL de YouTube. */
function youtubeId(?string $url): ?string
{
    if (empty($url)) return null;
    if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{6,})#', $url, $m)) {
        return $m[1];
    }
    return null;
}

/** Convierte cualquier link de YouTube a su URL de embed (iframe). Si no es YouTube, devuelve el link tal cual (ya podría ser un embed de Spotify/SoundCloud). */
function embedUrl(?string $url): string
{
    $id = youtubeId($url);
    return $id ? "https://www.youtube.com/embed/{$id}" : e($url);
}

/** Miniatura automática de YouTube para usar como imagen de la tarjeta. */
function youtubeThumb(?string $url, string $fallback): string
{
    $id = youtubeId($url);
    return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : $fallback;
}

/** Atajo para htmlspecialchars. */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formatea 'YYYY-MM-DD' a 'Ago 28, 2026' (estilo del sitio original). */
function formatFechaSitio(?string $fecha): string
{
    if (empty($fecha)) return '';
    $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
              '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
    $partes = explode('-', $fecha);
    if (count($partes) !== 3) return e($fecha);
    [$anio, $mes, $dia] = $partes;
    return ($meses[$mes] ?? $mes) . ' ' . ltrim($dia, '0') . ', ' . $anio;
}

/**
 * Construye la URL de una imagen subida desde el panel admin.
 * $ruta viene de la BD como "uploads/fotos/xxxx.jpg" -> la resolvemos
 * contra la carpeta del admin (ver UPLOADS_URL en config/database.php).
 * Si no hay imagen, devuelve una imagen de reemplazo genérica.
 */
function imagenUrl(?string $ruta, string $fallback = 'assets/images/placeholder.jpg'): string
{
    if (empty($ruta)) {
        return $fallback;
    }
    // $ruta guarda "uploads/fotos/archivo.jpg"; UPLOADS_URL ya apunta a ".../dyd-admin/uploads/"
    $archivo = preg_replace('#^uploads/#', '', $ruta);
    return UPLOADS_URL . $archivo;
}
