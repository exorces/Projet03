<?php

/**
 * Installation — crée la base de données, les tables, et un compte administrateur.
 * http://localhost/Projet03/install.php
 *
 * ATTENTION : recrée la base à chaque exécution (DROP DATABASE).
 */

$dbHost  = 'localhost';
$dbUser  = 'root';
$dbPass  = '';
$dbName  = 'pjf_equipexyz';

$adminCourriel = 'admin@gmail.com';
$adminMdp      = 'Secret123';

function msg($texte, $type = 'ok') {
    $couleur = ['ok' => '#060', 'err' => '#c00', 'info' => '#000'][$type] ?? '#000';
    echo "<li style=\"color:$couleur;\">$texte</li>\n";
    @ob_flush(); @flush();
}
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
        <div class="tagline">Création de la base de données et du compte administrateur</div>
      </div>
    </div>
  </header>

  <main>
    <h2 class="page-title">Journal d'installation</h2>
    <p class="page-sub">Base : <strong><?= $dbName ?></strong> sur <strong><?= $dbHost ?></strong></p>

    <ul class="log">
<?php

// 1. Connexion MySQL
$cnx = @mysqli_connect($dbHost, $dbUser, $dbPass);
if (!$cnx) {
    msg('Échec de connexion au serveur MySQL : ' . mysqli_connect_error(), 'err');
    echo '</ul></main></div></body></html>';
    exit;
}
msg("Connecté au serveur MySQL ($dbHost).");

// 2. (Re)création de la base
mysqli_query($cnx, "DROP DATABASE IF EXISTS `$dbName`");
if (!mysqli_query($cnx, "CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    msg('Erreur création base : ' . mysqli_error($cnx), 'err');
    exit;
}
mysqli_select_db($cnx, $dbName);
mysqli_set_charset($cnx, 'utf8mb4');
msg("Base <strong>$dbName</strong> créée.");

// 3. Tables
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
            Courriel      VARCHAR(50)  NOT NULL,
            MotDePasse    VARCHAR(15)  NOT NULL,
            Creation      DATETIME     NOT NULL,
            NbConnexions  INT UNSIGNED NOT NULL DEFAULT 0,
            Statut        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            NoEmpl        INT UNSIGNED NULL,
            Nom           VARCHAR(25)  NULL,
            Prenom        VARCHAR(20)  NULL,
            NoTelMaison   VARCHAR(15)  NULL,
            NoTelTravail  VARCHAR(21)  NULL,
            NoTelCellulaire VARCHAR(15) NULL,
            Modification  DATETIME     NULL,
            AutresInfos   VARCHAR(50)  NULL,
            PRIMARY KEY (NoUtilisateur),
            UNIQUE KEY uniq_courriel (Courriel)
        ) ENGINE=InnoDB",

    'connexions' => "
        CREATE TABLE connexions (
            NoConnexion   INT UNSIGNED NOT NULL AUTO_INCREMENT,
            NoUtilisateur INT UNSIGNED NOT NULL,
            Connexion     DATETIME NOT NULL,
            Deconnexion   DATETIME NULL,
            PRIMARY KEY (NoConnexion),
            KEY idx_user (NoUtilisateur),
            CONSTRAINT fk_conn_user FOREIGN KEY (NoUtilisateur)
                REFERENCES utilisateurs(NoUtilisateur) ON DELETE CASCADE
        ) ENGINE=InnoDB",

    'annonces' => "
        CREATE TABLE annonces (
            NoAnnonce          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            NoUtilisateur      INT UNSIGNED NOT NULL,
            Parution           DATETIME     NOT NULL,
            Categorie          INT UNSIGNED NOT NULL,
            DescriptionAbregee VARCHAR(50)  NOT NULL,
            DescriptionComplete VARCHAR(250) NOT NULL,
            Prix               DECIMAL(7,2) NOT NULL DEFAULT 0.00,
            Photo              VARCHAR(50)  NULL,
            MiseAJour          DATETIME     NOT NULL,
            Etat               TINYINT UNSIGNED NOT NULL DEFAULT 1,
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

// 4. Catégories de base
$categoriesSql = [
    [1, 'Location'],
    [2, 'Recherche'],
    [3, 'À vendre'],
    [4, 'À donner'],
    [5, 'Service offert'],
    [6, 'Autre'],
];

$stmtCat = mysqli_prepare($cnx, "INSERT INTO categories (NoCategorie, Categorie) VALUES (?, ?)");
foreach ($categoriesSql as [$no, $nom]) {
    mysqli_stmt_bind_param($stmtCat, 'is', $no, $nom);
    mysqli_stmt_execute($stmtCat);
}
mysqli_stmt_close($stmtCat);
msg(count($categoriesSql) . ' catégorie(s) insérée(s).');

// 5. Compte administrateur
$stmt = mysqli_prepare($cnx,
    "INSERT INTO utilisateurs (Courriel, MotDePasse, Creation, NbConnexions, Statut)
     VALUES (?, ?, NOW(), 0, 1)"
);
mysqli_stmt_bind_param($stmt, 'ss', $adminCourriel, $adminMdp);

if (mysqli_stmt_execute($stmt)) {
    $adminId = (int)mysqli_insert_id($cnx);
    msg("Compte administrateur <strong>$adminCourriel</strong> créé (Statut = 1).");
} else {
    $adminId = 0;
    msg("Erreur création admin : " . mysqli_stmt_error($stmt), 'err');
}
mysqli_stmt_close($stmt);

// 6. Annonce de test
if ($adminId > 0) {
    $stmt = mysqli_prepare($cnx,
        "INSERT INTO annonces
            (NoUtilisateur, Parution, Categorie, DescriptionAbregee, DescriptionComplete, Prix, Photo, MiseAJour, Etat)
         VALUES
            (?, NOW(), 3, 'Vélo de montagne — excellent état', 'Vélo de montagne 27 vitesses, cadre aluminium, freins à disque hydrauliques. Peu utilisé, aucune égratignure. Idéal pour sentiers et routes. Accessoires inclus (casque, pompe, antivol).', 150.00, NULL, NOW(), 1)"
    );
    mysqli_stmt_bind_param($stmt, 'i', $adminId);
    if (mysqli_stmt_execute($stmt)) {
        msg("Annonce de test insérée (catégorie : À vendre, prix : 150,00 $).");
    } else {
        msg("Erreur insertion annonce de test : " . mysqli_stmt_error($stmt), 'err');
    }
    mysqli_stmt_close($stmt);
}

// 7. Dossier photos
$photosFolder = __DIR__ . '/photos-annonce';
if (!is_dir($photosFolder)) {
    mkdir($photosFolder, 0775, true)
        ? msg("Dossier <strong>photos-annonce/</strong> créé.")
        : msg("Impossible de créer $photosFolder", 'err');
} else {
    msg("Dossier <strong>photos-annonce/</strong> déjà présent.");
}

mysqli_close($cnx);
?>
    </ul>

    <div class="alert ok" style="margin-top:20px;">
      <h4>Installation terminée</h4>
      <p>
        Base <strong><?= $dbName ?></strong> prête.<br>
        Compte administrateur : <strong><?= $adminCourriel ?></strong> / <strong><?= $adminMdp ?></strong>
      </p>
    </div>

    <div class="actions-bar">
      <a href="index.php" class="btn btn-primary">Aller à la connexion</a>
    </div>
  </main>

  <footer class="app-footer">
    <span>Équipe Rayane et Ken · 420-W46 · Hiver 2026</span>
    <span>Cégep Gérald-Godin</span>
  </footer>
</div>
</body>
</html>
