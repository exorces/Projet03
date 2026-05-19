<?php
require_once '_includes/db.php';

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['Nom']) || empty($_SESSION['Prenom'])) {
    header('Location: profil.php');
    exit;
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function formatDate($d) {
    return $d ? date('Y-m-d, H\hi', strtotime($d)) : '—';
}

function formatPrix($prix) {
    return ((float)$prix <= 0) ? 'N/A' : number_format((float)$prix, 2, ',', ' ') . ' $';
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: mes-annonces.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.NoAnnonce, a.Parution, a.DescriptionAbregee, a.Prix, a.Etat,
           c.Categorie AS NomCategorie
    FROM annonces a
    JOIN categories c ON a.Categorie = c.NoCategorie
    WHERE a.NoAnnonce = :id
      AND a.NoUtilisateur = :user
      AND a.Etat <> 3
");
$stmt->execute(['id' => $id, 'user' => $_SESSION['NoUtilisateur']]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: mes-annonces.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $pdo->prepare("
        UPDATE annonces SET Etat = 3, MiseAJour = NOW()
        WHERE NoAnnonce = :id AND NoUtilisateur = :user
    ")->execute(['id' => $id, 'user' => $_SESSION['NoUtilisateur']]);

    header('Location: mes-annonces.php');
    exit;
}

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
    <h3><?= e($annonce['DescriptionAbregee']) ?></h3>
    <div class="meta">
      Annonce #<?= e($annonce['NoAnnonce']) ?> &middot;
      <?= e($annonce['NomCategorie']) ?> &middot;
      <?= e(formatPrix($annonce['Prix'])) ?> &middot;
      Publiée le <?= e(formatDate($annonce['Parution'])) ?>
    </div>
  </div>

  <form method="post" action="annonce-retrait.php?id=<?= e($id) ?>">
    <div class="actions-bar">
      <button type="submit" name="confirmer" class="btn btn-danger">Confirmer le retrait</button>
      <a href="mes-annonces.php" class="btn btn-ghost">Annuler</a>
    </div>
  </form>
</div>

<?php include '_partials/footer.php'; ?>
