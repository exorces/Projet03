<?php
require_once '_includes/db.php';

$pageTitle = 'Confirmation d\'inscription';
$navType   = 'public';
$current   = 'confirmation';

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$statut = 'invalide'; // 'ok' | 'deja' | 'invalide'

$tokenBrut = $_GET['token'] ?? '';

if ($tokenBrut !== '') {
    $decoded = base64_decode($tokenBrut, true);

    if ($decoded !== false && strpos($decoded, '|') !== false) {
        [$noUtil, $hash] = explode('|', $decoded, 2);
        $noUtil = (int)$noUtil;

        if ($noUtil > 0) {
            $stmt = $pdo->prepare("
                SELECT Courriel, MotDePasse, Statut
                FROM utilisateurs
                WHERE NoUtilisateur = :no
            ");
            $stmt->execute(['no' => $noUtil]);
            $user = $stmt->fetch();

            if ($user) {
                $hashAttendu = hash('sha256', $user['Courriel'] . $user['MotDePasse']);

                if (hash_equals($hashAttendu, $hash)) {
                    if ((int)$user['Statut'] === 0) {
                        $pdo->prepare("UPDATE utilisateurs SET Statut = 9 WHERE NoUtilisateur = :no")
                            ->execute(['no' => $noUtil]);
                        $statut = 'ok';
                    } else {
                        $statut = 'deja';
                    }
                }
            }
        }
    }
}

include '_partials/header.php';
?>

<div class="auth-wrap">
  <h2>Confirmation d'inscription</h2>

  <?php if ($statut === 'ok'): ?>
    <div class="alert ok" style="margin-top:16px;">
      <h4>Bienvenue !</h4>
      <p>Votre adresse de courriel a été confirmée. Vous pouvez maintenant vous connecter.</p>
    </div>
    <a href="index.php" class="btn btn-primary" style="margin-top:12px;">Aller à la connexion</a>

  <?php elseif ($statut === 'deja'): ?>
    <div class="alert warn" style="margin-top:16px;">
      <h4>Déjà confirmé</h4>
      <p>Votre inscription a déjà été confirmée. Vous pouvez vous connecter directement.</p>
    </div>
    <a href="index.php" class="btn btn-primary" style="margin-top:12px;">Aller à la connexion</a>

  <?php else: ?>
    <div class="alert danger" style="margin-top:16px;">
      <h4>Lien invalide</h4>
      <p>Ce lien de confirmation n'est plus valide ou a été modifié. Veuillez vous réinscrire ou contacter l'administrateur.</p>
    </div>
    <a href="enregistrement.php" class="btn btn-primary" style="margin-top:12px;">Créer un compte</a>
  <?php endif; ?>

  <div class="auth-links" style="margin-top:12px;">
    <a href="index.php">← Retour à la connexion</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
