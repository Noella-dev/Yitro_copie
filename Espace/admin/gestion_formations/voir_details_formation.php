<?php
session_start();

require_once '../../config/db.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../authentification/connexion-admin.php");
    exit();
}
$formation_id = $_GET['id'] ?? 0;

if ($formation_id == 0) {
    // Rediriger si l'ID est manquant
    header("Location: gestion_formations.php");
    exit;
}

// 1. Charger les informations de la Formation Principale
$formation_details = null;
try {
    $stmt = $pdo->prepare("SELECT nom_formation FROM formations WHERE id_formation = ?");
    $stmt->execute([$formation_id]);
    $formation_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$formation_details) {
        // Rediriger si l'ID n'existe pas
        header("Location: gestion_formations.php?error=notfound");
        exit;
    }
} catch (PDOException $e) {
    // Gérer l'erreur de base de données
    $error_message = "Erreur de chargement de la formation : " . $e->getMessage();
}

// 2. Charger les Sous-Formations liées
$sous_formations = [];
try {
    $sql_contenu = "SELECT id_contenu, sous_formation FROM contenu_formations WHERE formation_id = ? ";
    $stmt_contenu = $pdo->prepare($sql_contenu);
    $stmt_contenu->execute([$formation_id]);
    $sous_formations = $stmt_contenu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message .= " Erreur de chargement des sous-formations : " . $e->getMessage();
}

// NOTE: Le traitement des actions MODIFIER/SUPPRIMER devra être ajouté ici ou dans un fichier de traitement séparé.
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Détails de <?= htmlspecialchars($formation_details['nom_formation']) ?></title>
  <link rel="stylesheet" href="../../../asset/css/styles/style-formateur.css">
  <link rel="stylesheet" href="../gestion_utilisateurs/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    /* Ajoutez des styles pour les boutons d'action */
    .btn-action.btn-edit { background-color: #ffc107; }
    .btn-action.btn-delete { background-color: #dc3545; }
    .btn-action { color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 5px; }
  </style>
</head>
<body>
  <div class="sidebar">...</div> 

  <div class="main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Détail</span>
                <h2>Sous-Formations de : <?= htmlspecialchars($formation_details['nom_formation']) ?></h2>
            </div>
        </div>
        
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>    
        <?php endif; ?>

        <a href="gestion_formations.php" class="btn-action btn-view" style="margin-bottom: 20px; display: inline-block; background-color: #6c757d;">
            <i class="fas fa-arrow-left"></i> Retour aux Formations
        </a>

        <div class="gest--container">
            <h2 class="gest--title">Liste des Sous-Formations Existantes</h2>
            <div class="table--wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Contenu</th>
                            <th>Nom de la Sous-Formation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="gest-list-contenu">
                        <?php if (!empty($sous_formations)): ?>
                            <?php foreach ($sous_formations as $contenu): ?>
                                <tr class="table--row">
                                    <td><?= $contenu['id_contenu'] ?></td>
                                    <td><?= htmlspecialchars($contenu['sous_formation']) ?></td>
                                    <td>
                                        <a href="modifier_contenu.php?id=<?= $contenu['id_contenu'] ?>" class="btn-action btn-edit">Modifier</a>
                                        <a href="supprimer_contenu.php?id=<?= $contenu['id_contenu'] ?>" class="btn-action btn-delete" onclick="return confirm('Supprimer cette sous-formation ?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Aucune sous-formation n'existe pour cette formation principale.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>