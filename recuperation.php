<?php
require_once '_includes/db.php';
require_once '_includes/mail.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pageTitle = 'Récupération du mot de passe';
$navType   = 'public';
$current   = 'recuperation';
$erreur    = '';
$message   = '';

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courriel = trim($_POST['courriel'] ?? '');

    if (!filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez saisir une adresse de courriel valide.';
    } else {
        $stmt = $pdo->prepare("
            SELECT Courriel, MotDePasse
            FROM utilisateurs
            WHERE Courriel = :courriel
        ");
        $stmt->execute(['courriel' => $courriel]);
        $user = $stmt->fetch();

        if (!$user) {
            $erreur = 'Aucun compte ne correspond à cette adresse de courriel.';
        } else {
            $sujet = 'Récupération de votre mot de passe – Les petites annonces GG';
            $contenu = '
                <h2>Récupération de votre mot de passe</h2>
                <p>Bonjour,</p>
                <p>Voici le mot de passe associé à votre compte :</p>
                <p style="font-family:monospace;background:#f5f5f5;padding:10px;border:1px solid #ccc;">
                    <strong>' . e($user['MotDePasse']) . '</strong>
                </p>
                <p>Pour des raisons de sécurité, nous vous recommandons de le modifier
                dès votre prochaine connexion.</p>
                <hr>
                <p style="font-size:12px;color:#666;">Équipe KLR — Cégep Gérald-Godin</p>
            ';

            $mailEnvoye = envoyerCourriel($user['Courriel'], $sujet, $contenu);

            if ($mailEnvoye) {
                $message = 'Le mot de passe a été envoyé à <strong>' . e($user['Courriel']) . '</strong>.';
            } else {
                $message = 'L\'envoi du courriel a échoué. Pour le prototype, voici le mot de passe associé : <strong>'
                         . e($user['MotDePasse']) . '</strong>';
            }
        }
    }
}

include '_partials/header.php';
?>

<div class="auth-wrap">
  <h2>Récupération du mot de passe</h2>
  <p class="lead">Saisissez votre adresse de courriel. Le mot de passe associé vous sera envoyé.</p>

  <?php if ($erreur): ?>
    <div class="alert danger">
      <h4>Erreur</h4>
      <p><?= e($erreur) ?></p>
    </div>
  <?php endif; ?>

  <?php if ($message): ?>
    <div class="alert ok">
      <h4>Demande traitée</h4>
      <p><?= $message ?></p>
    </div>
  <?php endif; ?>

  <form method="post" action="recuperation.php" onsubmit="return validerRecuperation();">
    <div class="field">
      <label for="courriel">Adresse de courriel</label>
      <input type="email" id="courriel" name="courriel" placeholder="prenom.nom@cgodin.qc.ca" required>
      <div id="erreur-courriel" class="err"></div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Envoyer le mot de passe</button>
  </form>

  <div class="auth-links">
    <a href="index.php">Retour à la connexion</a>
  </div>
</div>

<script>
function validerRecuperation() {
    const courriel = document.getElementById('courriel').value.trim();
    const zoneErreur = document.getElementById('erreur-courriel');
    const regexCourriel = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    zoneErreur.textContent = '';

    if (!regexCourriel.test(courriel)) {
        zoneErreur.textContent = 'Adresse de courriel invalide.';
        return false;
    }

    return true;
}
</script>

<?php include '_partials/footer.php'; ?>