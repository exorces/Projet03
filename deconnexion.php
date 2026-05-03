<?php

require_once '_includes/db.php';

if (isset($_SESSION['NoConnexion'])) {

    $stmt = $pdo->prepare("
        UPDATE connexions
        SET Deconnexion = NOW()
        WHERE NoConnexion = :no
    ");

    $stmt->execute([
        'no' => $_SESSION['NoConnexion']
    ]);
}

session_destroy();

header('Location: index.php');
exit;