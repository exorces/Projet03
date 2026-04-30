<?php
$pageTitle = 'Modifier le mot de passe';
$navType   = 'user';
$current   = 'profil';
include '_partials/header.php';
?>

<h2 class="page-title">Modifier le mot de passe</h2>
<p class="page-sub">5 à 15 caractères, lettres et chiffres combinés. Sensible à la casse.</p>

<div class="auth-wrap" style="margin-top:0;">
  <div class="field">
    <label>Mot de passe actuel</label>
    <input type="password">
  </div>
  <div class="field">
    <label>Nouveau mot de passe</label>
    <input type="password">
  </div>
  <div class="field">
    <label>Confirmer le nouveau mot de passe</label>
    <input type="password">
  </div>

  <div class="actions-bar">
    <button class="btn btn-primary">Modifier</button>
    <a href="profil.php" class="btn btn-ghost">Annuler</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
