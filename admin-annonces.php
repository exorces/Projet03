<?php
require_once '_includes/db.php';

if (!isset($_SESSION['Courriel'])) {
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['Statut']) || (int)$_SESSION['Statut'] !== 1) {
    header('Location: annonces.php');
    exit;
}
if (empty($_SESSION['Nom']) || empty($_SESSION['Prenom'])) {
    header('Location: profil.php');
    exit;
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function selected($c) { return $c ? 'selected' : ''; }

function formatDateAnnonce($date) {
    return $date ? date('Y-m-d, H\hi', strtotime($date)) : '';
}

function formatPrix($prix) {
    return ((float)$prix <= 0) ? 'N/A' : number_format((float)$prix, 2, ',', ' ') . ' $';
}

function imageAnnonce($photo) {
    if (!$photo) return '<div class="thumb" style="width:144px;height:100px;"></div>';
    $nomFichier = basename($photo);
    $vignette = 'photos-annonce/vignette-' . $nomFichier;
    $source = file_exists($vignette) ? $vignette : $photo;
    if (!file_exists($source)) return '<div class="thumb" style="width:144px;height:100px;"></div>';
    return '<div class="thumb" style="width:144px;height:100px;"><img src="' . e($source) . '" alt="Photo" style="width:144px;height:100px;object-fit:cover;"></div>';
}

function lienPagination($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'admin-annonces.php?' . http_build_query($params);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if ($_POST['action'] === 'retirer' && (int)$_POST['id'] > 0) {
        $pdo->prepare("
            UPDATE annonces SET Etat = 3, MiseAJour = NOW()
            WHERE NoAnnonce = :id AND Etat <> 3
        ")->execute(['id' => (int)$_POST['id']]);
    }
    $back = [];
    foreach (['du','au','categorie','etat','q','tri','ordre','parpage','page'] as $k) {
        if (isset($_POST[$k]) && $_POST[$k] !== '') $back[$k] = $_POST[$k];
    }
    header('Location: admin-annonces.php' . ($back ? '?' . http_build_query($back) : ''));
    exit;
}

$pageTitle = 'Toutes les annonces';
$navType   = 'admin';
$current   = 'annonces';

$categories = $pdo->query("SELECT NoCategorie, Categorie FROM categories ORDER BY NoCategorie")->fetchAll();

$du         = trim($_GET['du'] ?? '');
$au         = trim($_GET['au'] ?? '');
$categorie  = (int)($_GET['categorie'] ?? 0);
$etatFiltre = (int)($_GET['etat'] ?? -1);
$q          = trim($_GET['q'] ?? '');
$tri        = $_GET['tri'] ?? 'date';
$ordre      = $_GET['ordre'] ?? 'desc';
$parPage    = (int)($_GET['parpage'] ?? 10);
$page       = (int)($_GET['page'] ?? 1);

if (!in_array($parPage, [5, 10, 15, 20], true)) $parPage = 10;
if ($page < 1) $page = 1;

$ordreSql = ($ordre === 'asc') ? 'ASC' : 'DESC';
switch ($tri) {
    case 'auteur':    $orderBy = "u.Nom $ordreSql, u.Prenom $ordreSql, a.Parution DESC"; break;
    case 'categorie': $orderBy = "c.Categorie $ordreSql, a.Parution DESC"; break;
    case 'etat':      $orderBy = "a.Etat $ordreSql, a.Parution DESC"; break;
    default:          $tri = 'date'; $orderBy = "a.Parution $ordreSql, a.NoAnnonce DESC";
}

$where  = ['1=1'];
$params = [];

if ($du !== '')       { $where[] = 'a.Parution >= :du';        $params['du']        = $du . ' 00:00:00'; }
if ($au !== '')       { $where[] = 'a.Parution <= :au';        $params['au']        = $au . ' 23:59:59'; }
if ($categorie > 0)   { $where[] = 'a.Categorie = :categorie'; $params['categorie'] = $categorie; }
if ($etatFiltre >= 0) { $where[] = 'a.Etat = :etat';          $params['etat']      = $etatFiltre; }
if ($q !== '') {
    $where[] = '(a.DescriptionAbregee LIKE :q1 OR a.DescriptionComplete LIKE :q2 OR u.Nom LIKE :q3 OR u.Prenom LIKE :q4)';
    $params['q1'] = '%' . $q . '%';
    $params['q2'] = '%' . $q . '%';
    $params['q3'] = '%' . $q . '%';
    $params['q4'] = '%' . $q . '%';
}

$whereSql = implode(' AND ', $where);

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) FROM annonces a
    JOIN utilisateurs u ON a.NoUtilisateur = u.NoUtilisateur
    JOIN categories c ON a.Categorie = c.NoCategorie
    WHERE $whereSql
");
foreach ($params as $k => $v) {
    $stmtCount->bindValue(':' . $k, $v, $k === 'categorie' || $k === 'etat' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtCount->execute();
$total = (int)$stmtCount->fetchColumn();

$totalPages = max(1, (int)ceil($total / $parPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $parPage;

$stmt = $pdo->prepare("
    SELECT a.NoAnnonce, a.NoUtilisateur, a.Parution, a.DescriptionAbregee, a.Prix, a.Photo, a.Etat,
           u.Nom, u.Prenom, c.Categorie AS NomCategorie
    FROM annonces a
    JOIN utilisateurs u ON a.NoUtilisateur = u.NoUtilisateur
    JOIN categories c ON a.Categorie = c.NoCategorie
    WHERE $whereSql
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v, $k === 'categorie' || $k === 'etat' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$annonces = $stmt->fetchAll();

$etatLibelles = [1 => 'Actif', 2 => 'Inactif', 3 => 'Retiré'];
$etatClasses  = [1 => 'actif', 2 => 'inactif', 3 => 'retire'];

include '_partials/header.php';
?>

<h2 class="page-title">Toutes les annonces</h2>
<p class="page-sub">Vue administrateur — toutes les annonces, tous les états.</p>

<form method="get" action="admin-annonces.php" class="search-box">
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
    <label for="etat">État</label>
    <select id="etat" name="etat">
      <option value="-1" <?= selected($etatFiltre === -1) ?>>Tous</option>
      <option value="1"  <?= selected($etatFiltre === 1)  ?>>Actif</option>
      <option value="2"  <?= selected($etatFiltre === 2)  ?>>Inactif</option>
      <option value="3"  <?= selected($etatFiltre === 3)  ?>>Retiré</option>
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
  <a href="admin-annonces.php" class="btn btn-ghost">Réinitialiser</a>
</form>

<form method="get" action="admin-annonces.php" class="toolbar">
  <input type="hidden" name="du" value="<?= e($du) ?>">
  <input type="hidden" name="au" value="<?= e($au) ?>">
  <input type="hidden" name="categorie" value="<?= e($categorie) ?>">
  <input type="hidden" name="etat" value="<?= e($etatFiltre) ?>">
  <input type="hidden" name="q" value="<?= e($q) ?>">

  <div class="group">
    <label for="tri">Trier par</label>
    <select id="tri" name="tri">
      <option value="date"      <?= selected($tri === 'date') ?>>Date de parution</option>
      <option value="auteur"    <?= selected($tri === 'auteur') ?>>Auteur</option>
      <option value="categorie" <?= selected($tri === 'categorie') ?>>Catégorie</option>
      <option value="etat"      <?= selected($tri === 'etat') ?>>État</option>
    </select>
    <select name="ordre">
      <option value="desc" <?= selected($ordre === 'desc') ?>>Décroissant</option>
      <option value="asc"  <?= selected($ordre === 'asc')  ?>>Croissant</option>
    </select>
  </div>

  <div class="group">
    <span class="count"><strong><?= e($total) ?></strong> annonce(s)</span>
  </div>

  <div class="group">
    <label for="parpage">Par page</label>
    <select id="parpage" name="parpage">
      <option value="5"  <?= selected($parPage === 5)  ?>>5</option>
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
    <?php $etat = (int)$annonce['Etat']; ?>
    <article class="annonce-row">
      <div class="num"><?= e($offset + $index + 1) ?></div>

      <?= imageAnnonce($annonce['Photo']) ?>

      <div class="body">
        <div class="meta-line">
          <span><?= e(formatDateAnnonce($annonce['Parution'])) ?></span>
          <span class="id">Annonce #<?= e($annonce['NoAnnonce']) ?></span>
          <span class="cat"><?= e($annonce['NomCategorie']) ?></span>
          <span class="badge <?= e($etatClasses[$etat] ?? '') ?>"><?= e($etatLibelles[$etat] ?? '') ?></span>
        </div>

        <h3>
          <a href="annonce-detail.php?id=<?= e($annonce['NoAnnonce']) ?>">
            <?= e($annonce['DescriptionAbregee']) ?>
          </a>
        </h3>

        <div class="author">
          Par <a href="contact.php?id=<?= e($annonce['NoAnnonce']) ?>">
            <?= e($annonce['Nom'] . ', ' . $annonce['Prenom']) ?>
          </a>
        </div>
      </div>

      <div class="price <?= ((float)$annonce['Prix'] <= 0) ? 'na' : '' ?>">
        <?= e(formatPrix($annonce['Prix'])) ?>
      </div>

      <div class="actions">
        <?php if ($etat !== 3): ?>
          <form method="post" action="admin-annonces.php" style="display:inline;"
                onsubmit="return confirm('Retirer l\'annonce #<?= e($annonce['NoAnnonce']) ?> de <?= e($annonce['Nom'] . ', ' . $annonce['Prenom']) ?> ?');">
            <input type="hidden" name="action"   value="retirer">
            <input type="hidden" name="id"       value="<?= e($annonce['NoAnnonce']) ?>">
            <input type="hidden" name="du"       value="<?= e($du) ?>">
            <input type="hidden" name="au"       value="<?= e($au) ?>">
            <input type="hidden" name="categorie" value="<?= e($categorie) ?>">
            <input type="hidden" name="etat"     value="<?= e($etatFiltre) ?>">
            <input type="hidden" name="q"        value="<?= e($q) ?>">
            <input type="hidden" name="tri"      value="<?= e($tri) ?>">
            <input type="hidden" name="ordre"    value="<?= e($ordre) ?>">
            <input type="hidden" name="parpage"  value="<?= e($parPage) ?>">
            <input type="hidden" name="page"     value="<?= e($page) ?>">
            <button type="submit" class="btn btn-sm btn-danger">Retirer</button>
          </form>
        <?php else: ?>
          <span class="btn btn-sm btn-ghost" style="opacity:.4;cursor:default;">Retiré</span>
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
