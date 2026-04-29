<?php
$pageTitle = 'Nettoyage de la base';
$navType   = 'admin';
$current   = 'nettoyage';
include 'header.php';
?>

<h2 class="page-title">Nettoyage de la base de données</h2>
<p class="page-sub">Suppressions physiques. Ces opérations sont irréversibles.</p>

<div class="cleanup-block warn">
  <h3>Utilisateurs non confirmés</h3>
  <p style="color:var(--ink-soft); margin:0 0 16px;">
    Suppression physique des comptes inscrits depuis plus d'un mois sans confirmation par courriel.
  </p>
  <div class="cleanup-stat">
    <div class="big warn">8</div>
    <div style="font-size:13px; color:var(--ink-soft);">comptes en attente depuis plus de 30 jours seront supprimés.</div>
  </div>
  <button class="btn btn-danger">Supprimer ces comptes</button>
</div>

<div class="cleanup-block danger">
  <h3>Annonces retirées</h3>
  <p style="color:var(--ink-soft); margin:0 0 16px;">
    Suppression physique des annonces dont l'état logique est « Retiré ».
  </p>
  <div class="cleanup-stat">
    <div class="big danger">23</div>
    <div style="font-size:13px; color:var(--ink-soft);">annonces retirées seront supprimées définitivement.</div>
  </div>
  <button class="btn btn-danger">Supprimer ces annonces</button>
</div>

<?php include 'footer.php'; ?>
