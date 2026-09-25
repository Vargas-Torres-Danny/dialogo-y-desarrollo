<?php
/**
 * Configuración de envío de correos (recuperación de contraseña) usando
 * el SMTP de Gmail.
 *
 * IMPORTANTE: desde 2022 Gmail YA NO deja usar tu contraseña normal de la
 * cuenta para esto. Hay que generar una "Contraseña de aplicación":
 *
 *   1. Entra a myaccount.google.com/security con la cuenta Gmail que vas
 *      a usar para enviar los correos (puede ser una cuenta nueva solo
 *      para esto, no tiene que ser tu correo personal).
 *   2. Activa la "Verificación en 2 pasos" (si no la tienes activada, la
 *      opción de abajo ni te va a aparecer).
 *   3. Busca "Contraseñas de aplicaciones" (o entra directo a
 *      myaccount.google.com/apppasswords), crea una nueva, ponle un
 *      nombre como "DyD Admin", y Google te va a dar una clave de 16
 *      letras (tipo "abcd efgh ijkl mnop").
 *   4. Pega esa clave abajo en SMTP_PASS, SIN espacios.
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '023100936b@uandina.edu.pe');        // <-- cambia esto por tu Gmail
define('SMTP_PASS', '****'); // <-- las 16 letras, sin espacios
define('SMTP_FROM_NAME', 'Diálogo y Desarrollo');
