<?php
/**
 * Configuration de la base de données.
 * À inclure depuis toutes les pages qui ont besoin d'accéder à la BD.
 *
 * Usage :
 *   require_once '_includes/db.php';
 *   $resultat = mysqli_query($cnx, "SELECT * FROM utilisateurs");
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Mot de passe vide par défaut sur WampServer
define('DB_NAME', 'pjf_equipexyz'); // À changer pour le nom de votre équipe

$cnx = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$cnx) {
    die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
}

mysqli_set_charset($cnx, 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
?>