<?php
/**
 * Conexión a la base de datos "revista_digital".
 *
 * Valores por defecto de XAMPP: host=localhost, usuario=root, sin contraseña.
 * Si tu XAMPP tiene otra configuración (por ejemplo pusiste una contraseña a
 * root, o usas otro puerto), solo cambia las constantes de aquí abajo.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'revista_digital');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP por defecto: contraseña vacía

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
            // Mensaje amigable en vez del error crudo de PDO.
            die(
                '<div style="font-family:sans-serif;max-width:600px;margin:60px auto;' .
                'background:#fff3f3;border:1px solid #f5c2c2;padding:20px;border-radius:8px;">' .
                '<h3 style="margin-top:0;color:#c0392b;">No se pudo conectar a la base de datos</h3>' .
                '<p>Revisa que:</p><ul>' .
                '<li>XAMPP tenga Apache y MySQL iniciados.</li>' .
                '<li>La base <code>revista_digital</code> exista en phpMyAdmin (importaste el script del docente).</li>' .
                '<li>El usuario/contraseña en <code>config/database.php</code> coincidan con tu MySQL.</li>' .
                '</ul><p style="color:#888;font-size:13px;">Detalle técnico: ' . htmlspecialchars($e->getMessage()) . '</p></div>'
            );
        }
    }

    return $pdo;
}
