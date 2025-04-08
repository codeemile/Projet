<?php
require_once 'auth.php';
exigerConnexion();

$utilisateur = getUtilisateurInfo();
$tri = $_GET['tri'] ?? 'date_limite';
$ordre = $_GET['ordre'] ?? 'ASC';
$filtre = $_GET['filtre'] ?? 'toutes';

// Construction de la requête de filtrage
$condition = "utilisateur_id = ?";
$params = [$_SESSION['utilisateur_id']];

if ($filtre === 'en_cours') {
    $condition .= " AND statut = 'En attente'";
} elseif ($filtre === 'terminees') {
    $condition .= " AND statut = 'Terminée'";
} elseif ($filtre === 'retard') {
    $condition .= " AND statut = 'En attente' AND date_limite < CURDATE()";
} elseif ($filtre === 'a_venir') {
    $condition .= " AND statut = 'En attente' AND date_limite >= CURDATE()";
}

// Récupérer les tâches de l'utilisateur avec filtrage
$requete = $pdo->prepare("
    SELECT * FROM taches 
    WHERE $condition 
    ORDER BY $tri $ordre
");
$requete->execute($params);
$taches = $requete->fetchAll();

// Récupérer les notifications pour les tâches proches de leur date limite (dans 3 jours ou moins)
$notifications = [];
$aujourdhui = time();
$trois_jours = 3 * 24 * 60 * 60;

foreach ($taches as $tache) {
    if ($tache['statut'] === 'En attente') {
        $date_limite = strtotime($tache['date_limite']);
        $difference = $date_limite - $aujourdhui;
        
        if ($difference > 0 && $difference <= $trois_jours) {
            $jours_restants = ceil($difference / (24 * 60 * 60));
            $notifications[] = [
                'tache' => $tache['titre'],
                'jours' => $jours_restants,
                'date' => date('d/m/Y', $date_limite)
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Gestion de Tâches</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Tableau de bord</h1>
            <div class="user-info">
                Bonjour, <?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>
                <a href="deconnexion.php" class="btn-link">Déconnexion</a>
            </div>
        </header>
        <?php if (!empty($notifications)): ?>
    <div class="notifications">
        <h3>Notifications</h3>
        <ul>
            <?php foreach ($notifications as $notif): ?>
                <li class="notification">
                    <span class="notification-icon">⚠️</span>
                    <span class="notification-text">
                        La tâche "<strong><?= htmlspecialchars($notif['tache']) ?></strong>" 
                        est à échéance dans <?= $notif['jours'] ?> jour<?= $notif['jours'] > 1 ? 's' : '' ?> 
                        (<?= $notif['date'] ?>)
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
        <div class="actions">
            <a href="ajout_tache.php" class="btn">Ajouter une tâche</a>
            <a href="profil.php" class="btn">Modifier mon profil</a>
        </div>
        
        <div class="taches-container">
            <h2>Mes tâches</h2>
            
            <div class="filtres">
                <span>Filtrer :</span>
                <a href="?filtre=toutes" class="<?= $filtre === 'toutes' ? 'active' : '' ?>">Toutes</a> |
                <a href="?filtre=en_cours" class="<?= $filtre === 'en_cours' ? 'active' : '' ?>">En cours</a> |
                <a href="?filtre=terminees" class="<?= $filtre === 'terminees' ? 'active' : '' ?>">Terminées</a> |
                <a href="?filtre=retard" class="<?= $filtre === 'retard' ? 'active' : '' ?>">En retard</a> |
                <a href="?filtre=a_venir" class="<?= $filtre === 'a_venir' ? 'active' : '' ?>">À venir</a>
            </div>
            
            <div class="tri">
                <span>Trier par :</span>
                <a href="?filtre=<?= $filtre ?>&tri=date_limite&ordre=ASC">Date ↑</a> |
                <a href="?filtre=<?= $filtre ?>&tri=date_limite&ordre=DESC">Date ↓</a> |
                <a href="?filtre=<?= $filtre ?>&tri=statut&ordre=ASC">Statut ↑</a> |
                <a href="?filtre=<?= $filtre ?>&tri=statut&ordre=DESC">Statut ↓</a>
            </div>
            
            <?php if (empty($taches)): ?>
                <p class="no-tasks">Aucune tâche trouvée avec ce filtre. <a href="ajout_tache.php">Ajouter une tâche</a></p>
            <?php else: ?>
                <table class="table-taches">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Date limite</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taches as $tache): ?>
                            <?php
                                $classe_tache = '';
                                if ($tache['statut'] === 'Terminée') {
                                    $classe_tache = 'terminee';
                                } elseif (strtotime($tache['date_limite']) < time()) {
                                    $classe_tache = 'retard';
                                }
                            ?>
                            <tr class="<?= $classe_tache ?>">
                                <td data-label="Titre"><?= htmlspecialchars($tache['titre']) ?></td>
                                <td data-label="Description"><?= htmlspecialchars($tache['description']) ?></td>
                                <td data-label="Date limite"><?= date('d/m/Y', strtotime($tache['date_limite'])) ?></td>
                                <td data-label="Statut"><?= $tache['statut'] ?></td>
                                <td data-label="Actions" class="actions">
                                    <?php if ($tache['statut'] !== 'Terminée'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="terminer">
                                            <input type="hidden" name="tache_id" value="<?= $tache['id'] ?>">
                                            <button type="submit" class="btn-action btn-success">Terminer</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="modifier_tache.php?id=<?= $tache['id'] ?>" class="btn-action">Modifier</a>
                                    
                                    <a href="supprimer_tache.php?id=<?= $tache['id'] ?>" 
                                       class="btn-action btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')">
                                       Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>