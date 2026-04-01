<?php
// -------------------------------------------------------
//  config/mailer.php
//  PHPMailer setup using Gmail SMTP (Task 4)
// -------------------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Send a password reset email.
 *
 * @param  string $toEmail    Recipient email address
 * @param  string $toName     Recipient display name
 * @param  string $resetLink  The full reset URL
 * @return bool               True on success, false on failure
 */
function sendResetEmail(string $toEmail, string $toName, string $resetLink): bool
{
    $mail = new PHPMailer(true);

    try {
        // ── SMTP Configuration ──────────────────────────
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'martin.frederico29@gmail.com';
        $mail->Password   = 'bsxr hlch eykk jlrl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // ── Sender & Recipient ──────────────────────────
        $mail->setFrom('martin.frederico29@gmail.com', 'LoginForm App');
        $mail->addAddress($toEmail, $toName);

        // ── Email Content ───────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: auto; padding: 30px;
                        background: #f9f9f9; border-radius: 10px;'>
                <h2 style='color: #3b103b;'>Password Reset</h2>
                <p style='color: #555;'>Hi <strong>{$toName}</strong>,</p>
                <p style='color: #555;'>We received a request to reset your password.
                   Click the button below to set a new one.</p>
                <a href='{$resetLink}'
                   style='display:inline-block; margin: 20px 0; padding: 12px 28px;
                          background: #1e111e; color: #fff; border-radius: 25px;
                          text-decoration: none; font-size: 14px;'>
                   Reset Password
                </a>
                <p style='color: #999; font-size: 12px;'>
                    This link expires in <strong>30 minutes</strong> and can only be used once.<br>
                    If you did not request this, you can safely ignore this email.
                </p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #ccc; font-size: 11px;'>LoginForm App &mdash; Security System</p>
            </div>
        ";
        $mail->AltBody = "Reset your password here: {$resetLink} (expires in 30 minutes)";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer error: " . $mail->ErrorInfo);
        return false;
    }
}