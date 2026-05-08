<?php
require_once '_includes/db.php';

if (!isset($_SESSION['Courriel'])) {
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['Statut']) || $_SESSION['Statut'] != 1) {
    header('Location: annonces.php');
    exit;
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['supprimer_comptes'])) {
        // Delete unconfirmed accounts (Statut = 0) created more than 30 days ago
        $stmt = $pdo->prepare("
            DELETE FROM utilisateurs
            WHERE Statut = 0
              AND Creation < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $nb = $stmt->rowCount();
        $message = "$nb compte(s) non confirmé(s) supprimé(s).";

    } elseif (isset($_POST['supprimer_annonces'])) {
        // Delete withdrawn annonces (Etat = 3)
        $stmt = $pdo->prepare("DELETE FROM annonces WHERE Etat = 3");
        $stmt->execute();
        $nb = $stmt->rowCount();
        $message = "$nb annonce(s) retirée(s) supprimée(s).";
    }
}

// Real counts
$nbComptesAttente = (int)$pdo->query("
    SELECT COUNT(*) FROM utilisateurs
    WHERE Statut = 0
      AND Creation < DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

$nbAnnoncesRetirees = (int)$pdo->query("
    SELECT COUNT(*) FROM annonces WHERE Etat = 3
")->fetchColumn();

$pageTitle = 'Nettoyage de la base';
$navType   = 'admin';
$current   = 'nettoyage';

include '_partials/header.php';
?>

<h2 class="page-title">Nettoyage de la base de données</h2>
<p class="page-sub">Suppressions physiques. Ces opérations sont irréversibles.</p>

<?php if ($message): ?>
  <div class="alert ok" style="margin-bottom:20px;">
    <h4>Opération réussie</h4>
    <p><?= e($message) ?></p>
  </div>
<?php endif; ?>

<form method="post" action="admin-nettoyage.php"
      onsubmit="return confirm('Cette suppression est irréversible. Confirmer ?');">

  <div class="cleanup-block warn">
    <h3>Utilisateurs non confirmés</h3>
    <p style="color:var(--ink-soft);margin:0 0 16px;">
      Suppression physique des comptes inscrits depuis plus de 30 jours sans confirmation par courriel.
    </p>
    <div class="cleanup-stat">
      <div class="big warn"><?= e($nbComptesAttente) ?></div>
      <div style="font-size:13px;color:var(--ink-soft);">
        compte(s) en attente depuis plus de 30 jours seront supprimé(s).
      </div>
    </div>
    <button type="submit" name="supprimer_comptes" class="btn btn-danger"
      <?= $nbComptesAttente === 0 ? 'disabled' : '' ?>>
      Supprimer ces comptes
    </button>
  </div>

  <div class="cleanup-block danger">
    <h3>Annonces retirées</h3>
    <p style="color:var(--ink-soft);margin:0 0 16px;">
      Suppression physique des annonces dont l'état logique est « Retiré ».
    </p>
    <div class="cleanup-stat">
      <div class="big danger"><?= e($nbAnnoncesRetirees) ?></div>
      <div style="font-size:13px;color:var(--ink-soft);">
        annonce(s) retirée(s) seront supprimée(s) définitivement.
      </div>
    </div>
    <button type="submit" name="supprimer_annonces" class="btn btn-danger"
      <?= $nbAnnoncesRetirees === 0 ? 'disabled' : '' ?>>
      Supprimer ces annonces
    </button>
  </div>

</form>

<?php include '_partials/footer.php'; ?>
