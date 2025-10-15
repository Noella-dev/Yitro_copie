<?php
session_start();
require_once '../../config/db.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../authentification/connexion-admin.php");
    exit();
}

$id_formation = $_GET['id'] ?? 0;

if ($id_formation > 0) {
    try {
        // 1. Récupérer le nom pour la journalisation
        $stmt_name = $pdo->prepare("SELECT nom_formation FROM formations WHERE id_formation = ?");
        $stmt_name->execute([$id_formation]);
        $nom_formation = $stmt_name->fetchColumn();

        // 2. Supprimer la formation principale
        // L'option ON DELETE CASCADE dans la BDD s'occupera de supprimer
        // toutes les entrées liées dans la table 'contenu_formations'.
        $stmt_delete = $pdo->prepare("DELETE FROM formations WHERE id_formation = ?");
        $stmt_delete->execute([$id_formation]);
        
        // Journaliser l'action
        $admin_id = $_SESSION['user_id'] ?? 1;
        $details = "Formation principale et contenu associés supprimés : " . htmlspecialchars($nom_formation) . " (ID: " . $id_formation . ")";
        $stmt_journal = $pdo->prepare("INSERT INTO journal_activite (admin_id, action, details) VALUES (?, 'Suppression formation principale', ?)");
        $stmt_journal->execute([$admin_id, $details]);

        // Redirection avec message de succès
        header("Location: gestion_formations.php?success=formation_deleted");
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur BD
        error_log("Erreur suppression formation principale: " . $e->getMessage());
        header("Location: gestion_formations.php?error=db_error_deletion");
        exit;
    }
}

// Redirection par défaut si l'ID est manquant
header("Location: gestion_formations.php?error=id_missing");
exit;
?>