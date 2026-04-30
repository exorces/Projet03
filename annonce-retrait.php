<?php
$pageTitle = 'Retirer une annonce';
$navType   = 'user';
$current   = 'mes-annonces';
include '_partials/header.php';
?>

<h2 class="page-title">Retirer l'annonce</h2>
<p class="page-sub">Confirmation requise — cette opération change l'état à « Retiré ».</p>

<div class="confirm-box">
  <div class="alert danger">
    <h4>Êtes-vous certain de vouloir retirer cette annonce ?</h4>
    <p>L'annonce ne sera plus visible des autres utilisateurs. Elle restera dans votre historique avec l'état « Retiré ».</p>
  </div>

  <div class="summary-card">
    <div class="label">Annonce concernée</div>
    <h3>Console PSP-3000 + 12 jeux + cartes mémoire</h3>
    <div class="meta">Annonce #1015 · À vendre · 140,00 $ · Publiée le 2026-04-15</div>
  </div>

  <div class="actions-bar">
    <button class="btn btn-danger">Confirmer le retrait</button>
    <a href="mes-annonces.php" class="btn btn-ghost">Annuler</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
