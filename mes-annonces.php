<?php
require_once '_includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['Nom']) || empty($_SESSION['Prenom'])) {
    header('Location: profil.php');
    exit;
}

$pageTitle = 'Mes annonces';
$navType   = 'user';
$current   = 'mes-annonces';

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

function selected($condition) {
    return $condition ? 'selected' : '';
}

function formatDateAnnonce($date) {
    if (!$date) {
        return '';
    }

    return date('Y-m-d, H\hi', strtotime($date));
}

function formatPrix($prix) {
    if ((float)$prix <= 0) {
        return 'N/A';
    }

    return number_format((float)$prix, 2, ',', ' ') . ' $';
}

function imageAnnonce($photo, $noAnnonce = null) {
    $lien = $noAnnonce ? 'annonce-detail.php?id=' . (int)$noAnnonce : null;

    if (!$photo) {
        $div = '<div class="thumb" style="width:144px;height:100px;"></div>';
        return $lien ? '<a href="' . e($lien) . '">' . $div . '</a>' : $div;
    }

    $nomFichier = basename($photo);
    $vignette = 'photos-annonce/vignette-' . $nomFichier;
    $source = file_exists($vignette) ? $vignette : $photo;

    if (!file_exists($source)) {
        $div = '<div class="thumb" style="width:144px;height:100px;"></div>';
        return $lien ? '<a href="' . e($lien) . '">' . $div . '</a>' : $div;
    }

    $div = '<div class="thumb" style="width:144px;height:100px;"><img src="' . e($source) . '" alt="Photo de l\'annonce" style="width:144px;height:100px;object-fit:cover;"></div>';
    return $lien ? '<a href="' . e($lien) . '">' . $div . '</a>' : $div;
}

function lienPagination($page) {
    $params = $_GET;
    unset($params['action'], $params['id']);
    $params['page'] = $page;
    return 'mes-annonces.php?' . http_build_query($params);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $action = $_POST['action'];
    $id = (int)$_POST['id'];

    if ($id > 0 && $action === 'toggle') {
        $stmt = $pdo->prepare("
            UPDATE annonces
            SET Etat = CASE
                    WHEN Etat = 1 THEN 2
                    WHEN Etat = 2 THEN 1
                    ELSE Etat
                END,
                MiseAJour = NOW()
            WHERE NoAnnonce = :id
              AND NoUtilisateur = :user
              AND Etat IN (1, 2)
        ");
        $stmt->execute([
            'id' => $id,
            'user' => $_SESSION['NoUtilisateur']
        ]);
    }

    header('Location: mes-annonces.php');
    exit;
}

$tri = $_GET['tri'] ?? 'date';
$ordre = $_GET['ordre'] ?? 'desc';
$parPage = (int)($_GET['parpage'] ?? 10);
$page = (int)($_GET['page'] ?? 1);

if (!in_array($parPage, [5, 10, 15, 20], true)) {
    $parPage = 10;
}

if ($page < 1) {
    $page = 1;
}

$ordreSql = ($ordre === 'asc') ? 'ASC' : 'DESC';

switch ($tri) {
    case 'categorie':
        $orderBy = "c.Categorie $ordreSql, a.Parution DESC";
        break;
    case 'description':
        $orderBy = "a.DescriptionAbregee $ordreSql, a.Parution DESC";
        break;
    case 'etat':
        $orderBy = "a.Etat $ordreSql, a.Parution DESC";
        break;
    case 'date':
    default:
        $tri = 'date';
        $orderBy = "a.Parution $ordreSql, a.NoAnnonce DESC";
        break;
}

$stmtCount = $pdo->prepare("
    SELECT COUNT(*)
    FROM annonces
    WHERE NoUtilisateur = :user
");
$stmtCount->execute(['user' => $_SESSION['NoUtilisateur']]);
$total = (int)$stmtCount->fetchColumn();

$totalPages = max(1, (int)ceil($total / $parPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $parPage;

$stmt = $pdo->prepare("
    SELECT
        a.NoAnnonce,
        a.Parution,
        a.Categorie,
        a.DescriptionAbregee,
        a.Prix,
        a.Photo,
        a.Etat,
        c.Categorie AS NomCategorie
    FROM annonces a
    JOIN categories c ON a.Categorie = c.NoCategorie
    WHERE a.NoUtilisateur = :user
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':user', $_SESSION['NoUtilisateur'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$annonces = $stmt->fetchAll();

$etatLibelles = [
    1 => 'Actif',
    2 => 'Inactif',
    3 => 'Retiré'
];

$etatClasses = [
    1 => 'actif',
    2 => 'inactif',
    3 => 'retire'
];

include '_partials/header.php';
?>

<h2 class="page-title">Mes annonces</h2>
<p class="page-sub">Toutes vos annonces en ordre chronologique inverse.</p>

<form method="get" action="mes-annonces.php" class="toolbar">
  <div class="group">
    <label for="tri">Trier par</label>
    <select id="tri" name="tri">
      <option value="date" <?= selected($tri === 'date') ?>>Date de parution</option>
      <option value="categorie" <?= selected($tri === 'categorie') ?>>Catégorie</option>
      <option value="description" <?= selected($tri === 'description') ?>>Description abrégée</option>
      <option value="etat" <?= selected($tri === 'etat') ?>>État</option>
    </select>

    <select name="ordre">
      <option value="desc" <?= selected($ordre === 'desc') ?>>Décroissant</option>
      <option value="asc" <?= selected($ordre === 'asc') ?>>Croissant</option>
    </select>
  </div>

  <div class="group">
    <span class="count"><strong><?= e($total) ?></strong> annonce(s) au total</span>
  </div>

  <div class="group">
    <label for="parpage">Par page</label>
    <select id="parpage" name="parpage">
      <option value="5" <?= selected($parPage === 5) ?>>5</option>
      <option value="10" <?= selected($parPage === 10) ?>>10</option>
      <option value="15" <?= selected($parPage === 15) ?>>15</option>
      <option value="20" <?= selected($parPage === 20) ?>>20</option>
    </select>

    <button type="submit" class="btn">Appliquer</button>
    <a href="annonce-form.php" class="btn btn-primary btn-sm">+ Nouvelle annonce</a>
  </div>
</form>

<div class="annonces-list">
  <?php if (!$annonces): ?>
    <div class="alert warn" style="margin-top:15px;">
      <h4>Aucune annonce</h4>
      <p>Vous n'avez pas encore publié d'annonce.</p>
    </div>
  <?php endif; ?>

  <?php foreach ($annonces as $index => $annonce): ?>
    <?php
      $etat = (int)$annonce['Etat'];
      $etatTexte = $etatLibelles[$etat] ?? 'Inconnu';
      $etatClasse = $etatClasses[$etat] ?? 'inactif';
    ?>

    <article class="annonce-row">
      <div class="num"><?= e($offset + $index + 1) ?></div>

      <?= imageAnnonce($annonce['Photo'], $annonce['NoAnnonce']) ?>

      <div class="body">
        <div class="meta-line">
          <span><?= e(formatDateAnnonce($annonce['Parution'])) ?></span>
          <span class="id">Annonce #<?= e($annonce['NoAnnonce']) ?></span>
          <span class="cat"><?= e($annonce['NomCategorie']) ?></span>
          <span class="badge <?= e($etatClasse) ?>"><?= e($etatTexte) ?></span>
        </div>

        <h3>
          <a href="annonce-detail.php?id=<?= e($annonce['NoAnnonce']) ?>">
            <?= e($annonce['DescriptionAbregee']) ?>
          </a>
        </h3>

        <div class="author">
          Prix : <strong><?= e(formatPrix($annonce['Prix'])) ?></strong>
        </div>
      </div>

      <div class="actions">
        <?php if ($etat !== 3): ?>
          <a href="annonce-form.php?id=<?= e($annonce['NoAnnonce']) ?>" class="btn btn-sm btn-ghost">Modifier</a>

          <form method="post" action="mes-annonces.php" style="display:inline;">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e($annonce['NoAnnonce']) ?>">
            <button type="submit" class="btn btn-sm btn-ghost">
              <?= $etat === 1 ? 'Désactiver' : 'Activer' ?>
            </button>
          </form>

          <a href="annonce-retrait.php?id=<?= e($annonce['NoAnnonce']) ?>" class="btn btn-sm btn-danger">Retirer</a>
        <?php else: ?>
          <span class="btn btn-sm btn-ghost">Aucune action</span>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="<?= e(lienPagination(1)) ?>">«</a>
    <a href="<?= e(lienPagination($page - 1)) ?>">‹</a>
  <?php else: ?>
    <span class="disabled">«</span>
    <span class="disabled">‹</span>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <?php if ($i === $page): ?>
      <span class="current"><?= e($i) ?></span>
    <?php else: ?>
      <a href="<?= e(lienPagination($i)) ?>"><?= e($i) ?></a>
    <?php endif; ?>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="<?= e(lienPagination($page + 1)) ?>">›</a>
    <a href="<?= e(lienPagination($totalPages)) ?>">»</a>
  <?php else: ?>
    <span class="disabled">›</span>
    <span class="disabled">»</span>
  <?php endif; ?>
</div>

<?php include '_partials/footer.php'; ?>