<?php
require_once '_includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['Courriel'], $_SESSION['NoUtilisateur'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['Nom']) || empty($_SESSION['Prenom'])) {
    header('Location: profil.php');
    exit;
}

$pageTitle = 'Nouvelle annonce';
$navType   = 'user';
$current   = 'mes-annonces';
$erreur    = '';

function e($valeur) {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

function selected($condition) {
    return $condition ? 'selected' : '';
}

function creerVignette($source, $destination, $extension, $largeurVoulue = 144) {
    if (!function_exists('imagecreatetruecolor')) {
        copy($source, $destination);
        return;
    }

    $extension = strtolower($extension);
    $imageSource = null;

    if (($extension === 'jpg' || $extension === 'jpeg') && function_exists('imagecreatefromjpeg')) {
        $imageSource = @imagecreatefromjpeg($source);
    } elseif ($extension === 'png' && function_exists('imagecreatefrompng')) {
        $imageSource = @imagecreatefrompng($source);
    } elseif ($extension === 'gif' && function_exists('imagecreatefromgif')) {
        $imageSource = @imagecreatefromgif($source);
    } elseif ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
        $imageSource = @imagecreatefromwebp($source);
    }

    if (!$imageSource) {
        copy($source, $destination);
        return;
    }

    $largeurOriginale = imagesx($imageSource);
    $hauteurOriginale = imagesy($imageSource);

    if ($largeurOriginale <= 0 || $hauteurOriginale <= 0) {
        imagedestroy($imageSource);
        copy($source, $destination);
        return;
    }

    $hauteurVoulue = (int)round(($largeurVoulue / $largeurOriginale) * $hauteurOriginale);

    $vignette = imagecreatetruecolor($largeurVoulue, $hauteurVoulue);

    if ($extension === 'png' || $extension === 'gif' || $extension === 'webp') {
        imagealphablending($vignette, false);
        imagesavealpha($vignette, true);
    }

    imagecopyresampled(
        $vignette,
        $imageSource,
        0,
        0,
        0,
        0,
        $largeurVoulue,
        $hauteurVoulue,
        $largeurOriginale,
        $hauteurOriginale
    );

    if ($extension === 'jpg' || $extension === 'jpeg') {
        imagejpeg($vignette, $destination, 85);
    } elseif ($extension === 'png') {
        imagepng($vignette, $destination);
    } elseif ($extension === 'gif') {
        imagegif($vignette, $destination);
    } elseif ($extension === 'webp' && function_exists('imagewebp')) {
        imagewebp($vignette, $destination, 85);
    } else {
        imagejpeg($vignette, $destination, 85);
    }

    imagedestroy($imageSource);
    imagedestroy($vignette);
}

function sauvegarderPhoto($fichier, &$erreur) {
    if (!isset($fichier) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $erreur = 'Erreur lors du téléversement de la photo.';
        return null;
    }

    $info = @getimagesize($fichier['tmp_name']);

    if ($info === false) {
        $erreur = 'Le fichier choisi n\'est pas une image valide.';
        return null;
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    $mime = $info['mime'] ?? '';

    if (!isset($extensions[$mime])) {
        $erreur = 'La photo doit être au format JPG, PNG, GIF ou WebP.';
        return null;
    }

    $extension = $extensions[$mime];
    $dossier = __DIR__ . '/photos-annonce';

    if (!is_dir($dossier)) {
        mkdir($dossier, 0775, true);
    }

    $nomFichier = 'ann_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
    $destination = $dossier . '/' . $nomFichier;
    $destinationRelative = 'photos-annonce/' . $nomFichier;

    if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
        $erreur = 'Impossible d\'enregistrer la photo.';
        return null;
    }

    $vignette = $dossier . '/vignette-' . $nomFichier;
    creerVignette($destination, $vignette, $extension, 144);

    return $destinationRelative;
}

$categories = $pdo->query("
    SELECT NoCategorie, Categorie
    FROM categories
    ORDER BY NoCategorie
")->fetchAll();

$categoriesValides = [];

foreach ($categories as $cat) {
    $categoriesValides[] = (int)$cat['NoCategorie'];
}

$id = (int)($_GET['id'] ?? 0);
$edition = $id > 0;
$annonce = null;

if ($edition) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM annonces
        WHERE NoAnnonce = :id
          AND NoUtilisateur = :user
          AND Etat <> 3
    ");
    $stmt->execute([
        'id' => $id,
        'user' => $_SESSION['NoUtilisateur']
    ]);
    $annonce = $stmt->fetch();

    if (!$annonce) {
        header('Location: mes-annonces.php');
        exit;
    }

    $pageTitle = 'Modifier une annonce';
}

$categorie = $annonce['Categorie'] ?? '';
$descriptionAbregee = $annonce['DescriptionAbregee'] ?? '';
$descriptionComplete = $annonce['DescriptionComplete'] ?? '';
$prix = isset($annonce['Prix']) ? (string)$annonce['Prix'] : '';
$etat = $annonce['Etat'] ?? 1;
$photoActuelle = $annonce['Photo'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categorie = (int)($_POST['categorie'] ?? 0);
    $descriptionAbregee = trim($_POST['descriptionAbregee'] ?? '');
    $descriptionComplete = trim($_POST['descriptionComplete'] ?? '');
    $prixTexte = trim($_POST['prix'] ?? '');
    $etat = (int)($_POST['etat'] ?? 1);

    $prixTexte = str_replace(',', '.', $prixTexte);

    if (!in_array($categorie, $categoriesValides, true)) {
        $erreur = 'Veuillez choisir une catégorie valide.';
    } elseif ($descriptionAbregee === '' || mb_strlen($descriptionAbregee) > 50) {
        $erreur = 'La description abrégée est obligatoire et ne peut pas dépasser 50 caractères.';
    } elseif ($descriptionComplete === '' || mb_strlen($descriptionComplete) > 250) {
        $erreur = 'La description complète est obligatoire et ne peut pas dépasser 250 caractères.';
    } elseif (!in_array($etat, [1, 2], true)) {
        $erreur = 'L\'état sélectionné est invalide.';
    } elseif ($prixTexte !== '' && !preg_match('/^\d{1,5}(\.\d{1,2})?$/', $prixTexte)) {
        $erreur = 'Le prix doit être entre 0.00 et 99999.99.';
    } else {
        $prix = ($prixTexte === '') ? 0.00 : (float)$prixTexte;

        $erreurPhoto = '';
        $nouvellePhoto = sauvegarderPhoto($_FILES['photo'] ?? null, $erreurPhoto);

        if ($erreurPhoto !== '') {
            $erreur = $erreurPhoto;
        } else {
            if ($nouvellePhoto !== null) {
                if ($edition && $photoActuelle !== null) {
                    $ancienAbs     = __DIR__ . '/' . $photoActuelle;
                    $ancienVignette = __DIR__ . '/photos-annonce/vignette-' . basename($photoActuelle);
                    if (file_exists($ancienAbs))     @unlink($ancienAbs);
                    if (file_exists($ancienVignette)) @unlink($ancienVignette);
                }
                $photoActuelle = $nouvellePhoto;
            }

            if ($edition) {
                $stmt = $pdo->prepare("
                    UPDATE annonces
                    SET
                        Categorie = :categorie,
                        DescriptionAbregee = :descriptionAbregee,
                        DescriptionComplete = :descriptionComplete,
                        Prix = :prix,
                        Photo = :photo,
                        MiseAJour = NOW(),
                        Etat = :etat
                    WHERE NoAnnonce = :id
                      AND NoUtilisateur = :user
                ");

                $stmt->execute([
                    'categorie' => $categorie,
                    'descriptionAbregee' => $descriptionAbregee,
                    'descriptionComplete' => $descriptionComplete,
                    'prix' => $prix,
                    'photo' => $photoActuelle,
                    'etat' => $etat,
                    'id' => $id,
                    'user' => $_SESSION['NoUtilisateur']
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO annonces
                        (NoUtilisateur, Parution, Categorie, DescriptionAbregee, DescriptionComplete, Prix, Photo, MiseAJour, Etat)
                    VALUES
                        (:user, NOW(), :categorie, :descriptionAbregee, :descriptionComplete, :prix, :photo, NOW(), :etat)
                ");

                $stmt->execute([
                    'user' => $_SESSION['NoUtilisateur'],
                    'categorie' => $categorie,
                    'descriptionAbregee' => $descriptionAbregee,
                    'descriptionComplete' => $descriptionComplete,
                    'prix' => $prix,
                    'photo' => $photoActuelle,
                    'etat' => $etat
                ]);
            }

            header('Location: mes-annonces.php');
            exit;
        }
    }
}

include '_partials/header.php';
?>

<h2 class="page-title"><?= $edition ? 'Modifier une annonce' : 'Nouvelle annonce' ?></h2>
<p class="page-sub">Tous les champs sont obligatoires sauf la photo.</p>

<?php if ($erreur): ?>
  <div class="alert danger">
    <h4>Erreur</h4>
    <p><?= e($erreur) ?></p>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" action="annonce-form.php<?= $edition ? '?id=' . e($id) : '' ?>">
  <div class="auth-wrap" style="margin-top:0; max-width:720px;">

    <div class="field">
      <label for="categorie">Catégorie</label>
      <select id="categorie" name="categorie" required>
        <option value="">Choisir</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e($cat['NoCategorie']) ?>" <?= selected((int)$categorie === (int)$cat['NoCategorie']) ?>>
            <?= e($cat['Categorie']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="descriptionAbregee">Description abrégée</label>
      <input type="text" id="descriptionAbregee" name="descriptionAbregee" maxlength="50" value="<?= e($descriptionAbregee) ?>" placeholder="Maximum 50 caractères" required>
      <div class="hint">Apparaîtra dans la liste des annonces. <span id="cpt-abregee" class="char-count">0 / 50</span></div>
    </div>

    <div class="field">
      <label for="descriptionComplete">Description complète</label>
      <textarea id="descriptionComplete" name="descriptionComplete" rows="5" maxlength="250" placeholder="Maximum 250 caractères" required><?= e($descriptionComplete) ?></textarea>
      <div class="hint"><span id="cpt-complete" class="char-count">0 / 250</span></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="prix">Prix en $</label>
        <input type="text" id="prix" name="prix" value="<?= e($prix) ?>" placeholder="0,00 - laisser vide si à donner">
      </div>

      <div class="field">
        <label for="etat">État</label>
        <select id="etat" name="etat">
          <option value="1" <?= selected((int)$etat === 1) ?>>Actif</option>
          <option value="2" <?= selected((int)$etat === 2) ?>>Inactif</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="photo">Photo</label>
      <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
      <div class="hint">Une vignette de 144 px de largeur sera générée automatiquement.</div>

      <?php if ($photoActuelle && file_exists($photoActuelle)): ?>
        <div style="margin-top:10px;">
          <strong>Photo actuelle :</strong><br>
          <img src="<?= e($photoActuelle) ?>" alt="Photo actuelle" style="max-width:220px;height:auto;border:1px solid #999;margin-top:6px;">
        </div>
      <?php endif; ?>
    </div>

    <div class="actions-bar">
      <button type="submit" class="btn btn-primary">
        <?= $edition ? 'Enregistrer les modifications' : 'Publier l\'annonce' ?>
      </button>
      <a href="mes-annonces.php" class="btn btn-ghost">Annuler</a>
    </div>
  </div>
</form>

<script>
function majCompteur(inputId, compteurId, max) {
    const input    = document.getElementById(inputId);
    const compteur = document.getElementById(compteurId);
    function maj() {
        const n = input.value.length;
        compteur.textContent = n + ' / ' + max;
        compteur.style.color = n >= max ? '#b91c1c' : '';
    }
    input.addEventListener('input', maj);
    maj();
}
majCompteur('descriptionAbregee', 'cpt-abregee', 50);
majCompteur('descriptionComplete', 'cpt-complete', 250);

document.querySelector('form').addEventListener('submit', function () {
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';
});
</script>

<?php include '_partials/footer.php'; ?>