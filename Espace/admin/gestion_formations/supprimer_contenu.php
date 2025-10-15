<?php
session_start();
require_once '../../config/db.php'; 

// Vérifier si l'utilisateur est admin et si l'ID est présent
// if ($_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
//     header("Location: gestion_formations.php");
//     exit;
// }

$id_contenu = $_GET['id'] ?? 0;

if ($id_contenu > 0) {
    try {
        // 1. Récupérer l'ID de la formation parente pour la redirection
        $stmt_parent = $pdo->prepare("SELECT formation_id FROM contenu_formations WHERE id_contenu = ?");
        $stmt_parent->execute([$id_contenu]);
        $parent_id = $stmt_parent->fetchColumn();

        // 2. Supprimer la sous-formation
        $stmt_delete = $pdo->prepare("DELETE FROM contenu_formations WHERE id_contenu = ?");
        $stmt_delete->execute([$id_contenu]);
        
        // Journaliser l'action
        $admin_id = $_SESSION['user_id'] ?? 1;
        $details = "Suppression sous-formation ID: " . $id_contenu . " (Formation ID: " . $parent_id . ")";
        $stmt_journal = $pdo->prepare("INSERT INTO journal_activite (admin_id, action, details) VALUES (?, 'Suppression sous-formation', ?)");
        $stmt_journal->execute([$admin_id, $details]);

        // Redirection vers la page de détails de la formation parente
        if ($parent_id) {
            header("Location: voir_details_formation.php?id=" . $parent_id . "&success=deleted");
            exit;
        }

    } catch (PDOException $e) {
        // En cas d'erreur BD
        error_log("Erreur suppression contenu: " . $e->getMessage());
        header("Location: gestion_formations.php?error=db_error");
        exit;
    }
}

// Redirection par défaut en cas de problème d'ID
header("Location: gestion_formations.php");
exit;
?>