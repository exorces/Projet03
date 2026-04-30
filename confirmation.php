<?php
$pageTitle = 'Inscription confirmée';
$navType   = 'public';
$current   = 'confirmation';
include '_partials/header.php';
?>

<div class="auth-wrap">
  <h2>Inscription confirmée</h2>
  <div class="alert ok" style="margin-top:16px;">
    <h4>Bienvenue !</h4>
    <p>Votre adresse de courriel a été confirmée. Vous pouvez maintenant vous connecter.</p>
  </div>
  <a href="index.php" class="btn btn-primary btn-block" style="margin-top:12px;">Aller à la connexion</a>
</div>

<?php include '_partials/footer.php'; ?>
