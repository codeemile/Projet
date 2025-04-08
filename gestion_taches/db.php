<?php
try {
    $host = 'localhost';
    $dbname = 'gestion_taches';
    $username = 'root';  // À modifier selon votre configuration
    $password = '';      // À modifier selon votre configuration
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

session_start();
?>