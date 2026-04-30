<?php
$pageTitle = 'Connexion';
$navType   = 'public';
$current   = 'connexion';
$erreur    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '_includes/db.php';

    $courriel = trim($_POST['courriel'] ?? '');
    $mdp      = $_POST['mdp'] ?? '';

    // Vérifier les identifiants (sensible à la casse via BINARY)
    $stmt = $pdo->prepare("
        SELECT NoUtilisateur, Nom, Prenom, Statut
        FROM utilisateurs
        WHERE Courriel = :courriel AND BINARY MotDePasse = :mdp
    ");
    $stmt->execute(['courriel' => $courriel, 'mdp' => $mdp]);
    $user = $stmt->fetch();

    if (!$user) {
        $erreur = 'Courriel ou mot de passe invalide.';
    } elseif ($user['Statut'] == 0) {
        // Statut 0 = en attente, refus de connexion
        $erreur = 'Votre compte est en attente d\'approbation.';
    } else {
        // Enregistrer la connexion
        $stmt = $pdo->prepare("
            INSERT INTO connexions (NoUtilisateur, Connexion)
            VALUES (:no, NOW())
        ");
        $stmt->execute(['no' => $user['NoUtilisateur']]);
        $_SESSION['NoConnexion'] = $pdo->lastInsertId();

        // Incrémenter NbConnexions
        $stmt = $pdo->prepare("
            UPDATE utilisateurs
            SET NbConnexions = NbConnexions + 1
            WHERE NoUtilisateur = :no
        ");
        $stmt->execute(['no' => $user['NoUtilisateur']]);

        header('Location: annonces.php');
        exit;
    }
}

include '_partials/header.php';
?>

<div class="auth-wrap">
  <h2>Connexion</h2>
  <p class="lead">Entrez vos identifiants pour accéder à votre compte.</p>

  <?php if ($erreur): ?>
  <p class="error"><?= htmlspecialchars($erreur) ?></p>
  <?php endif; ?>

  <form method="post" action="index.php">
    <div class="field">
      <label for="login-email">Adresse de courriel</label>
      <input type="email" id="login-email" name="courriel" placeholder="prenom.nom@cgodin.qc.ca" required>
    </div>

    <div class="field">
      <label for="login-pwd">Mot de passe</label>
      <input type="password" id="login-pwd" name="mdp" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
  </form>

  <div class="auth-links">
    <a href="enregistrement.php">Créer un compte</a>
    <a href="recuperation.php">Mot de passe oublié ?</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
