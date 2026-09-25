<?php
/**
 * Conexión a la base de datos "revista_digital" para el SITIO PÚBLICO.
 * Es la misma base que usa el panel admin — aquí solo leemos datos,
 * nunca insertamos ni editamos desde el sitio público.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'revista_digital');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP por defecto: contraseña vacía

/**
 * Ruta base donde el panel admin guarda las fotos/PDFs subidos.
 * Si pusiste dyd-admin y dyd-sitio como carpetas hermanas dentro de
 * htdocs (recomendado), esta ruta relativa ya funciona tal cual:
 *   htdocs/dyd-admin/uploads/...
 *   htdocs/dyd-sitio/index.php   <- este archivo está aquí
 * Si le pusiste otro nombre a la carpeta del admin, cambia esta línea.
 */
define('UPLOADS_URL', '../dyd-admin/uploads/');

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('No se pudo conectar a la base de datos: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}
