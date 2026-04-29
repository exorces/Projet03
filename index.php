<?php
$pageTitle = 'Connexion';
$navType   = 'public';
$current   = 'connexion';
include 'header.php';
?>

<div class="auth-wrap">
  <h2>Connexion</h2>
  <p class="lead">Entrez vos identifiants pour accéder à votre compte.</p>

  <div class="field">
    <label for="login-email">Adresse de courriel</label>
    <input type="email" id="login-email" placeholder="prenom.nom@cgodin.qc.ca">
  </div>

  <div class="field">
    <label for="login-pwd">Mot de passe</label>
    <input type="password" id="login-pwd" placeholder="••••••••">
  </div>

  <button class="btn btn-primary btn-block">Se connecter</button>

  <div class="auth-links">
    <a href="enregistrement.php">Créer un compte</a>
    <a href="recuperation.php">Mot de passe oublié ?</a>
  </div>
</div>

<?php include 'footer.php'; ?>
