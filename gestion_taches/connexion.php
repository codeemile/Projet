<?php
require_once 'db.php';

$erreur = null;

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs";
    } else {
        $requete = $pdo->prepare("SELECT id, mot_de_passe FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);
        $utilisateur = $requete->fetch();
        
        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $erreur = "Email ou mot de passe incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Connexion</h1>
        
        <?php if ($erreur): ?>
            <div class="alert error"><?= $erreur ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Se connecter</button>
                <a href="inscription.php" class="btn-link">Pas encore inscrit ? Créer un compte</a>
            </div>
        </form>
        
        <div class="footer">
            <a href="index.php">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>