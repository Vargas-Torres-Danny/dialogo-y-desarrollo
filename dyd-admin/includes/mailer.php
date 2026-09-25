<?php
/**
 * Envío de correos usando PHPMailer + el SMTP de Gmail configurado en
 * config/mail.php.
 *
 * Este archivo espera encontrar la librería PHPMailer (sin Composer) en:
 *   dyd-admin/libs/PHPMailer/src/Exception.php
 *   dyd-admin/libs/PHPMailer/src/PHPMailer.php
 *   dyd-admin/libs/PHPMailer/src/SMTP.php
 *
 * Cómo conseguirla (una sola vez):
 *   1. Ve a https://github.com/PHPMailer/PHPMailer
 *   2. Botón verde "Code" -> "Download ZIP"
 *   3. Del ZIP descargado, copia la carpeta "src" completa dentro de tu
 *      proyecto, en la ruta: dyd-admin/libs/PHPMailer/src/
 *   4. Confirma que quedó exactamente así:
 *      dyd-admin/libs/PHPMailer/src/PHPMailer.php
 *      dyd-admin/libs/PHPMailer/src/SMTP.php
 *      dyd-admin/libs/PHPMailer/src/Exception.php
 */

require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envía un correo HTML con la cuenta Gmail configurada.
 * Lanza una excepción (con el detalle del error) si algo falla, para que
 * se pueda mostrar mientras se está probando en local.
 */
function enviarCorreo(string $destinatario, string $nombreDestinatario, string $asunto, string $cuerpoHtml): bool
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->addAddress($destinatario, $nombreDestinatario);
    $mail->addReplyTo(SMTP_USER, SMTP_FROM_NAME);

    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body    = $cuerpoHtml;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $cuerpoHtml));

    return $mail->send();
}
