<?php
/**
 * Sube un archivo de $_FILES a /uploads/<subcarpeta>/ y devuelve la ruta
 * relativa a guardar en la base de datos (o null si no se envió archivo).
 *
 * @param string $campo       nombre del input file en el formulario
 * @param string $subcarpeta  "fotos" | "pdfs"
 * @param array  $extensionesPermitidas ej. ['jpg','jpeg','png','webp']
 * @return string|null|false  ruta relativa | null (sin archivo) | false (error)
 */
function subirArchivo(string $campo, string $subcarpeta, array $extensionesPermitidas)
{
    if (empty($_FILES[$campo]['name']) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no se seleccionó archivo, está bien (es opcional en edición)
    }

    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $nombreOriginal = $_FILES[$campo]['name'];
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    if (!in_array($ext, $extensionesPermitidas, true)) {
        return false;
    }

    $carpetaDestino = __DIR__ . '/../uploads/' . $subcarpeta . '/';
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0777, true);
    }

    $nombreArchivo = uniqid($subcarpeta . '_', true) . '.' . $ext;
    $rutaCompleta  = $carpetaDestino . $nombreArchivo;

    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $rutaCompleta)) {
        return false;
    }

    return 'uploads/' . $subcarpeta . '/' . $nombreArchivo; // ruta relativa para la BD
}
