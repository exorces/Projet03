<?php
$pageTitle = 'Contacter l\'auteur';
$navType   = 'user';
$current   = 'annonces';
include '_partials/header.php';
?>

<h2 class="page-title">Contacter l'auteur</h2>
<p class="page-sub">À propos de l'annonce #1042 — Vélo de route Trek Domane AL2</p>

<div class="auth-wrap" style="margin-top:0;">
  <div class="field">
    <label>Destinataire</label>
    <input type="text" value="Tremblay, Sophie" disabled style="background:var(--paper-dim);">
  </div>
  <div class="field">
    <label>De la part de</label>
    <input type="text" value="Roux, Ken-Li &lt;ken-li.roux@cgodin.qc.ca&gt;" disabled style="background:var(--paper-dim);">
  </div>
  <div class="field">
    <label>Objet</label>
    <input type="text" value="Au sujet de votre annonce — Vélo Trek Domane AL2">
  </div>
  <div class="field">
    <label>Message</label>
    <textarea rows="6" placeholder="Bonjour, je suis intéressé par votre annonce…"></textarea>
  </div>

  <div class="actions-bar">
    <button class="btn btn-primary">Envoyer</button>
    <a href="annonce-detail.php" class="btn btn-ghost">Annuler</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
