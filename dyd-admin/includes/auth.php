<?php
/**
 * Manejo de sesión y autenticación contra la tabla "usuarios".
 * Incluir este archivo AL INICIO de cada página protegida:
 *
 *     require_once __DIR__ . '/includes/auth.php';
 *     requireLogin();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/** Corta la ejecución y manda a login si no hay sesión activa. */
function requireLogin(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

/** Devuelve el usuario actualmente logueado (array) o null. */
function usuarioActual(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }
    return [
        'id'      => $_SESSION['usuario_id'],
        'nombres' => $_SESSION['usuario_nombres'],
        'email'   => $_SESSION['usuario_email'],
        'rol'     => $_SESSION['usuario_rol'],
    ];
}

/** Guarda en sesión los datos del usuario que acaba de loguearse. */
function iniciarSesionUsuario(array $usuario): void
{
    $_SESSION['usuario_id']      = $usuario['id'];
    $_SESSION['usuario_nombres'] = $usuario['nombres'] . ' ' . $usuario['ap_paterno'];
    $_SESSION['usuario_email']   = $usuario['email'];
    $_SESSION['usuario_rol']     = $usuario['rol'];
}

/** Nombre corto para mostrar en el sidebar/topbar. */
function nombreUsuarioActual(): string
{
    return $_SESSION['usuario_nombres'] ?? 'Usuario';
}

/** true si el usuario logueado tiene rol "admin". */
function esAdmin(): bool
{
    return ($_SESSION['usuario_rol'] ?? '') === 'admin';
}

/** Corta la ejecución si el usuario logueado no es Admin (llamar después de requireLogin()). */
function requireAdmin(): void
{
    if (!esAdmin()) {
        http_response_code(403);
        die(
            '<div style="font-family:sans-serif;max-width:480px;margin:80px auto;' .
            'background:#fff3f3;border:1px solid #f5c2c2;padding:24px 28px;border-radius:8px;text-align:center;">' .
            '<h3 style="margin-top:0;color:#c0392b;">Acceso restringido</h3>' .
            '<p>Esta sección es solo para administradores.</p>' .
            '<a href="index.php">&larr; Volver al panel</a></div>'
        );
    }
}

/**
 * ¿Puede el usuario logueado EDITAR un contenido cuyo dueño (columna
 * usuario_id de esa fila) es $idDueno?
 *   - admin: siempre puede.
 *   - editor / redactor: solo si el contenido es suyo.
 */
function puedeEditarContenido(int $idDueno): bool
{
    if (esAdmin()) {
        return true;
    }
    $rol = $_SESSION['usuario_rol'] ?? '';
    if ($rol === 'editor' || $rol === 'redactor') {
        return $idDueno === (int) ($_SESSION['usuario_id'] ?? 0);
    }
    return false;
}

/**
 * ¿Puede el usuario logueado ELIMINAR un contenido cuyo dueño (columna
 * usuario_id de esa fila) es $idDueno?
 *   - admin: siempre puede.
 *   - editor: solo si el contenido es suyo.
 *   - redactor: nunca puede eliminar.
 */
function puedeEliminarContenido(int $idDueno): bool
{
    if (esAdmin()) {
        return true;
    }
    if (($_SESSION['usuario_rol'] ?? '') === 'editor') {
        return $idDueno === (int) ($_SESSION['usuario_id'] ?? 0);
    }
    return false;
}
