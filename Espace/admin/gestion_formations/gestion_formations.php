<?php
session_start();

require_once '../../config/db.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../authentification/connexion-admin.php");
    exit();
}
// --- Variables de Message ---
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_SESSION['user_id'] ?? 1; 
        //formation
    if (isset($_POST['ajouter_formation'])) {
        $nom_formation = trim($_POST['nom_formation'] ?? '');

        if (!empty($nom_formation)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO formations (nom_formation) VALUES (:nom_formation)");
                $stmt->bindParam(':nom_formation', $nom_formation);

                if ($stmt->execute()) {
                    $success_message = "Formation Principale ('" . htmlspecialchars($nom_formation) . "') ajoutée avec succès !";
                    // Journaliser l'action (en supposant que vous ayez une table journal_activite)
                    $details = "Formation ajoutée: " . $nom_formation;
                    $stmt_journal = $pdo->prepare("INSERT INTO journal_activite (admin_id, action, details) VALUES (?, 'Ajout formation', ?)");
                    $stmt_journal->execute([$admin_id, $details]);
                } else {
                    $error_message = "Erreur lors de l'ajout de la formation.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    $error_message = "Cette formation existe déjà (doublon).";
                } else {
                    $error_message = "Erreur BD: " . $e->getMessage();
                }
            }
        } else {
            $error_message = "Le nom de la formation ne peut pas être vide.";
        }
    }
        //sous_formation
    elseif (isset($_POST['ajouter_sous_formation'])) {
        $formation_id = $_POST['formation_parent_id'] ?? 0;
        $sous_formation_nom = trim($_POST['sous_formation'] ?? '');

        if ($formation_id > 0 && !empty($sous_formation_nom)) {
            try {
                // Préparation de la requête pour l'insertion
                $stmt = $pdo->prepare("INSERT INTO contenu_formations (formation_id, sous_formation) VALUES (:id, :nom)");
                $stmt->bindParam(':id', $formation_id, PDO::PARAM_INT);
                $stmt->bindParam(':nom', $sous_formation_nom);

                if ($stmt->execute()) {
                    $success_message = "Sous-Formation ('" . htmlspecialchars($sous_formation_nom) . "') ajoutée avec succès !";
                    // Journaliser l'action
                    $details = "Sous-formation ajoutée: " . $sous_formation_nom . " (Formation ID: " . $formation_id . ")";
                    $stmt_journal = $pdo->prepare("INSERT INTO journal_activite (admin_id, action, details) VALUES (?, 'Ajout sous-formation', ?)");
                    $stmt_journal->execute([$admin_id, $details]);
                } else {
                    $error_message = "Erreur lors de l'ajout de la sous-formation.";
                }

            } catch (PDOException $e) {
                $error_message = "Erreur BD: Impossible d'ajouter la sous-formation. " . $e->getMessage();
            }
        } else {
            $error_message = "Erreur : La formation parent et le nom de la sous-formation sont requis.";
        }
    }
}

//la liste des Formations principales (pour les listes déroulantes et l'affichage)
$formations = [];
try {
    $stmt = $pdo->query("SELECT id_formation, nom_formation FROM formations ORDER BY nom_formation ASC");
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message .= " Erreur de chargement des formations : " . $e->getMessage();
}

// sous-Formations pour l'affichage (avec le nom de la formation)
$sous_formations = [];
try {
    $sql_contenu = "SELECT cf.id_contenu, cf.sous_formation, f.nom_formation 
                    FROM contenu_formations cf
                    JOIN formations f ON cf.formation_id = f.id_formation
                    ORDER BY f.nom_formation, cf.sous_formation";
    $stmt_contenu = $pdo->query($sql_contenu);
    $sous_formations = $stmt_contenu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message .= " Erreur de chargement du contenu : " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yitro Learning - Gestion des formations</title>
    <link rel="stylesheet" href="../../../asset/css/styles/style-formateur.css">
    <link rel="stylesheet" href="../gestion_utilisateurs/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <style>
    /* Styles spécifiques pour cette page */
    .error-message { color: red; text-align: center; margin-bottom: 15px; }
    .success-message { color: green; text-align: center; margin-bottom: 15px; }
    .forms--container {
        display: flex;
        gap: 30px;
        margin-bottom: 30px;
        flex-wrap: wrap; /* Pour la réactivité */
    }
    .form-section {
        flex: 1;
        min-width: 300px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    .form-section h3 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    input[type="text"], select, button {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    button[type="submit"] {
        background-color: #01ae8f;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    button[type="submit"]:hover {
        background-color: #01ae8f;
    }
    .table--wrapper table {
        width: 100%;
        border-collapse: collapse;
    }
    .table--wrapper th, .table--wrapper td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    /* Ajoutez des styles pour les boutons d'action */
    .btn-action.btn-edit { background-color: #ffc107; }
    .btn-action.btn-delete { background-color: #dc3545; }
    .btn-action { color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; margin-right: 5px; }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo">
        <img src="../../../asset/images/other_logo.png" alt="Yitro E-Learning" style="height: 50px;position:relative;left:-18px;">
    </div>
    <ul class="menu" style="margin-top:-14px">
      <li>
        <a href="../backoffice.php"><i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span></a>
      </li>
      <li>
        <a href="../gestion_utilisateurs/gestion_utilisateur.php"><i class="fas fa-user-cog"></i><span>Gestion utilisateur</span></a>
      </li>
      <li class="active">
        <a href="#"><i class="fas fa-chart-line"></i><span>Gestion formations</span></a>
      </li>
      <li>
        <a href="../gestion_forum.php"><i class="fas fa-comments"></i><span>Forum</span></a>
      </li>
      <li>
        <a href="../progression_apprenant.php"><i class="fas fa-chart-line"></i><span>Progression Apprenant</span></a>
      </li>
      <li>
        <a href="../espace-certificat.php"><i class="fas fa-certificate"></i><span>Certificat Apprenant</span></a>
     </li>
      <li class="logout">
        <a href="../../../authentification/logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
      </li>
    </ul>
  </div>
  <div class="main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Gestion</span>
                <h2>Gestion des Formations</h2>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>    
        <?php elseif (!empty($success_message)): ?>
            <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        
        <div class="forms--container">
            
            <div class="form-section gest--container">
                <h3>Ajouter une Formation Principale</h3>
                <form method="POST">
                    <label for="nom_formation">Nom de la Formation :</label>
                    <input type="text" id="nom_formation" name="nom_formation" required>
                    
                    <button type="submit" name="ajouter_formation">Ajouter la Formation</button>
                </form>
            </div>

            <div class="form-section gest--container">
                <h3>Ajouter une Sous-Formation</h3>
                <form method="POST">
                    <label for="formation_parent_id">Sélectionner la Formation :</label>
                    
                    <select id="formation_parent_id" name="formation_parent_id" required>
                        <option value="">-- Choisir une formation --</option>
                        <?php 
                            // Affichage des formations chargées en amont
                            if (!empty($formations)) {
                                foreach($formations as $formation) {
                                    echo '<option value="' . htmlspecialchars($formation["id_formation"]) . '">' 
                                         . htmlspecialchars($formation["nom_formation"]) . 
                                         '</option>';
                                }
                            } else {
                                echo '<option value="" disabled>Aucune formation trouvée.</option>';
                            }
                        ?>
                    </select>
                    
                    <label for="sous_formation">Nom de la Sous-Formation :</label>
                    <input type="text" id="sous_formation" name="sous_formation" required>

                    <button type="submit" name="ajouter_sous_formation">Ajouter la Sous-Formation</button>
                </form>
            </div>
        </div>

        <div class="gest--container">
            <h2 class="gest--title">Liste des Formations Principales</h2>
            <div class="table--wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Formation</th>
                            <th>Nom de la Formation</th>
                            <th>Nombre de Sous-Formations</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="gest-list-formations">
                        <?php 
                        if (!empty($formations)): 
                            foreach ($formations as $formation): 
                                // Compter le nombre de sous-formations pour l'affichage
                                $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM contenu_formations WHERE formation_id = ?");
                                $count_stmt->execute([$formation['id_formation']]);
                                $count = $count_stmt->fetchColumn();
                        ?>
                                <tr class="table--row">
                                    <td><?= $formation['id_formation'] ?></td>
                                    <td><?= htmlspecialchars($formation['nom_formation']) ?></td>
                                    <td><?= $count ?></td>
                                    <td>
                                        <a href="modifier_formation.php?id=<?= $formation['id_formation'] ?>" class="btn-action btn-edit">Modifier</a>
                                    
                                        <a href="voir_details_formation.php?id=<?= $formation['id_formation'] ?>" class="btn-action btn-view">Voir Toutes</a>
                                        
                                        <a href="supprimer_formation.php?id=<?= $formation['id_formation'] ?>" class="btn-action btn-delete" onclick="return confirm('Supprimer la formation <?= htmlspecialchars($formation['nom_formation']) ?> et TOUTES ses sous-formations ?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Aucune formation principale n'a été ajoutée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Animations GSAP pour les conteneurs
        gsap.from(".forms--container, .gest--container", {
            opacity: 0,
            y: 50,
            duration: 1,
            stagger: 0.2,
            ease: "power3.out"
        });
    </script>
</body>
</html>