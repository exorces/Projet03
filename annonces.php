<?php
require_once '_includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Annonces';
$navType   = 'user';
$current   = 'annonces';

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

function imageAnnonce($photo) {
    if (!$photo) {
        return '<div class="thumb" style="width:144px;height:100px;"></div>';
    }

    $nomFichier = basename($photo);
    $vignette = 'photos-annonce/vignette-' . $nomFichier;
    $source = file_exists($vignette) ? $vignette : $photo;

    if (!file_exists($source)) {
        return '<div class="thumb" style="width:144px;height:100px;"></div>';
    }

    return '<div class="thumb" style="width:144px;height:100px;"><img src="' . e($source) . '" alt="Photo de l\'annonce" style="width:144px;height:100px;object-fit:cover;"></div>';
}

function lienPagination($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'annonces.php?' . http_build_query($params);
}

$categories = $pdo->query("
    SELECT NoCategorie, Categorie
    FROM categories
    ORDER BY NoCategorie
")->fetchAll();

$du = trim($_GET['du'] ?? '');
$au = trim($_GET['au'] ?? '');
$categorie = (int)($_GET['categorie'] ?? 0);
$q = trim($_GET['q'] ?? '');
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
    case 'auteur':
        $orderBy = "u.Nom $ordreSql, u.Prenom $ordreSql, a.Parution DESC";
        break;
    case 'categorie':
        $orderBy = "c.Categorie $ordreSql, a.Parution DESC";
        break;
    case 'date':
    default:
        $tri = 'date';
        $orderBy = "a.Parution $ordreSql, a.NoAnnonce DESC";
        break;
}

$where = ['a.Etat = 1'];
$params = [];

if ($du !== '') {
    $where[] = 'a.Parution >= :du';
    $params['du'] = $du . ' 00:00:00';
}

if ($au !== '') {
    $where[] = 'a.Parution <= :au';
    $params['au'] = $au . ' 23:59:59';
}

if ($categorie > 0) {
    $where[] = 'a.Categorie = :categorie';
    $params['categorie'] = $categorie;
}

if ($q !== '') {
    $where[] = '(a.DescriptionAbregee LIKE :q OR a.DescriptionComplete LIKE :q OR u.Nom LIKE :q OR u.Prenom LIKE :q)';
    $params['q'] = '%' . $q . '%';
}

$whereSql = implode(' AND ', $where);

$sqlCount = "
    SELECT COUNT(*)
    FROM annonces a
    JOIN utilisateurs u ON a.NoUtilisateur = u.NoUtilisateur
    JOIN categories c ON a.Categorie = c.NoCategorie
    WHERE $whereSql
";

$stmtCount = $pdo->prepare($sqlCount);

foreach ($params as $cle => $valeur) {
    if ($cle === 'categorie') {
        $stmtCount->bindValue(':' . $cle, $valeur, PDO::PARAM_INT);
    } else {
        $stmtCount->bindValue(':' . $cle, $valeur);
    }
}

$stmtCount->execute();
$total = (int)$stmtCount->fetchColumn();

$totalPages = max(1, (int)ceil($total / $parPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $parPage;

$sql = "
    SELECT
        a.NoAnnonce,
        a.NoUtilisateur,
        a.Parution,
        a.DescriptionAbregee,
        a.Prix,
        a.Photo,
        u.Nom,
        u.Prenom,
        c.Categorie AS NomCategorie
    FROM annonces a
    JOIN utilisateurs u ON a.NoUtilisateur = u.NoUtilisateur
    JOIN categories c ON a.Categorie = c.NoCategorie
    WHERE $whereSql
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

foreach ($params as $cle => $valeur) {
    if ($cle === 'categorie') {
        $stmt->bindValue(':' . $cle, $valeur, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':' . $cle, $valeur);
    }
}

$stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$annonces = $stmt->fetchAll();

include '_partials/header.php';
?>

<h2 class="page-title">Annonces récentes</h2>
<p class="page-sub">Toutes les annonces actives, en ordre chronologique inverse de parution.</p>

<form method="get" action="annonces.php" class="search-box">
  <div class="field">
    <label for="du">Du</label>
    <input type="date" id="du" name="du" value="<?= e($du) ?>">
  </div>

  <div class="field">
    <label for="au">Au</label>
    <input type="date" id="au" name="au" value="<?= e($au) ?>">
  </div>

  <div class="field">
    <label for="categorie">Catégorie</label>
    <select id="categorie" name="categorie">
      <option value="0">Toutes</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat['NoCategorie']) ?>" <?= selected($categorie === (int)$cat['NoCategorie']) ?>>
          <?= e($cat['Categorie']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="field">
    <label for="q">Description / Auteur</label>
    <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Mots-clés">
  </div>

  <input type="hidden" name="tri" value="<?= e($tri) ?>">
  <input type="hidden" name="ordre" value="<?= e($ordre) ?>">
  <input type="hidden" name="parpage" value="<?= e($parPage) ?>">

  <button type="submit" class="btn">Rechercher</button>
  <a href="annonces.php" class="btn btn-ghost">Réinitialiser</a>
</form>

<form method="get" action="annonces.php" class="toolbar">
  <input type="hidden" name="du" value="<?= e($du) ?>">
  <input type="hidden" name="au" value="<?= e($au) ?>">
  <input type="hidden" name="categorie" value="<?= e($categorie) ?>">
  <input type="hidden" name="q" value="<?= e($q) ?>">

  <div class="group">
    <label for="tri">Trier par</label>
    <select id="tri" name="tri">
      <option value="date" <?= selected($tri === 'date') ?>>Date de parution</option>
      <option value="auteur" <?= selected($tri === 'auteur') ?>>Nom de l'auteur</option>
      <option value="categorie" <?= selected($tri === 'categorie') ?>>Catégorie</option>
    </select>

    <select name="ordre">
      <option value="desc" <?= selected($ordre === 'desc') ?>>Décroissant</option>
      <option value="asc" <?= selected($ordre === 'asc') ?>>Croissant</option>
    </select>
  </div>

  <div class="group">
    <span class="count"><strong><?= e($total) ?></strong> annonce(s) active(s)</span>
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
  </div>
</form>

<div class="annonces-list">
  <?php if (!$annonces): ?>
    <div class="alert warn" style="margin-top:15px;">
      <h4>Aucune annonce</h4>
      <p>Aucune annonce ne correspond aux critères sélectionnés.</p>
    </div>
  <?php endif; ?>

  <?php foreach ($annonces as $index => $annonce): ?>
    <article class="annonce-row">
      <div class="num"><?= e($offset + $index + 1) ?></div>

      <?= imageAnnonce($annonce['Photo']) ?>

      <div class="body">
        <div class="meta-line">
          <span><?= e(formatDateAnnonce($annonce['Parution'])) ?></span>
          <span class="id">Annonce #<?= e($annonce['NoAnnonce']) ?></span>
          <span class="cat"><?= e($annonce['NomCategorie']) ?></span>
        </div>

        <h3>
          <a href="annonce-detail.php?id=<?= e($annonce['NoAnnonce']) ?>">
            <?= e($annonce['DescriptionAbregee']) ?>
          </a>
        </h3>

        <div class="author">
          <?php if ((int)$annonce['NoUtilisateur'] === (int)$_SESSION['NoUtilisateur']): ?>
            Vous (cette annonce)
          <?php else: ?>
            Par <a href="contact.php?id=<?= e($annonce['NoAnnonce']) ?>"><?= e($annonce['Nom'] . ', ' . $annonce['Prenom']) ?></a>
          <?php endif; ?>
        </div>
      </div>

      <div class="price <?= ((float)$annonce['Prix'] <= 0) ? 'na' : '' ?>">
        <?= e(formatPrix($annonce['Prix'])) ?>
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