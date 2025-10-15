<?php
session_start();
require_once '../../config/db.php'; 

// --- Variables de Message ---
$error_message = '';
$success_message = '';
$formation_details = null;

$id_formation = $_GET['id'] ?? $_POST['id_formation'] ?? 0;

// ******************************************************
// TRAITEMENT DE LA MISE À JOUR (POST)
// ******************************************************
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_formation'])) {
    $nouveau_nom = trim($_POST['nouveau_nom'] ?? '');

    if ($id_formation > 0 && !empty($nouveau_nom)) {
        try {
            $stmt = $pdo->prepare("UPDATE formations SET nom_formation = ? WHERE id_formation = ?");
            
            if ($stmt->execute([$nouveau_nom, $id_formation])) {
                $success_message = "La formation principale a été modifiée avec succès.";
                
                // Journaliser l'action
                $admin_id = $_SESSION['user_id'] ?? 1;
                $details = "Modification formation principale ID: " . $id_formation . " vers: " . $nouveau_nom;
                $stmt_journal = $pdo->prepare("INSERT INTO journal_activite (admin_id, action, details) VALUES (?, 'Modification formation principale', ?)");
                $stmt_journal->execute([$admin_id, $details]);

                // Rediriger après le succès
                header("Location: gestion_formations.php?success=formation_updated");
                exit;

            } else {
                $error_message = "Erreur lors de la mise à jour de la formation.";
            }
        } catch (PDOException $e) {
             if ($e->getCode() == '23000') {
                    $error_message = "Ce nom de formation existe déjà (doublon).";
                } else {
                    $error_message = "Erreur BD: " . $e->getMessage();
                }
        }
    } else {
        $error_message = "Le nom de la formation ne peut pas être vide.";
    }
}

// ******************************************************
// CHARGEMENT INITIAL DES DONNÉES (GET)
// ******************************************************
if ($id_formation > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id_formation, nom_formation FROM formations WHERE id_formation = ?");
        $stmt->execute([$id_formation]);
        $formation_details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$formation_details) {
            header("Location: gestion_formations.php?error=formation_not_found");
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
  <title>Modifier Formation Principale</title>
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
                <h2>Modifier la Formation Principale</h2>
            </div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>    
        <?php elseif (!empty($success_message)): ?>
            <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <div class="form-container">
            <p>Vous modifiez la formation ID : <strong><?= htmlspecialchars($formation_details['id_formation'] ?? 'N/A') ?></strong></p>
            
            <form method="POST">
                <input type="hidden" name="id_formation" value="<?= htmlspecialchars($formation_details['id_formation'] ?? '') ?>">

                <label for="nouveau_nom">Nouveau nom de la Formation :</label>
                <input type="text" id="nouveau_nom" name="nouveau_nom" 
                       value="<?= htmlspecialchars($formation_details['nom_formation'] ?? '') ?>" required>

                <button type="submit" name="update_formation">Enregistrer la Modification</button>
            </form>

            <a href="gestion_formations.php" class="btn-action btn-view" style="display: block; text-align: center; background-color: #6c757d;">
                Annuler et Retour à la Gestion
            </a>
        </div>
    </div>
</body>
</html>