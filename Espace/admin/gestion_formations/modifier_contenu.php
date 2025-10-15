<?php
session_start();
require_once '../../config/db.php'; 

// --- Variables de Message ---
$error_message = '';
$success_message = '';
$contenu_details = null;

$id_contenu = $_GET['id'] ?? $_POST['id_contenu'] ?? 0;

// ******************************************************
// TRAITEMENT DE LA MISE À JOUR (POST)
// ******************************************************
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_contenu'])) {
    $nouveau_nom = trim($_POST['nouveau_nom'] ?? '');
    $parent_id = $_POST['formation_parent_id'] ?? 0;

    if ($id_contenu > 0 && !empty($nouveau_nom)) {
        try {
            $stmt = $pdo->prepare("UPDATE contenu_formations SET sous_formation = ? WHERE id_contenu = ?");
            
            if ($stmt->execute([$nouveau_nom, $id_contenu])) {
                $success_message = "La sous-formation a été modifiée avec succès.";
                
                // Journaliser l'action
                $admin_id = $_SESSION['user_id'] ?? 1;
                $details = "Modification sous-formation ID: " . $id_contenu . " vers: " . $nouveau_nom;
                $stmt_journal = $pdo->prepare("INSERT INTO journal_activite (admin_id, action, details) VALUES (?, 'Modification sous-formation', ?)");
                $stmt_journal->execute([$admin_id, $details]);

                // Rediriger après le succès pour éviter la soumission multiple
                header("Location: voir_details_formation.php?id=" . $parent_id . "&success=updated");
                exit;

            } else {
                $error_message = "Erreur lors de la mise à jour de la sous-formation.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur BD: " . $e->getMessage();
        }
    } else {
        $error_message = "Le nom de la sous-formation ne peut pas être vide.";
    }
}

// ******************************************************
// CHARGEMENT INITIAL DES DONNÉES (GET)
// ******************************************************
if ($id_contenu > 0) {
    try {
        $sql = "SELECT cf.id_contenu, cf.sous_formation, f.nom_formation, f.id_formation as parent_id
                FROM contenu_formations cf
                JOIN formations f ON cf.formation_id = f.id_formation
                WHERE cf.id_contenu = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_contenu]);
        $contenu_details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contenu_details) {
            header("Location: gestion_formations.php?error=contenu_not_found");
            exit;
        }

    } catch (PDOException $e) {
        $error_message = "Erreur lors du chargement des détails : " . $e->getMessage();
    }
} else {
    header("Location: gestion_formations.php?error=id_missing");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier Sous-Formation</title>
  <link rel="stylesheet" href="../../../asset/css/styles/style-formateur.css">
  <link rel="stylesheet" href="../gestion_utilisateurs/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .error-message { color: red; text-align: center; margin-bottom: 15px; }
    .success-message { color: green; text-align: center; margin-bottom: 15px; }
    .form-container { max-width: 600px; margin: 30px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
    input[type="text"], button { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button[type="submit"] { background-color: #007bff; color: white; cursor: pointer; }
    .info-box { background-color: #e9ecef; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="sidebar">...</div> 

  <div class="main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Modification</span>
                <h2>Modifier la Sous-Formation</h2>
            </div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>    
        <?php elseif (!empty($success_message)): ?>
            <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <div class="form-container">
            <div class="info-box">
                <p>Formation Principale : <strong><?= htmlspecialchars($contenu_details['nom_formation'] ?? 'N/A') ?></strong></p>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id_contenu" value="<?= htmlspecialchars($contenu_details['id_contenu'] ?? '') ?>">
                <input type="hidden" name="formation_parent_id" value="<?= htmlspecialchars($contenu_details['parent_id'] ?? '') ?>">

                <label for="nouveau_nom">Nouveau nom de la Sous-Formation :</label>
                <input type="text" id="nouveau_nom" name="nouveau_nom" 
                       value="<?= htmlspecialchars($contenu_details['sous_formation'] ?? '') ?>" required>

                <button type="submit" name="update_contenu">Enregistrer la Modification</button>
            </form>

            <a href="voir_details_formation.php?id=<?= htmlspecialchars($contenu_details['parent_id'] ?? '') ?>" class="btn-action btn-view" style="display: block; text-align: center; background-color: #6c757d;">
                Annuler et Retour
            </a>
        </div>
    </div>
</body>
</html>