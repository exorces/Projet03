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

function telPublic($valeur) {
    if (!$valeur) return null;
    $dernier = substr($valeur, -1);
    if ($dernier === 'P') return substr($valeur, 0, -1);
    return null;
}

$id = (int)($_GET['id'] ?? 0);

$annonce = null;

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT
            a.NoAnnonce, a.NoUtilisateur, a.Parution, a.MiseAJour,
            a.DescriptionAbregee, a.DescriptionComplete, a.Prix, a.Photo, a.Etat,
            c.Categorie AS NomCategorie,
            u.Nom, u.Prenom, u.NoTelMaison, u.NoTelTravail, u.NoTelCellulaire
        FROM annonces a
        JOIN categories c ON a.Categorie = c.NoCategorie
        JOIN utilisateurs u ON a.NoUtilisateur = u.NoUtilisateur
        WHERE a.NoAnnonce = :id
    ");
    $stmt->execute(['id' => $id]);
    $annonce = $stmt->fetch();

    if ($annonce && (int)$annonce['Etat'] === 3
        && (int)$annonce['NoUtilisateur'] !== (int)$_SESSION['NoUtilisateur']) {
        $annonce = null;
    }
}

$pageTitle = $annonce ? e($annonce['DescriptionAbregee']) : 'Annonce introuvable';
$navType   = 'user';
$current   = 'annonces';

if ($annonce) {
    $estAuteur     = (int)$annonce['NoUtilisateur'] === (int)$_SESSION['NoUtilisateur'];
    $nomFichier    = $annonce['Photo'] ? basename($annonce['Photo']) : null;
    $photoSrc      = null;
    if ($nomFichier) {
        $grand    = 'photos-annonce/' . $nomFichier;
        $photoSrc = file_exists($grand) ? $grand : null;
    }
    $telMaison     = telPublic($annonce['NoTelMaison']);
    $telTravail    = telPublic($annonce['NoTelTravail']);
    $telCellulaire = telPublic($annonce['NoTelCellulaire']);
}

include '_partials/header.php';
?>

<?php if (!$annonce): ?>

<div class="auth-wrap">
  <div class="alert danger" style="margin-top:16px;">
    <h4>Annonce introuvable</h4>
    <p>Cette annonce n'existe pas ou n'est plus disponible.</p>
  </div>
  <a href="annonces.php" class="btn btn-primary" style="margin-top:12px;">← Retour à la liste</a>
</div>

<?php else: ?>

<h2 class="page-title"><?= e($annonce['DescriptionAbregee']) ?></h2>
<p class="page-sub">
  Annonce #<?= e($annonce['NoAnnonce']) ?> &middot;
  Publiée le <?= e(formatDate($annonce['Parution'])) ?> &middot;
  Mise à jour le <?= e(formatDate($annonce['MiseAJour'])) ?>
</p>

<div class="detail-grid">

  <div>
    <?php if ($photoSrc): ?>
      <img src="<?= e($photoSrc) ?>" alt="Photo de l'annonce"
           style="max-width:600px;width:100%;height:auto;border-radius:4px;border:1px solid #ddd;">
    <?php else: ?>
      <div style="width:100%;max-width:600px;height:200px;background:#eee;border-radius:4px;
                  display:flex;align-items:center;justify-content:center;color:#999;">
        Aucune photo
      </div>
    <?php endif; ?>
  </div>

  <div class="detail-info">
    <div class="price-big"><?= e(formatPrix($annonce['Prix'])) ?></div>

    <dl>
      <dt>Catégorie</dt>
      <dd><?= e($annonce['NomCategorie']) ?></dd>

      <dt>Auteur</dt>
      <dd>
        <?php if ($estAuteur): ?>
          Vous (cette annonce)
        <?php else: ?>
          <a href="contact.php?id=<?= e($annonce['NoAnnonce']) ?>">
            <?= e($annonce['Nom'] . ', ' . $annonce['Prenom']) ?>
          </a>
        <?php endif; ?>
      </dd>

      <?php if ($telMaison): ?>
        <dt>Tél. maison</dt>
        <dd><?= e($telMaison) ?></dd>
      <?php endif; ?>

      <?php if ($telTravail): ?>
        <dt>Tél. travail</dt>
        <dd><?= e($telTravail) ?></dd>
      <?php endif; ?>

      <?php if ($telCellulaire): ?>
        <dt>Tél. cellulaire</dt>
        <dd><?= e($telCellulaire) ?></dd>
      <?php endif; ?>

      <dt>Date de parution</dt>
      <dd><?= e(formatDate($annonce['Parution'])) ?></dd>

      <dt>Dernière MAJ</dt>
      <dd><?= e(formatDate($annonce['MiseAJour'])) ?></dd>
    </dl>

    <div class="detail-desc">
      <?= nl2br(e($annonce['DescriptionComplete'])) ?>
    </div>

    <div class="actions-bar">
      <?php if (!$estAuteur): ?>
        <a href="contact.php?id=<?= e($annonce['NoAnnonce']) ?>" class="btn btn-primary">Contacter l'auteur</a>
      <?php endif; ?>
      <a href="annonces.php" class="btn btn-ghost">← Retour à la liste</a>
    </div>
  </div>

</div>

<?php endif; ?>

<?php include '_partials/footer.php'; ?>
