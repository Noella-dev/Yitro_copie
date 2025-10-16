<?php

header('Content-Type: application/json');
session_start();
 
require_once '../config/db.php';

$formation_id = $_GET['formation_id'] ?? null;

$sous_formations = [];

if ($formation_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id_contenu, sous_formation 
                                FROM contenu_formations 
                                WHERE formation_id = ? 
                                ORDER BY sous_formation ASC");
        $stmt->execute([$formation_id]);
        $sous_formations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // En cas d'erreur BD, renvoyer un tableau vide
        error_log("Erreur de chargement AJAX : " . $e->getMessage());
    }
}

    echo json_encode($sous_formations);
?>