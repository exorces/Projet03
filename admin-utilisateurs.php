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
if (empty($_SESSION['Nom']) || empty($_SESSION['Prenom'])) {
    header('Location: profil.php');
    exit;
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Confirm a pending user (Statut 0 → 9)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $no = (int)($_POST['no'] ?? 0);
    if ($no > 0) {
        $pdo->prepare("UPDATE utilisateurs SET Statut = 9 WHERE NoUtilisateur = :no AND Statut = 0")
            ->execute(['no' => $no]);
    }
    header('Location: admin-utilisateurs.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$pageTitle = 'Tous les utilisateurs';
$navType   = 'admin';
$current   = 'utilisateurs';

$filtreStatut = $_GET['statut'] ?? 'tous';

$statutLibelles = [
    0 => 'En attente',
    1 => 'Administrateur',
    2 => 'Cadre',
    3 => 'Employé de soutien',
    4 => 'Enseignant',
    5 => 'Professionnel',
    9 => 'Confirmé',
];

$where = '';
$params = [];

if ($filtreStatut === 'attente') {
    $where = 'WHERE u.Statut = 0';
} elseif ($filtreStatut === 'confirme') {
    $where = 'WHERE u.Statut >= 2';
} elseif ($filtreStatut === 'admin') {
    $where = 'WHERE u.Statut = 1';
}

// Total counts for toolbar
$totaux = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(Statut = 0) AS attente,
        SUM(Statut >= 2 OR Statut = 9) AS confirmes
    FROM utilisateurs
")->fetch();

// Main user list with annonce counts
$utilisateurs = $pdo->query("
    SELECT
        u.NoUtilisateur,
        u.Nom,
        u.Prenom,
        u.Courriel,
        u.Statut,
        u.NoEmpl,
        u.Creation,
        u.NbConnexions,
        SUM(a.Etat = 1) AS annoncesActives,
        SUM(a.Etat = 2) AS annoncesInactives,
        SUM(a.Etat = 3) AS annoncesRetirees
    FROM utilisateurs u
    LEFT JOIN annonces a ON a.NoUtilisateur = u.NoUtilisateur
    $where
    GROUP BY u.NoUtilisateur
    ORDER BY u.Nom ASC, u.Prenom ASC
")->fetchAll();

// Last 5 connexions per user — fetched per user below
include '_partials/header.php';
?>

<h2 class="page-title">Tous les utilisateurs</h2>
<p class="page-sub">Liste alphabétique. Cliquez « Confirmer » pour activer un compte en attente.</p>

<div class="toolbar">
  <div class="group">
    <span class="count">
      <strong><?= e($totaux['total']) ?></strong> utilisateur(s) ·
      <strong><?= e($totaux['confirmes']) ?></strong> confirmé(s) ·
      <strong><?= e($totaux['attente']) ?></strong> en attente
    </span>
  </div>
  <div class="group">
    <form method="get" action="admin-utilisateurs.php" style="display:flex;gap:8px;align-items:center;">
      <label>Filtrer par statut</label>
      <select name="statut" onchange="this.form.submit()">
        <option value="tous"    <?= $filtreStatut === 'tous'    ? 'selected' : '' ?>>Tous</option>
        <option value="confirme"<?= $filtreStatut === 'confirme'? 'selected' : '' ?>>Confirmé</option>
        <option value="attente" <?= $filtreStatut === 'attente' ? 'selected' : '' ?>>En attente</option>
        <option value="admin"   <?= $filtreStatut === 'admin'   ? 'selected' : '' ?>>Administrateur</option>
      </select>
    </form>
  </div>
</div>

<?php if (!$utilisateurs): ?>
  <div class="alert warn" style="margin-top:15px;">
    <h4>Aucun utilisateur</h4>
    <p>Aucun utilisateur ne correspond au filtre sélectionné.</p>
  </div>
<?php else: ?>

<table class="data">
  <thead>
    <tr>
      <th>#</th>
      <th>Nom, Prénom</th>
      <th>Courriel</th>
      <th>Statut</th>
      <th>N° empl.</th>
      <th>Inscription</th>
      <th>Connexions</th>
      <th>Annonces<br><span style="font-weight:400;text-transform:none;letter-spacing:0;">(act/inac/ret)</span></th>
      <th>5 dernières connexions</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($utilisateurs as $i => $u):
        $connexions = $pdo->prepare("
            SELECT Connexion
            FROM connexions
            WHERE NoUtilisateur = :no
            ORDER BY Connexion DESC
            LIMIT 5
        ");
        $connexions->execute(['no' => $u['NoUtilisateur']]);
        $dernieres = $connexions->fetchAll();

        $nomAffiche = trim(($u['Nom'] ?? '') . ', ' . ($u['Prenom'] ?? ''));
        if ($nomAffiche === ', ') $nomAffiche = '(inconnu)';

        $statutTexte = $statutLibelles[(int)$u['Statut']] ?? 'Inconnu';
    ?>
    <tr>
      <td><?= e(str_pad($i + 1, 3, '0', STR_PAD_LEFT)) ?></td>
      <td><strong><?= e($nomAffiche) ?></strong></td>
      <td><?= e($u['Courriel']) ?></td>
      <td><?= e($statutTexte) ?></td>
      <td><?= e($u['NoEmpl'] ?? '—') ?></td>
      <td class="small"><?= $u['Creation'] ? e(date('Y-m-d', strtotime($u['Creation']))) : '—' ?></td>
      <td><?= e($u['NbConnexions']) ?></td>
      <td>
        <?= e((int)$u['annoncesActives']) ?> /
        <?= e((int)$u['annoncesInactives']) ?> /
        <?= e((int)$u['annoncesRetirees']) ?>
      </td>
      <td class="small">
        <?php if ($dernieres): ?>
          <?php foreach ($dernieres as $cx): ?>
            <?= e(date('Y-m-d H\hi', strtotime($cx['Connexion']))) ?><br>
          <?php endforeach; ?>
        <?php else: ?>
          Aucune
        <?php endif; ?>
      </td>
      <td>
        <?php if ((int)$u['Statut'] === 0): ?>
          <form method="post" action="admin-utilisateurs.php<?= $filtreStatut !== 'tous' ? '?statut=' . e($filtreStatut) : '' ?>" style="display:inline;">
            <input type="hidden" name="no" value="<?= e($u['NoUtilisateur']) ?>">
            <button type="submit" name="confirmer" class="btn btn-sm btn-primary">Confirmer</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php endif; ?>

<?php include '_partials/footer.php'; ?>
