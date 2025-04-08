<?php
require_once 'auth.php';
exigerConnexion();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$tache_id = intval($_GET['id']);

// Vérifier que la tâche appartient bien à l'utilisateur
$requete = $pdo->prepare("
    SELECT id FROM taches 
    WHERE id = ? AND utilisateur_id = ?
");
$requete->execute([$tache_id, $_SESSION['utilisateur_id']]);

if ($requete->rowCount() === 0) {
    header('Location: dashboard.php');
    exit;
}

// Suppression de la tâche
$requete = $pdo->prepare("DELETE FROM taches WHERE id = ? AND utilisateur_id = ?");
$requete->execute([$tache_id, $_SESSION['utilisateur_id']]);

// Redirection vers le dashboard
header('Location: dashboard.php');
exit;
?>
