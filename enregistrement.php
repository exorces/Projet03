<?php
$pageTitle = 'Enregistrement';
$navType   = 'public';
$current   = 'enregistrement';
include 'header.php';
?>

<div class="auth-wrap">
  <h2>Créer un compte</h2>
  <p class="lead">Un courriel de confirmation vous sera envoyé.</p>

  <div class="field">
    <label>Adresse de courriel</label>
    <input type="email" placeholder="prenom.nom@cgodin.qc.ca">
    <div class="hint">Servira d'identifiant unique.</div>
  </div>
  <div class="field">
    <label>Confirmer l'adresse de courriel</label>
    <input type="email">
  </div>
  <div class="field">
    <label>Mot de passe</label>
    <input type="password">
    <div class="hint">5 à 15 caractères, lettres et chiffres combinés. Sensible à la casse.</div>
  </div>
  <div class="field">
    <label>Confirmer le mot de passe</label>
    <input type="password">
  </div>

  <button class="btn btn-primary btn-block">Soumettre</button>

  <div class="auth-links">
    <a href="index.php">← Retour à la connexion</a>
  </div>
</div>

<?php include 'footer.php'; ?>
