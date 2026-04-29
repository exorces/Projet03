<?php
/**
 * Header partagé.
 *
 * Variables attendues (à définir AVANT l'inclusion) :
 *   $pageTitle  string  Titre affiché dans la balise <title> et utilisé par les pages.
 *   $navType    string  'public' (non authentifié), 'user' (utilisateur), 'admin' (administrateur).
 *   $current    string  Identifiant de la page courante pour mettre l'item de menu en surbrillance.
 *
 * Aucune logique applicative ici — uniquement la structure HTML.
 */

$pageTitle = isset($pageTitle) ? $pageTitle : 'Les petites annonces GG';
$navType   = isset($navType)   ? $navType   : 'public';
$current   = isset($current)   ? $current   : '';

$teamName  = 'Équipe XYZ';
$userName  = 'Roux, Ken-Li'; // Sera remplacé par la variable de session.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= $teamName ?> — <?= $pageTitle ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="app">

  <header class="app-header">
    <div class="brand-row">
      <div class="brand">
        <h1><a href="<?= $navType === 'admin' ? 'admin.php' : 'annonces.php' ?>">Les petites annonces <span class="gg">GG</span></a></h1>
        <div class="tagline">Le premier site de petites annonces au Cégep Gérald-Godin</div>
      </div>
      <?php if ($navType !== 'public'): ?>
      <div class="meta">
        <span class="user"><?= $userName ?></span>
        <span class="team"><?= $teamName ?> · Hiver 2026</span>
      </div>
      <?php else: ?>
      <div class="meta">
        <span class="team"><?= $teamName ?> · Hiver 2026</span>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($navType === 'user'): ?>
    <nav class="main-nav">
      <a href="annonces.php"     class="<?= $current === 'annonces'    ? 'active' : '' ?>">Annonces</a>
      <a href="mes-annonces.php" class="<?= $current === 'mes-annonces' ? 'active' : '' ?>">Mes annonces</a>
      <a href="profil.php"       class="<?= $current === 'profil'      ? 'active' : '' ?>">Mon profil</a>
      <a href="index.php">Déconnexion</a>
    </nav>
    <?php elseif ($navType === 'admin'): ?>
    <nav class="main-nav">
      <a href="annonces.php"           class="<?= $current === 'annonces'    ? 'active' : '' ?>">Toutes les annonces</a>
      <a href="admin-utilisateurs.php" class="<?= $current === 'utilisateurs' ? 'active' : '' ?>">Utilisateurs</a>
      <a href="admin-nettoyage.php"    class="<?= $current === 'nettoyage'   ? 'active' : '' ?>">Nettoyage</a>
      <a href="index.php">Déconnexion</a>
    </nav>
    <?php endif; ?>
  </header>

  <main>
