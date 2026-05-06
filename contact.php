<?php
require_once '_includes/db.php';

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function telPublic($valeur) {
    if (!$valeur) return null;
    $dernier = substr($valeur, -1);
    return ($dernier === 'P') ? substr($valeur, 0, -1) : null;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: annonces.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.NoAnnonce, a.DescriptionAbregee, a.NoUtilisateur,
           u.Nom, u.Prenom, u.Courriel,
           u.NoTelMaison, u.NoTelTravail, u.NoTelCellulaire
    FROM annonces a
    JOIN utilisateurs u ON a.NoUtilisateur = u.NoUtilisateur
    WHERE a.NoAnnonce = :id AND a.Etat = 1
");
$stmt->execute(['id' => $id]);
$annonce = $stmt->fetch();

if (!$annonce || (int)$annonce['NoUtilisateur'] === (int)$_SESSION['NoUtilisateur']) {
    header('Location: annonces.php');
    exit;
}

$message  = '';
$erreur   = '';
$envoye   = false;

$sujetDefaut = 'Au sujet de votre annonce — ' . $annonce['DescriptionAbregee'];
$sujet    = $_POST['sujet']   ?? $sujetDefaut;
$contenu  = $_POST['contenu'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet   = trim($_POST['sujet']   ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    if ($sujet === '') {
        $erreur = 'L\'objet du message est obligatoire.';
    } elseif ($contenu === '') {
        $erreur = 'Le message ne peut pas être vide.';
    } else {
        $expediteur = $_SESSION['Nom'] . ', ' . $_SESSION['Prenom'] . ' <' . $_SESSION['Courriel'] . '>';
        $corps = "Bonjour " . $annonce['Prenom'] . " " . $annonce['Nom'] . ",\n\n"
               . $contenu . "\n\n"
               . "— " . $expediteur . "\n"
               . "(Message envoyé via Les petites annonces GG, annonce #" . $annonce['NoAnnonce'] . ")";

        $entetes  = "From: " . $_SESSION['Courriel'] . "\r\n";
        $entetes .= "Reply-To: " . $_SESSION['Courriel'] . "\r\n";
        $entetes .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $mailEnvoye = @mail($annonce['Courriel'], $sujet, $corps, $entetes);

        if ($mailEnvoye) {
            $message = 'Votre message a été envoyé à ' . e($annonce['Nom'] . ', ' . $annonce['Prenom']) . '.';
        } else {
            $message = 'Le serveur local ne peut pas envoyer de courriel. Pour le prototype, le message aurait été envoyé à : <strong>' . e($annonce['Courriel']) . '</strong>';
        }
        $envoye = true;
    }
}

$pageTitle = 'Contacter l\'auteur';
$navType   = 'user';
$current   = 'annonces';

$telMaison     = telPublic($annonce['NoTelMaison']);
$telTravail    = telPublic($annonce['NoTelTravail']);
$telCellulaire = telPublic($annonce['NoTelCellulaire']);

include '_partials/header.php';
?>

<h2 class="page-title">Contacter l'auteur</h2>
<p class="page-sub">À propos de l'annonce #<?= e($annonce['NoAnnonce']) ?> — <?= e($annonce['DescriptionAbregee']) ?></p>

<div class="auth-wrap" style="margin-top:0; max-width:720px;">

  <?php if ($erreur): ?>
    <div class="alert danger"><h4>Erreur</h4><p><?= e($erreur) ?></p></div>
  <?php endif; ?>

  <?php if ($envoye): ?>
    <div class="alert ok">
      <h4>Message envoyé</h4>
      <p><?= $message ?></p>
    </div>
    <a href="annonces.php" class="btn btn-primary" style="margin-top:12px;">Retour aux annonces</a>

  <?php else: ?>

    <?php if ($telMaison || $telTravail || $telCellulaire): ?>
      <div class="alert ok" style="margin-bottom:16px;">
        <h4>Coordonnées publiques de l'auteur</h4>
        <?php if ($telMaison):    ?><p>Maison : <?= e($telMaison) ?></p><?php endif; ?>
        <?php if ($telTravail):   ?><p>Travail : <?= e($telTravail) ?></p><?php endif; ?>
        <?php if ($telCellulaire):?><p>Cellulaire : <?= e($telCellulaire) ?></p><?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="field">
      <label>Destinataire</label>
      <input type="text" value="<?= e($annonce['Nom'] . ', ' . $annonce['Prenom']) ?>" disabled style="background:var(--paper-dim);">
    </div>

    <div class="field">
      <label>De la part de</label>
      <input type="text" value="<?= e($_SESSION['Nom'] . ', ' . $_SESSION['Prenom'] . ' <' . $_SESSION['Courriel'] . '>') ?>" disabled style="background:var(--paper-dim);">
    </div>

    <form method="post" action="contact.php?id=<?= e($id) ?>">
      <div class="field">
        <label for="sujet">Objet</label>
        <input type="text" id="sujet" name="sujet" value="<?= e($sujet) ?>" required>
      </div>

      <div class="field">
        <label for="contenu">Message</label>
        <textarea id="contenu" name="contenu" rows="6" placeholder="Bonjour, je suis intéressé par votre annonce…" required><?= e($contenu) ?></textarea>
      </div>

      <div class="actions-bar">
        <button type="submit" class="btn btn-primary">Envoyer</button>
        <a href="annonce-detail.php?id=<?= e($id) ?>" class="btn btn-ghost">Annuler</a>
      </div>
    </form>

  <?php endif; ?>
</div>

<?php include '_partials/footer.php'; ?>
