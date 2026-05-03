<?php
require_once '_includes/db.php';

$pageTitle = 'Enregistrement';
$navType   = 'public';
$current   = 'enregistrement';
$erreur    = '';
$message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courriel1 = trim($_POST['courriel1'] ?? '');
    $courriel2 = trim($_POST['courriel2'] ?? '');
    $mdp1 = $_POST['mdp1'] ?? '';
    $mdp2 = $_POST['mdp2'] ?? '';

    if ($courriel1 !== $courriel2) {
        $erreur = 'Les deux adresses de courriel ne correspondent pas.';
    } elseif ($mdp1 !== $mdp2) {
        $erreur = 'Les deux mots de passe ne correspondent pas.';
    } elseif (!filter_var($courriel1, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse de courriel invalide.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,15}$/', $mdp1)) {
        $erreur = 'Le mot de passe doit contenir 5 à 15 caractères avec lettres et chiffres.';
    } else {
        $stmt = $pdo->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Courriel = :courriel");
        $stmt->execute(['courriel' => $courriel1]);

        if ($stmt->fetch()) {
            $erreur = 'Cette adresse de courriel existe déjà.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (Courriel, MotDePasse, Creation, NbConnexions, Statut)
                VALUES (:courriel, :mdp, NOW(), 0, 0)
            ");
            $stmt->execute([
                'courriel' => $courriel1,
                'mdp' => $mdp1
            ]);

            $message = 'Compte créé. Vous devez maintenant confirmer votre inscription.';
        }
    }
}

include '_partials/header.php';
?>

<form method="post" action="enregistrement.php" onsubmit="return validerEnregistrement();">

<div class="auth-wrap">
  <h2>Créer un compte</h2>
  <p class="lead">Un courriel de confirmation vous sera envoyé.</p>

  <div class="field">
    <label>Adresse de courriel</label>
<input type="email" name="courriel1" id="courriel1">
    <div class="hint">Servira d'identifiant unique.</div>
  </div>
  <div class="field">
    <label>Confirmer l'adresse de courriel</label>
<input type="email" name="courriel2" id="courriel2">
  </div>
  <div class="field">
    <label>Mot de passe</label>
<input type="password" name="mdp1" id="mdp1">
    <div class="hint">5 à 15 caractères, lettres et chiffres combinés. Sensible à la casse.</div>
  </div>
  <div class="field">
    <label>Confirmer le mot de passe</label>
<input type="password" name="mdp2" id="mdp2">
  </div>

  <button class="btn btn-primary btn-block">Soumettre</button>

  <div class="auth-links">
    <a href="index.php">← Retour à la connexion</a>
  </div>
</div>

<?php include '_partials/footer.php'; ?>
