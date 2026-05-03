<?php
require_once '_includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Modifier le mot de passe';
$navType   = 'user';
$current   = 'profil';
$erreur    = '';
$message   = '';

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actuel = $_POST['actuel'] ?? '';
    $nouveau = $_POST['nouveau'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($actuel === '' || $nouveau === '' || $confirmation === '') {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif ($nouveau !== $confirmation) {
        $erreur = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,15}$/', $nouveau)) {
        $erreur = 'Le nouveau mot de passe doit contenir 5 à 15 caractères avec lettres et chiffres.';
    } else {
        $stmt = $pdo->prepare("
            SELECT MotDePasse
            FROM utilisateurs
            WHERE NoUtilisateur = :no
        ");
        $stmt->execute(['no' => $_SESSION['NoUtilisateur']]);
        $user = $stmt->fetch();

        if (!$user || $user['MotDePasse'] !== $actuel) {
            $erreur = 'Le mot de passe actuel est incorrect.';
        } else {
            $stmt = $pdo->prepare("
                UPDATE utilisateurs
                SET MotDePasse = :mdp,
                    Modification = NOW()
                WHERE NoUtilisateur = :no
            ");
            $stmt->execute([
                'mdp' => $nouveau,
                'no' => $_SESSION['NoUtilisateur']
            ]);

            $message = 'Votre mot de passe a été modifié.';
        }
    }
}

include '_partials/header.php';
?>

<h2 class="page-title">Modifier le mot de passe</h2>
<p class="page-sub">5 à 15 caractères, lettres et chiffres combinés. Sensible à la casse.</p>

<?php if ($erreur): ?>
  <div class="alert danger">
    <h4>Erreur</h4>
    <p><?= e($erreur) ?></p>
  </div>
<?php endif; ?>

<?php if ($message): ?>
  <div class="alert ok">
    <h4>Modification réussie</h4>
    <p><?= e($message) ?></p>
  </div>
<?php endif; ?>

<form method="post" action="motdepasse.php" onsubmit="return validerMotDePasse();">
  <div class="auth-wrap" style="margin-top:0;">
    <div class="field">
      <label for="actuel">Mot de passe actuel</label>
      <input type="password" id="actuel" name="actuel" required>
    </div>

    <div class="field">
      <label for="nouveau">Nouveau mot de passe</label>
      <input type="password" id="nouveau" name="nouveau" required>
      <div class="hint">5 à 15 caractères, au moins une lettre et un chiffre.</div>
    </div>

    <div class="field">
      <label for="confirmation">Confirmer le nouveau mot de passe</label>
      <input type="password" id="confirmation" name="confirmation" required>
      <div id="erreur-mdp" class="err"></div>
    </div>

    <div class="actions-bar">
      <button type="submit" class="btn btn-primary">Modifier</button>
      <a href="profil.php" class="btn btn-ghost">Annuler</a>
    </div>
  </div>
</form>

<script>
function validerMotDePasse() {
    const nouveau = document.getElementById('nouveau').value;
    const confirmation = document.getElementById('confirmation').value;
    const zoneErreur = document.getElementById('erreur-mdp');
    const regexMdp = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,15}$/;

    zoneErreur.textContent = '';

    if (!regexMdp.test(nouveau)) {
        zoneErreur.textContent = 'Le mot de passe doit contenir 5 à 15 caractères avec lettres et chiffres.';
        return false;
    }

    if (nouveau !== confirmation) {
        zoneErreur.textContent = 'Les deux mots de passe ne correspondent pas.';
        return false;
    }

    return true;
}
</script>

<?php include '_partials/footer.php'; ?>