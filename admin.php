<?php
$pageTitle = 'Module administrateur';
$navType   = 'admin';
$current   = '';
include '_partials/header.php';
?>

<h2 class="page-title">Module administrateur</h2>
<p class="page-sub">Bienvenue, administrateur. Choisissez une opération.</p>

<div class="admin-cards">
  <a href="annonces.php" class="admin-card">
    <div class="num">01</div>
    <h3>Toutes les annonces</h3>
    <p>Visualiser et gérer l'ensemble des annonces de la plateforme.</p>
  </a>
  <a href="admin-utilisateurs.php" class="admin-card green">
    <div class="num">02</div>
    <h3>Tous les utilisateurs</h3>
    <p>Liste complète, statistiques et historique de connexions.</p>
  </a>
  <a href="admin-nettoyage.php" class="admin-card warn">
    <div class="num">03</div>
    <h3>Nettoyage de la base</h3>
    <p>Suppression des comptes non confirmés et des annonces retirées.</p>
  </a>
</div>

<?php include '_partials/footer.php'; ?>
