<?php
require_once 'auth.php';
exigerConnexion();

$utilisateur = getUtilisateurInfo();
$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $mot_de_passe_actuel = $_POST['mot_de_passe_actuel'] ?? '';
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
    
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
    
    // Vérifier si l'email existe déjà pour un autre utilisateur
    if ($email !== $utilisateur['email']) {
        $verif = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $verif->execute([$email, $_SESSION['utilisateur_id']]);
        if ($verif->rowCount() > 0) {
            $erreurs[] = "Cette adresse email est déjà utilisée par un autre utilisateur";
        }
    }
    
    // Vérifier le mot de passe actuel
    if (!empty($mot_de_passe_actuel) || !empty($nouveau_mot_de_passe)) {
        $verif = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
        $verif->execute([$_SESSION['utilisateur_id']]);
        $hash = $verif->fetchColumn();
        
        if (!password_verify($mot_de_passe_actuel, $hash)) {
            $erreurs[] = "Le mot de passe actuel est incorrect";
        }
        
        if (!empty($nouveau_mot_de_passe)) {
            if (strlen($nouveau_mot_de_passe) < 6) {
                $erreurs[] = "Le nouveau mot de passe doit contenir au moins 6 caractères";
            }
            
            if ($nouveau_mot_de_passe !== $confirmer_mot_de_passe) {
                $erreurs[] = "Les nouveaux mots de passe ne correspondent pas";
            }
        }
    }
    
    // Mise à jour du profil
    if (empty($erreurs)) {
        // Construire la requête SQL en fonction des champs à mettre à jour
        $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?";
        $params = [$nom, $prenom, $email];
        
        if (!empty($nouveau_mot_de_passe)) {
            $sql .= ", mot_de_passe = ?";
            $params[] = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $_SESSION['utilisateur_id'];
        
        $requete = $pdo->prepare($sql);
        
        if ($requete->execute($params)) {
            $success = true;
            // Mettre à jour les informations de l'utilisateur
            $utilisateur['nom'] = $nom;
            $utilisateur['prenom'] = $prenom;
            $utilisateur['email'] = $email;
        } else {
            $erreurs[] = "Une erreur est survenue lors de la mise à jour du profil";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil | Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Modifier mon profil</h1>
        
        <?php if ($success): ?>
            <div class="alert success">
                Votre profil a été mis à jour avec succès !
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
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>
            </div>
            
            <hr class="separator">
            
            <h3>Changer de mot de passe</h3>
            <p class="info">Laissez les champs vides si vous ne souhaitez pas changer de mot de passe</p>
            
            <div class="form-group">
                <label for="mot_de_passe_actuel">Mot de passe actuel</label>
                <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel">
            </div>
            
            <div class="form-group">
                <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe">
            </div>
            
            <div class="form-group">
                <label for="confirmer_mot_de_passe">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer les modifications</button>
                <a href="dashboard.php" class="btn-link">Retour au tableau de bord</a>
            </div>
        </form>
    </div>
</body>
</html>