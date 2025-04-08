<?php
require_once 'db.php';

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    
    // Validation des champs
    if (empty($nom)) {
        $erreurs[] = "Le nom est requis";
    }
    
    if (empty($prenom)) {
        $erreurs[] = "Le prénom est requis";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Adresse email invalide";
    }
    
    if (strlen($mot_de_passe) < 6) {
        $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    if ($mot_de_passe !== $confirmation) {
        $erreurs[] = "Les mots de passe ne correspondent pas";
    }
    
    // Vérifier si l'email existe déjà
    $verif = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $verif->execute([$email]);
    if ($verif->rowCount() > 0) {
        $erreurs[] = "Cette adresse email est déjà utilisée";
    }
    
    // Si aucune erreur, on enregistre l'utilisateur
    if (empty($erreurs)) {
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        $requete = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
        if ($requete->execute([$nom, $prenom, $email, $mot_de_passe_hash])) {
            $success = true;
        } else {
            $erreurs[] = "Une erreur est survenue lors de l'inscription";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        
        <?php if ($success): ?>
            <div class="alert success">
                Votre compte a été créé avec succès ! <a href="connexion.php">Se connecter</a>
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
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= $_POST['nom'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= $_POST['prenom'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="confirmation" name="confirmation" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">S'inscrire</button>
                    <a href="connexion.php" class="btn-link">Déjà inscrit ? Se connecter</a>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="footer">
            <a href="index.php">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>