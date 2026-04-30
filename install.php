<?php
/**
 * Utilitaire d'installation de la base de données.
 *
 * - Crée (ou recrée) la base PJF_NomEquipe avec les 4 tables.
 * - Peuple les tables à partir des fichiers CSV du dossier /data.
 * - Crée le dossier /photos-annonce s'il n'existe pas.
 *
 * Pour exécuter : http://localhost/lespac/install.php
 *
 * ATTENTION : ce script DROP la base existante. À utiliser uniquement
 * pour initialiser la BD ou la réinitialiser pendant le développement.
 */

// -------- Configuration --------
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';                   // Mot de passe vide par défaut sur WampServer
$dbName = 'pjf_equipexyz';      // À changer pour le nom de votre équipe

$dataFolder   = __DIR__ . '/data';
$photosFolder = __DIR__ . '/photos-annonce';

// -------- Fonctions utilitaires --------
function msg($texte, $type = 'ok') {
    $couleur = ['ok' => '#060', 'err' => '#c00', 'info' => '#000'][$type] ?? '#000';
    echo "<li style=\"color:$couleur;\">$texte</li>\n";
    @ob_flush(); @flush();
}

function lireCsv($chemin) {
    $lignes = [];
    if (($f = fopen($chemin, 'r')) === false) {
        return false;
    }
    $entetes = fgetcsv($f);
    while (($donnees = fgetcsv($f)) !== false) {
        if (count($donnees) === 1 && $donnees[0] === null) continue; // ligne vide
        $lignes[] = array_combine($entetes, $donnees);
    }
    fclose($f);
    return $lignes;
}

// -------- En-tête HTML --------
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Installation — Les petites annonces GG</title>
<link rel="stylesheet" href="styles.css">
<style>
  ul.log { list-style: none; padding: 0; font-family: monospace; font-size: 13px; }
  ul.log li { padding: 4px 8px; border-bottom: 1px solid #eee; }
</style>
</head>
<body>
<div class="app">
  <header class="app-header">
    <div class="brand-row">
      <div class="brand">
        <h1>Installation <span class="gg">GG</span></h1>
        <div class="tagline">Création de la base de données et chargement des données initiales</div>
      </div>
    </div>
  </header>

  <main>
    <h2 class="page-title">Journal d'installation</h2>
    <p class="page-sub">Base : <strong><?= $dbName ?></strong> sur <strong><?= $dbHost ?></strong></p>

    <ul class="log">
<?php

// -------- 1. Connexion au serveur MySQL --------
$cnx = @mysqli_connect($dbHost, $dbUser, $dbPass);
if (!$cnx) {
    msg('Échec de la connexion au serveur MySQL : ' . mysqli_connect_error(), 'err');
    echo '</ul></main></div></body></html>';
    exit;
}
msg("Connecté au serveur MySQL ($dbHost)");

// -------- 2. (Re)création de la base --------
mysqli_query($cnx, "DROP DATABASE IF EXISTS `$dbName`");
if (!mysqli_query($cnx, "CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    msg('Erreur création BD : ' . mysqli_error($cnx), 'err');
    exit;
}
mysqli_select_db($cnx, $dbName);
mysqli_set_charset($cnx, 'utf8mb4');
msg("Base de données <strong>$dbName</strong> créée.");

// -------- 3. Création des tables --------
$tables = [
    'categories' => "
        CREATE TABLE categories (
            NoCategorie INT UNSIGNED NOT NULL,
            Categorie VARCHAR(20) NOT NULL,
            PRIMARY KEY (NoCategorie)
        ) ENGINE=InnoDB",

    'utilisateurs' => "
        CREATE TABLE utilisateurs (
            NoUtilisateur INT UNSIGNED NOT NULL AUTO_INCREMENT,
            Courriel VARCHAR(50) NOT NULL,
            MotDePasse VARCHAR(15) NOT NULL,
            Creation DATETIME NOT NULL,
            NbConnexions INT UNSIGNED NOT NULL DEFAULT 0,
            Statut TINYINT UNSIGNED NOT NULL DEFAULT 0,
            NoEmpl INT UNSIGNED NULL,
            Nom VARCHAR(25) NULL,
            Prenom VARCHAR(20) NULL,
            NoTelMaison VARCHAR(15) NULL,
            NoTelTravail VARCHAR(21) NULL,
            NoTelCellulaire VARCHAR(15) NULL,
            Modification DATETIME NULL,
            AutresInfos VARCHAR(50) NULL,
            PRIMARY KEY (NoUtilisateur),
            UNIQUE KEY uniq_courriel (Courriel)
        ) ENGINE=InnoDB",

    'connexions' => "
        CREATE TABLE connexions (
            NoConnexion INT UNSIGNED NOT NULL AUTO_INCREMENT,
            NoUtilisateur INT UNSIGNED NOT NULL,
            Connexion DATETIME NOT NULL,
            Deconnexion DATETIME NULL,
            PRIMARY KEY (NoConnexion),
            KEY idx_user (NoUtilisateur),
            CONSTRAINT fk_conn_user FOREIGN KEY (NoUtilisateur)
                REFERENCES utilisateurs(NoUtilisateur)
                ON DELETE CASCADE
        ) ENGINE=InnoDB",

    'annonces' => "
        CREATE TABLE annonces (
            NoAnnonce INT UNSIGNED NOT NULL AUTO_INCREMENT,
            NoUtilisateur INT UNSIGNED NOT NULL,
            Parution DATETIME NOT NULL,
            Categorie INT UNSIGNED NOT NULL,
            DescriptionAbregee VARCHAR(50) NOT NULL,
            DescriptionComplete VARCHAR(250) NOT NULL,
            Prix DECIMAL(7,2) NOT NULL DEFAULT 0.00,
            Photo VARCHAR(50) NULL,
            MiseAJour DATETIME NOT NULL,
            Etat TINYINT UNSIGNED NOT NULL DEFAULT 1,
            PRIMARY KEY (NoAnnonce),
            KEY idx_ann_user (NoUtilisateur),
            KEY idx_ann_cat (Categorie),
            CONSTRAINT fk_ann_user FOREIGN KEY (NoUtilisateur)
                REFERENCES utilisateurs(NoUtilisateur),
            CONSTRAINT fk_ann_cat FOREIGN KEY (Categorie)
                REFERENCES categories(NoCategorie)
        ) ENGINE=InnoDB",
];

foreach ($tables as $nom => $sql) {
    if (!mysqli_query($cnx, $sql)) {
        msg("Erreur création table $nom : " . mysqli_error($cnx), 'err');
        exit;
    }
    msg("Table <strong>$nom</strong> créée.");
}

// -------- 4. Chargement des CSV --------
// Ordre important : catégories et utilisateurs avant annonces et connexions (clés étrangères)
$ordreImport = [
    'categories'   => ['NoCategorie', 'Categorie'],
    'utilisateurs' => ['NoUtilisateur', 'Courriel', 'MotDePasse', 'Creation', 'NbConnexions',
                       'Statut', 'NoEmpl', 'Nom', 'Prenom', 'NoTelMaison', 'NoTelTravail',
                       'NoTelCellulaire', 'Modification', 'AutresInfos'],
    'annonces'     => ['NoAnnonce', 'NoUtilisateur', 'Parution', 'Categorie', 'DescriptionAbregee',
                       'DescriptionComplete', 'Prix', 'Photo', 'MiseAJour', 'Etat'],
    'connexions'   => ['NoConnexion', 'NoUtilisateur', 'Connexion', 'Deconnexion'],
];

foreach ($ordreImport as $table => $colonnes) {
    $fichier = "$dataFolder/$table.csv";
    if (!file_exists($fichier)) {
        msg("Fichier $fichier introuvable, table $table laissée vide.", 'err');
        continue;
    }

    $lignes = lireCsv($fichier);
    if ($lignes === false) {
        msg("Lecture impossible de $fichier", 'err');
        continue;
    }

    $listeCols = '`' . implode('`,`', $colonnes) . '`';
    $placeholders = rtrim(str_repeat('?,', count($colonnes)), ',');
    $sql = "INSERT INTO `$table` ($listeCols) VALUES ($placeholders)";
    $stmt = mysqli_prepare($cnx, $sql);

    $nb = 0;
    foreach ($lignes as $ligne) {
        $valeurs = [];
        foreach ($colonnes as $col) {
            $val = $ligne[$col] ?? '';
            $valeurs[] = ($val === '' ? null : $val);
        }
        $types = str_repeat('s', count($valeurs)); // tout en string, MySQL convertit
        mysqli_stmt_bind_param($stmt, $types, ...$valeurs);
        if (!mysqli_stmt_execute($stmt)) {
            msg("Erreur insertion $table ligne " . ($nb + 1) . " : " . mysqli_stmt_error($stmt), 'err');
            continue;
        }
        $nb++;
    }
    mysqli_stmt_close($stmt);
    msg("Table <strong>$table</strong> : $nb ligne(s) insérée(s).");
}

// -------- 5. Dossier des photos --------
if (!is_dir($photosFolder)) {
    if (mkdir($photosFolder, 0775, true)) {
        msg("Dossier <strong>photos-annonce/</strong> créé.");
    } else {
        msg("Impossible de créer le dossier $photosFolder", 'err');
    }
} else {
    msg("Dossier <strong>photos-annonce/</strong> déjà présent.");
}

mysqli_close($cnx);
?>
    </ul>

    <div class="alert ok" style="margin-top: 20px;">
      <h4>Installation terminée</h4>
      <p>
        La base <strong><?= $dbName ?></strong> est prête.
        Compte administrateur : <strong>admin@gmail.com</strong> / <strong>Secret123</strong>.
      </p>
    </div>

    <div class="actions-bar">
      <a href="index.php" class="btn btn-primary">Aller à la connexion</a>
      <a href="_apercu.php" class="btn btn-ghost">Aperçu de la maquette</a>
    </div>
  </main>

  <footer class="app-footer">
    <span>Équipe XYZ · 420-W46 · Hiver 2026</span>
    <span>Cégep Gérald-Godin</span>
  </footer>
</div>
</body>
</html>
