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

function importerCsv($pdo, $fichier, $sql, $colonnes) {
    $r = ['inserees' => 0, 'ignorees' => 0, 'erreurs' => 0, 'message' => ''];
    if (!file_exists($fichier)) {
        $r['message'] = 'Fichier introuvable : ' . basename($fichier);
        return $r;
    }
    $fp = fopen($fichier, 'r');
    if (!$fp) {
        $r['message'] = 'Impossible d\'ouvrir : ' . basename($fichier);
        return $r;
    }
    fgetcsv($fp); // skip header row
    $stmt = $pdo->prepare($sql);
    while (($ligne = fgetcsv($fp)) !== false) {
        if (count($ligne) < count($colonnes)) { $r['erreurs']++; continue; }
        $params = [];
        foreach ($colonnes as $i => $col) {
            $params[$col] = ($ligne[$i] === '' || strtoupper($ligne[$i]) === 'NULL') ? null : $ligne[$i];
        }
        try {
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
                $r['inserees']++;
            } else {
                $r['ignorees']++;
            }
        } catch (PDOException $ex) {
            $r['erreurs']++;
        }
    }
    fclose($fp);
    return $r;
}

$resultats = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importer'])) {
    $dir = __DIR__ . '/data/';
    $resultats = [];

    $resultats['utilisateurs'] = importerCsv(
        $pdo,
        $dir . 'utilisateurs.csv',
        "INSERT IGNORE INTO utilisateurs
            (NoUtilisateur, Courriel, MotDePasse, Creation, NbConnexions, Statut,
             NoEmpl, Nom, Prenom, NoTelMaison, NoTelTravail, NoTelCellulaire, Modification, AutresInfos)
         VALUES
            (:NoUtilisateur, :Courriel, :MotDePasse, :Creation, :NbConnexions, :Statut,
             :NoEmpl, :Nom, :Prenom, :NoTelMaison, :NoTelTravail, :NoTelCellulaire, :Modification, :AutresInfos)",
        ['NoUtilisateur','Courriel','MotDePasse','Creation','NbConnexions','Statut',
         'NoEmpl','Nom','Prenom','NoTelMaison','NoTelTravail','NoTelCellulaire','Modification','AutresInfos']
    );

    $resultats['annonces'] = importerCsv(
        $pdo,
        $dir . 'annonces.csv',
        "INSERT IGNORE INTO annonces
            (NoAnnonce, NoUtilisateur, Parution, Categorie, DescriptionAbregee,
             DescriptionComplete, Prix, Photo, MiseAJour, Etat)
         VALUES
            (:NoAnnonce, :NoUtilisateur, :Parution, :Categorie, :DescriptionAbregee,
             :DescriptionComplete, :Prix, :Photo, :MiseAJour, :Etat)",
        ['NoAnnonce','NoUtilisateur','Parution','Categorie','DescriptionAbregee',
         'DescriptionComplete','Prix','Photo','MiseAJour','Etat']
    );

    $resultats['connexions'] = importerCsv(
        $pdo,
        $dir . 'connexions.csv',
        "INSERT IGNORE INTO connexions
            (NoConnexion, NoUtilisateur, Connexion, Deconnexion)
         VALUES
            (:NoConnexion, :NoUtilisateur, :Connexion, :Deconnexion)",
        ['NoConnexion','NoUtilisateur','Connexion','Deconnexion']
    );
}

$pageTitle = 'Importation CSV';
$navType   = 'admin';
$current   = '';
include '_partials/header.php';
?>

<h2 class="page-title">Importation CSV</h2>
<p class="page-sub">
  Importe les données depuis <code>data/utilisateurs.csv</code>,
  <code>data/annonces.csv</code> et <code>data/connexions.csv</code>.
  Les enregistrements déjà présents sont ignorés (<code>INSERT IGNORE</code>).
</p>

<?php if ($resultats !== null): ?>
  <div class="import-results">
    <?php
    $tableLabels = [
        'utilisateurs' => 'Utilisateurs',
        'annonces'     => 'Annonces',
        'connexions'   => 'Connexions',
    ];
    foreach ($resultats as $table => $r):
        $label = $tableLabels[$table] ?? $table;
    ?>
    <div class="alert <?= $r['erreurs'] > 0 ? 'warn' : 'ok' ?>" style="margin-bottom:12px;">
      <h4><?= e($label) ?></h4>
      <?php if ($r['message']): ?>
        <p><?= e($r['message']) ?></p>
      <?php else: ?>
        <p>
          Insérée(s) : <strong><?= e($r['inserees']) ?></strong> &nbsp;·&nbsp;
          Ignorée(s) : <strong><?= e($r['ignorees']) ?></strong> &nbsp;·&nbsp;
          Erreur(s) : <strong><?= e($r['erreurs']) ?></strong>
        </p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" action="importer.php">
  <div class="actions-bar" style="margin-top:20px;">
    <button type="submit" name="importer" class="btn btn-primary"
            onclick="return confirm('Lancer l\'importation des trois fichiers CSV ?');">
      Importer les données CSV
    </button>
    <a href="admin.php" class="btn btn-ghost">Retour</a>
  </div>
</form>

<?php include '_partials/footer.php'; ?>
