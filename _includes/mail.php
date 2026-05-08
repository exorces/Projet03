<?php
require_once __DIR__ . '/../_libs/PHPMailer.php';
require_once __DIR__ . '/../_libs/SMTP.php';
require_once __DIR__ . '/../_libs/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration SMTP — à ajuster selon ton fournisseur
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'superkenli01@gmail.com');     // ton compte
define('SMTP_PASS', 'xtck ywuw bmrp eumx');      // App Password Gmail
define('SMTP_FROM_NAME', 'Les petites annonces GG');

// Clé secrète pour générer les tokens de confirmation
define('TOKEN_SECRET', 'change-moi-pour-une-chaine-aleatoire-longue');

/**
 * Envoie un courriel via SMTP. Retourne true en cas de succès.
 */
function envoyerCourriel($destinataire, $sujet, $messageHtml) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($destinataire);

        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body    = $messageHtml;
        $mail->AltBody = strip_tags($messageHtml);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur courriel : " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Génère un token unique pour confirmer l'inscription d'un utilisateur.
 */
function genererTokenConfirmation($noUtilisateur, $courriel) {
    return hash('sha256', $noUtilisateur . $courriel . TOKEN_SECRET);
}