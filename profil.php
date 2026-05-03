<?php
require_once '_includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Mon profil';
$navType   = 'user';
$current   = 'profil';
$erreur    = '';
$message   = '';

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

function checked($condition) {
    return $condition ? 'checked' : '';
}

function selected($condition) {
    return $condition ? 'selected' : '';
}

function lireTelephone($valeur) {
    $resultat = [
        'numero' => '',
        'public' => false
    ];

    if ($valeur === null || $valeur === '') {
        return $resultat;
    }

    $dernier = substr($valeur, -1);

    if ($dernier === 'P' || $dernier === 'N') {
        $resultat['numero'] = substr($valeur, 0, -1);
        $resultat['public'] = ($dernier === 'P');
    } else {
        $resultat['numero'] = $valeur;
        $resultat['public'] = false;
    }

    return $resultat;
}

function composerTelephone($numero, $estPublic) {
    $numero = trim($numero);

    if ($numero === '') {
        return null;
    }

    return $numero . ($estPublic ? 'P' : 'N');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM utilisateurs
    WHERE NoUtilisateur = :no
");
$stmt->execute(['no' => $_SESSION['NoUtilisateur']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $statut = (int)($_POST['statut'] ?? 9);
    $noEmpl = trim($_POST['noempl'] ?? '');
    $telMaison = trim($_POST['telMaison'] ?? '');
    $telTravail = trim($_POST['telTravail'] ?? '');
    $telCellulaire = trim($_POST['telCellulaire'] ?? '');
    $autresInfos = trim($_POST['autresInfos'] ?? '');

    $regexNom = "/^[A-Za-zÀ-ÖØ-öø-ÿ][A-Za-zÀ-ÖØ-öø-ÿ '\\-]*$/u";
    $regexTelSimple = "/^\\(\\d{3}\\) \\d{3}-\\d{4}$/";
    $regexTelTravail = "/^\\(\\d{3}\\) \\d{3}-\\d{4}( #\\d{1,4})?$/";

    if (!preg_match($regexNom, $nom)) {
        $erreur = 'Le nom est invalide.';
    } elseif (!preg_match($regexNom, $prenom)) {
        $erreur = 'Le prénom est invalide.';
    } elseif (!in_array($statut, [2, 3, 4, 5, 9], true)) {
        $erreur = 'Le statut sélectionné est invalide.';
    } elseif ($noEmpl !== '' && (!ctype_digit($noEmpl) || (int)$noEmpl < 1 || (int)$noEmpl > 9999)) {
        $erreur = 'Le numéro d\'employé doit être entre 1 et 9999.';
    } elseif ($telMaison !== '' && !preg_match($regexTelSimple, $telMaison)) {
        $erreur = 'Le téléphone à la maison doit respecter le format (999) 999-9999.';
    } elseif ($telTravail !== '' && !preg_match($regexTelTravail, $telTravail)) {
        $erreur = 'Le téléphone au travail doit respecter le format (999) 999-9999 ou (999) 999-9999 #9999.';
    } elseif ($telCellulaire !== '' && !preg_match($regexTelSimple, $telCellulaire)) {
        $erreur = 'Le téléphone cellulaire doit respecter le format (999) 999-9999.';
    } elseif (mb_strlen($autresInfos) > 50) {
        $erreur = 'Le champ Autres infos ne peut pas dépasser 50 caractères.';
    } else {
        $nouveauStatut = ((int)$user['Statut'] === 1) ? 1 : $statut;

        $stmt = $pdo->prepare("
            UPDATE utilisateurs
            SET
                Statut = :statut,
                NoEmpl = :noempl,
                Nom = :nom,
                Prenom = :prenom,
                NoTelMaison = :telMaison,
                NoTelTravail = :telTravail,
                NoTelCellulaire = :telCellulaire,
                Modification = NOW(),
                AutresInfos = :autresInfos
            WHERE NoUtilisateur = :no
        ");

        $stmt->execute([
            'statut' => $nouveauStatut,
            'noempl' => ($noEmpl === '' ? null : (int)$noEmpl),
            'nom' => $nom,
            'prenom' => $prenom,
            'telMaison' => composerTelephone($telMaison, isset($_POST['telMaisonPublic'])),
            'telTravail' => composerTelephone($telTravail, isset($_POST['telTravailPublic'])),
            'telCellulaire' => composerTelephone($telCellulaire, isset($_POST['telCellulairePublic'])),
            'autresInfos' => ($autresInfos === '' ? null : $autresInfos),
            'no' => $_SESSION['NoUtilisateur']
        ]);

        $_SESSION['Nom'] = $nom;
        $_SESSION['Prenom'] = $prenom;
        $_SESSION['Statut'] = $nouveauStatut;

        $message = 'Votre profil a été mis à jour.';

        $stmt = $pdo->prepare("
            SELECT *
            FROM utilisateurs
            WHERE NoUtilisateur = :no
        ");
        $stmt->execute(['no' => $_SESSION['NoUtilisateur']]);
        $user = $stmt->fetch();
    }
}

$telMaisonData = lireTelephone($user['NoTelMaison'] ?? '');
$telTravailData = lireTelephone($user['NoTelTravail'] ?? '');
$telCellulaireData = lireTelephone($user['NoTelCellulaire'] ?? '');

include '_partials/header.php';
?>

<h2 class="page-title">Mon profil</h2>
<p class="page-sub">Mettez à jour vos informations personnelles. Cochez « Public » pour rendre un numéro visible dans vos annonces.</p>

<?php if ($erreur): ?>
  <div class="alert danger">
    <h4>Erreur</h4>
    <p><?= e($erreur) ?></p>
  </div>
<?php endif; ?>

<?php if ($message): ?>
  <div class="alert ok">
    <h4>Profil enregistré</h4>
    <p><?= e($message) ?></p>
  </div>
<?php endif; ?>

<form method="post" action="profil.php">
  <div class="auth-wrap" style="margin-top:0; max-width:720px;">

    <div class="section-label">Identification</div>

    <div class="field">
      <label>Adresse de courriel</label>
      <input type="email" value="<?= e($user['Courriel']) ?>" disabled style="background:var(--paper-dim);">
      <div class="hint">L'adresse de courriel ne peut pas être modifiée.</div>
    </div>

    <div class="field">
      <label>Mot de passe</label>
      <a href="motdepasse.php" class="btn btn-sm btn-ghost">Modifier le mot de passe</a>
    </div>

    <div class="section-label">Identité</div>

    <div class="field-row">
      <div class="field">
        <label for="nom">Nom de famille</label>
        <input type="text" id="nom" name="nom" value="<?= e($user['Nom'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= e($user['Prenom'] ?? '') ?>" required>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="statut">Statut d'employé</label>
        <select id="statut" name="statut" <?= ((int)$user['Statut'] === 1) ? 'disabled' : '' ?>>
          <option value="9" <?= selected((int)$user['Statut'] === 9 || (int)$user['Statut'] === 1) ?>>Aucun</option>
          <option value="2" <?= selected((int)$user['Statut'] === 2) ?>>Cadre</option>
          <option value="3" <?= selected((int)$user['Statut'] === 3) ?>>Employé de soutien</option>
          <option value="4" <?= selected((int)$user['Statut'] === 4) ?>>Enseignant</option>
          <option value="5" <?= selected((int)$user['Statut'] === 5) ?>>Professionnel</option>
        </select>
        <?php if ((int)$user['Statut'] === 1): ?>
          <input type="hidden" name="statut" value="9">
          <div class="hint">Le compte administrateur conserve son statut automatiquement.</div>
        <?php endif; ?>
      </div>
      <div class="field">
        <label for="noempl">Numéro d'employé</label>
        <input type="text" id="noempl" name="noempl" value="<?= e($user['NoEmpl'] ?? '') ?>" placeholder="1 à 9999">
      </div>
    </div>

    <div class="section-label">Coordonnées</div>

    <div class="field">
      <label for="telMaison">Téléphone à la maison</label>
      <div class="phone-row">
        <input type="text" id="telMaison" name="telMaison" value="<?= e($telMaisonData['numero']) ?>" placeholder="(999) 999-9999">
        <label class="field-inline">
          <input type="checkbox" name="telMaisonPublic" <?= checked($telMaisonData['public']) ?>>
          <span>Public</span>
        </label>
      </div>
    </div>

    <div class="field">
      <label for="telTravail">Téléphone au travail</label>
      <div class="phone-row">
        <input type="text" id="telTravail" name="telTravail" value="<?= e($telTravailData['numero']) ?>" placeholder="(999) 999-9999 #9999">
        <label class="field-inline">
          <input type="checkbox" name="telTravailPublic" <?= checked($telTravailData['public']) ?>>
          <span>Public</span>
        </label>
      </div>
    </div>

    <div class="field">
      <label for="telCellulaire">Téléphone cellulaire</label>
      <div class="phone-row">
        <input type="text" id="telCellulaire" name="telCellulaire" value="<?= e($telCellulaireData['numero']) ?>" placeholder="(999) 999-9999">
        <label class="field-inline">
          <input type="checkbox" name="telCellulairePublic" <?= checked($telCellulaireData['public']) ?>>
          <span>Public</span>
        </label>
      </div>
    </div>

    <div class="field">
      <label for="autresInfos">Autres infos</label>
      <input type="text" id="autresInfos" name="autresInfos" maxlength="50" value="<?= e($user['AutresInfos'] ?? '') ?>">
      <div class="hint">Maximum 50 caractères.</div>
    </div>

    <div class="actions-bar">
      <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      <a href="annonces.php" class="btn btn-ghost">Annuler</a>
    </div>
  </div>
</form>

<?php include '_partials/footer.php'; ?>