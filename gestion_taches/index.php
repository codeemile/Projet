<?php
require_once 'auth.php';

if (estConnecte()) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur Gestion de Tâches</h1>
        <p>Une application simple pour gérer vos tâches quotidiennes.</p>
        
        <div class="actions">
            <a href="connexion.php" class="btn">Se connecter</a>
            <a href="inscription.php" class="btn">S'inscrire</a>
        </div>
    </div>
</body>
</html>