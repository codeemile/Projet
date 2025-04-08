<?php
require_once 'db.php';

function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

function exigerConnexion() {
    if (!estConnecte()) {
        header('Location: connexion.php');
        exit;
    }
}

function getUtilisateurInfo() {
    global $pdo;
    
    if (!estConnecte()) {
        return null;
    }
    
    $requete = $pdo->prepare("SELECT id, nom, prenom, email FROM utilisateurs WHERE id = ?");
    $requete->execute([$_SESSION['utilisateur_id']]);
    return $requete->fetch();
}
?>