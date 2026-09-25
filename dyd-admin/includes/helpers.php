<?php
/** Guarda un mensaje flash (se muestra una sola vez en la siguiente carga). */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** Redirige y termina la ejecución. */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Formatea una fecha 'YYYY-MM-DD' a '07 Sep 2026' (español). */
function formatFecha(?string $fecha): string
{
    if (empty($fecha)) return '—';
    $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
              '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
    $partes = explode('-', $fecha);
    if (count($partes) !== 3) return htmlspecialchars($fecha);
    [$anio, $mes, $dia] = $partes;
    return $dia . ' ' . ($meses[$mes] ?? $mes) . ' ' . $anio;
}

/** Atajo para htmlspecialchars. */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}
