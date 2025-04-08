<?php
require_once 'auth.php';
exigerConnexion();

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO taches (utilisateur_id, titre, description, date_limite, priorite) 
            VALUES (:utilisateur_id, :titre, :description, :date_limite, :priorite)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'utilisateur_id' => $_SESSION['user_id'],
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'date_limite' => $_POST['date_limite'],
        'priorite' => $_POST['priorite']
    ]);
    
    // Validation des champs
    if (empty($titre)) {
        $erreurs[] = "Le titre est requis";
    }
    
    if (empty($date_limite)) {
        $erreurs[] = "La date limite est requise";
    } elseif (strtotime($date_limite) === false) {
        $erreurs[] = "Format de date invalide";
    }
    
    // Si aucune erreur, on ajoute la tâche
    if (empty($erreurs)) {
        $requete = $pdo->prepare("
            INSERT INTO taches (utilisateur_id, titre, description, date_limite) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($requete->execute([$_SESSION['utilisateur_id'], $titre, $description, $date_limite])) {
            $success = true;
            // Redirection vers le dashboard après 2 secondes
            header("refresh:2;url=dashboard.php");
        } else {
            $erreurs[] = "Une erreur est survenue lors de l'ajout de la tâche";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une tâche | Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter une tâche</h1>
        
        <?php if ($success): ?>
            <div class="alert success">
                La tâche a été ajoutée avec succès ! Redirection vers le tableau de bord...
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
        
        <?php if (!$success): ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="titre">Titre</label>
                    <input type="text" id="titre" name="titre" value="<?= $_POST['titre'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?= $_POST['description'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="date_limite">Date limite</label>
                    <input type="date" id="date_limite" name="date_limite" value="<?= $_POST['date_limite'] ?? date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="priorite">Priorité</label>
                    <select class="form-control" id="priorite" name="priorite">
                    <option value="Normale">Normale</option>
                    <option value="Élevée">Élevée</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Ajouter</button>
                    <a href="dashboard.php" class="btn-link">Annuler</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>