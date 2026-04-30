<?php
$pageTitle = 'Récupération du mot de passe';
$navType   = 'public';
$current   = 'recuperation';
include '_partials/header.php';
?>

<div class="auth-wrap">
  <h2>Récupération du mot de passe</h2>
  <p class="lead">Saisissez votre adresse de courriel. Le mot de passe associé vous sera envoyé.</p>

  <div class="field">
    <label>Adresse de courriel</label>
    <input type="email" placeholder="prenom.nom@cgodin.qc.ca">
  </div>

  <button class="btn btn-primary btn-block">Envoyer le mot de passe</button>

  <div class="auth-links">
    <a href="index.php">← Retour à la connexion</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
