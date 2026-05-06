<?php
require_once '_includes/db.php';

$pageTitle = 'Enregistrement';
$navType   = 'public';
$current   = 'enregistrement';
$erreur    = '';
$message   = '';

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courriel1 = trim($_POST['courriel1'] ?? '');
    $courriel2 = trim($_POST['courriel2'] ?? '');
    $mdp1      = $_POST['mdp1'] ?? '';
    $mdp2      = $_POST['mdp2'] ?? '';

    if (!filter_var($courriel1, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse de courriel invalide.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,15}$/', $mdp1)) {
        $erreur = 'Le mot de passe doit contenir 5 à 15 caractères avec lettres et chiffres combinés.';
    } elseif ($courriel1 !== $courriel2) {
        $erreur = 'Les deux adresses de courriel ne correspondent pas.';
    } elseif ($mdp1 !== $mdp2) {
        $erreur = 'Les deux mots de passe ne correspondent pas.';
    } else {
        $stmt = $pdo->prepare("SELECT NoUtilisateur FROM utilisateurs WHERE Courriel = :courriel");
        $stmt->execute(['courriel' => $courriel1]);

        if ($stmt->fetch()) {
            $erreur = 'Cette adresse de courriel est déjà utilisée.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (Courriel, MotDePasse, Creation, NbConnexions, Statut)
                VALUES (:courriel, :mdp, NOW(), 0, 0)
            ");
            $stmt->execute([
                'courriel' => $courriel1,
                'mdp'      => $mdp1,
            ]);

            $noUtil = $pdo->lastInsertId();
            $token  = base64_encode($noUtil . '|' . hash('sha256', $courriel1 . $mdp1));
            $lien   = 'http://' . $_SERVER['HTTP_HOST'] . '/Projet03/confirmation.php?token=' . urlencode($token);

            $sujet   = 'Confirmation de votre inscription – Les petites annonces GG';
            $contenu = "Bonjour,\n\nMerci de vous être inscrit.\n\n"
                     . "Cliquez sur le lien suivant pour confirmer votre inscription :\n$lien\n\n"
                     . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n";
            $entetes = "From: noreply@cgodin.qc.ca\r\nContent-Type: text/plain; charset=UTF-8\r\n";

            @mail($courriel1, $sujet, $contenu, $entetes);

            $message = 'Compte créé. Un courriel de confirmation vous a été envoyé. '
                     . 'Pour le prototype : <a href="' . e($lien) . '">cliquez ici pour confirmer</a>.';
        }
    }
}

include '_partials/header.php';
?>

<div class="auth-wrap">
  <h2>Créer un compte</h2>
  <p class="lead">Un courriel de confirmation vous sera envoyé.</p>

  <?php if ($erreur): ?>
    <div class="alert danger">
      <h4>Erreur</h4>
      <p><?= e($erreur) ?></p>
    </div>
  <?php endif; ?>

  <?php if ($message): ?>
    <div class="alert ok">
      <h4>Inscription réussie</h4>
      <p><?= $message ?></p>
    </div>
  <?php endif; ?>

  <form method="post" action="enregistrement.php" onsubmit="return validerEnregistrement();">

    <div class="field">
      <label for="courriel1">Adresse de courriel</label>
      <input type="email" name="courriel1" id="courriel1" placeholder="prenom.nom@cgodin.qc.ca">
      <div class="hint">Servira d'identifiant unique.</div>
      <div id="err-courriel1" class="err"></div>
    </div>

    <div class="field">
      <label for="courriel2">Confirmer l'adresse de courriel</label>
      <input type="email" name="courriel2" id="courriel2" placeholder="prenom.nom@cgodin.qc.ca">
      <div id="err-courriel2" class="err"></div>
    </div>

    <div class="field">
      <label for="mdp1">Mot de passe</label>
      <input type="password" name="mdp1" id="mdp1">
      <div class="hint">5 à 15 caractères, lettres et chiffres combinés. Sensible à la casse.</div>
      <div id="err-mdp1" class="err"></div>
    </div>

    <div class="field">
      <label for="mdp2">Confirmer le mot de passe</label>
      <input type="password" name="mdp2" id="mdp2">
      <div id="err-mdp2" class="err"></div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Soumettre</button>

  </form>

  <div class="auth-links">
    <a href="index.php">← Retour à la connexion</a>
  </div>
</div>

<script>
function validerEnregistrement() {
    const courriel1 = document.getElementById('courriel1').value.trim();
    const courriel2 = document.getElementById('courriel2').value.trim();
    const mdp1      = document.getElementById('mdp1').value;
    const mdp2      = document.getElementById('mdp2').value;

    const zones = ['courriel1', 'courriel2', 'mdp1', 'mdp2'];
    zones.forEach(id => { document.getElementById('err-' + id).textContent = ''; });

    const regexCourriel = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const regexMdp      = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,15}$/;

    let valide = true;

    if (!regexCourriel.test(courriel1)) {
        document.getElementById('err-courriel1').textContent = 'Adresse de courriel invalide.';
        valide = false;
    }

    if (!regexMdp.test(mdp1)) {
        document.getElementById('err-mdp1').textContent =
            'Le mot de passe doit contenir 5 à 15 caractères avec lettres et chiffres combinés.';
        valide = false;
    }

    if (courriel2 !== courriel1) {
        document.getElementById('err-courriel2').textContent =
            'Les adresses de courriel ne correspondent pas.';
        valide = false;
    }

    if (mdp2 !== mdp1) {
        document.getElementById('err-mdp2').textContent =
            'Les mots de passe ne correspondent pas.';
        valide = false;
    }

    return valide;
}
</script>

<?php include '_partials/footer.php'; ?>
