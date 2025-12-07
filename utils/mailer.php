<?php
/**
 * Clase Mailer
 * Maneja el envío de correos electrónicos usando PHPMailer con Gmail
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class Mailer {
    private $from_email;
    private $from_name;
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct() {
        // Cargar variables de entorno si no están cargadas
        if (!isset($_ENV['MAIL_HOST'])) {
            $this->loadEnv();
        }

        $this->host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->port = $_ENV['MAIL_PORT'] ?? 587;
        $this->username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->from_email = $_ENV['MAIL_FROM_ADDRESS'] ?? '';
        $this->from_name = $_ENV['MAIL_FROM_NAME'] ?? 'Hersil Shop';
    }

    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    // Configurar PHPMailer
    private function configurePHPMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;
            $mail->CharSet    = 'UTF-8';

            // Remitente
            $mail->setFrom($this->from_email, $this->from_name);

            return $mail;
        } catch (Exception $e) {
            error_log("Error configurando PHPMailer: " . $e->getMessage());
            return false;
        }
    }

    // Enviar código de verificación
    public function sendVerificationCode($to, $code, $nombre = '') {
        $mail = $this->configurePHPMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = "Código de Recuperación - Hersil Shop";
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
                    .code-box { background: white; border: 2px dashed #4F46E5; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                    .code { font-size: 32px; font-weight: bold; color: #4F46E5; letter-spacing: 5px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                    .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🔐 Recuperación de Contraseña</h1>
                    </div>
                    <div class='content'>
                        <p>Hola" . ($nombre ? " <strong>{$nombre}</strong>" : "") . ",</p>
                        <p>Hemos recibido una solicitud para recuperar tu contraseña en <strong>Hersil Shop</strong>.</p>
                        
                        <div class='code-box'>
                            <p style='margin: 0 0 10px 0; color: #666;'>Tu código de verificación es:</p>
                            <div class='code'>{$code}</div>
                        </div>

                        <div class='warning'>
                            <strong>⚠️ Importante:</strong>
                            <ul style='margin: 10px 0;'>
                                <li>Este código expira en <strong>15 minutos</strong></li>
                                <li>No compartas este código con nadie</li>
                                <li>Si no solicitaste este código, ignora este correo</li>
                            </ul>
                        </div>

                        <p>Si tienes algún problema, contáctanos respondiendo a este correo.</p>
                        
                        <p style='margin-top: 30px;'>
                            Saludos,<br>
                            <strong>Equipo de Hersil Shop</strong>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                        <p>&copy; " . date('Y') . " Hersil Shop. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
            error_log("Correo de verificación enviado exitosamente a: " . $to);
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo de verificación: " . $mail->ErrorInfo);
            return false;
        }
    }

    // Enviar correo de bienvenida
    public function sendWelcomeEmail($to, $nombre) {
        $mail = $this->configurePHPMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = "¡Bienvenido a Hersil Shop! 🎉";
            
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                    .features { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                    .feature-item { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎉 ¡Bienvenido a Hersil Shop!</h1>
                        <p style='font-size: 18px; margin: 10px 0 0 0;'>Tu cuenta ha sido creada exitosamente</p>
                    </div>
                    <div class='content'>
                        <p>Hola <strong>{$nombre}</strong>,</p>
                        <p>¡Gracias por registrarte en Hersil Shop! Estamos emocionados de tenerte con nosotros.</p>
                        
                        <div class='features'>
                            <h3 style='margin-top: 0; color: #4F46E5;'>¿Qué puedes hacer ahora?</h3>
                            <div class='feature-item'>✅ Explora nuestro catálogo de productos electrónicos</div>
                            <div class='feature-item'>✅ Busca productos por categorías</div>
                            <div class='feature-item'>✅ Gestiona tu perfil</div>
                            <div class='feature-item' style='border: none;'>✅ Mantente al día con las últimas novedades</div>
                        </div>

                        <div style='text-align: center;'>
                            <a href='" . ($_ENV['APP_URL'] ?? 'http://localhost/hersil_php') . "/public/productos' class='button'>
                                Ver Productos
                            </a>
                        </div>

                        <p style='margin-top: 30px;'>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                        
                        <p style='margin-top: 30px;'>
                            ¡Feliz compra!<br>
                            <strong>Equipo de Hersil Shop</strong>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Hersil Shop. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
            error_log("Correo de bienvenida enviado exitosamente a: " . $to);
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo de bienvenida: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>