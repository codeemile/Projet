<?php
require_once 'auth.php';
exigerConnexion();

$erreurs = [];
$success = false;
$tache = null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$tache_id = intval($_GET['id']);

// Vérifier que la tâche appartient bien à l'utilisateur
$requete = $pdo->prepare("
    SELECT * FROM taches 
    WHERE id = ? AND utilisateur_id = ?
");
$requete->execute([$tache_id, $_SESSION['utilisateur_id']]);
$tache = $requete->fetch();

if (!$tache) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $titre = trim(htmlspecialchars($_POST['titre'] ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $date_limite = trim($_POST['date_limite'] ?? '');
    $statut = $_POST['statut'] ?? 'En attente';
    
    // Validation des champs
    if (empty($titre)) {
        $erreurs[] = "Le titre est requis";
    }
    
    if (empty($date_limite)) {
        $erreurs[] = "La date limite est requise";
    } elseif (strtotime($date_limite) === false) {
        $erreurs[] = "Format de date invalide";
    }
    
    if ($statut !== 'En attente' && $statut !== 'Terminée') {
        $erreurs[] = "Statut invalide";
    }
    
    // Si aucune erreur, on modifie la tâche
    if (empty($erreurs)) {
        $requete = $pdo->prepare("
            UPDATE taches 
            SET titre = ?, description = ?, date_limite = ?, statut = ? 
            WHERE id = ? AND utilisateur_id = ?
        ");
        
        if ($requete->execute([$titre, $description, $date_limite, $statut, $tache_id, $_SESSION['utilisateur_id']])) {
            $success = true;
            // Mettre à jour les données de la tâche
            $tache['titre'] = $titre;
            $tache['description'] = $description;
            $tache['date_limite'] = $date_limite;
            $tache['statut'] = $statut;
            
            // Redirection vers le dashboard après 2 secondes
            header("refresh:2;url=dashboard.php");
        } else {
            $erreurs[] = "Une erreur est survenue lors de la modification de la tâche";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une tâche | Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Modifier une tâche</h1>
        
        <?php if ($success): ?>
            <div class="alert success">
                La tâche a été modifiée avec succès ! Redirection vers le tableau de bord...
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erreurs)): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?= $erreur ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($tache['titre']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($tache['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="date_limite">Date limite</label>
                <input type="date" id="date_limite" name="date_limite" value="<?= $tache['date_limite'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="En attente" <?= $tache['statut'] === 'En attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="Terminée" <?= $tache['statut'] === 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priorite">Priorité</label>
                <select class="form-control" id="priorite" name="priorite">
                <option value="Normale" <?php echo ($tache['priorite'] == 'Normale') ? 'selected' : ''; ?>>Normale</option>
                <option value="Élevée" <?php echo ($tache['priorite'] == 'Élevée') ? 'selected' : ''; ?>>Élevée</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer les modifications</button>
                <a href="dashboard.php" class="btn-link">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>