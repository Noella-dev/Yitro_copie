<?php
// On inclut le fichier de configuration de la base de données
require_once 'config.php';
session_start();

// Le script doit retourner une réponse JSON
header('Content-Type: application/json');

// Vérifier si la requête est de type POST et si l'utilisateur est connecté
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Non autorisé ou méthode de requête invalide.']);
    exit();
}

// Récupérer les données envoyées par le formulaire
$utilisateur_id = $_SESSION['user_id'];
$cours_id = $_POST['cours_id'] ?? null;
$prix_cours = $_POST['prix_cours'] ?? null;
$mvola_number = $_POST['mvola_number'] ?? null;

// Validation des données
if (empty($mvola_number) || empty($cours_id) || empty($prix_cours)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données de paiement manquantes.']);
    exit();
}

try {
    // 1. Vérifier si l'utilisateur est déjà inscrit ou si une transaction est déjà en cours
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE utilisateur_id = ? AND cours_id = ? AND statut_paiement IN ('paye', 'en_attente')");
    $stmt->execute([$utilisateur_id, $cours_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Vous êtes déjà inscrit à ce cours ou une transaction est en cours.']);
        exit();
    }
    
    // 2. Étape CRITIQUE : Créer la transaction "en attente" dans la base de données.
    // Cette étape est essentielle pour que le callback de Mvola puisse identifier la transaction.
    $stmt = $pdo->prepare("INSERT INTO inscriptions (utilisateur_id, cours_id, date_inscription, statut_paiement) VALUES (?, ?, NOW(), 'en_attente')");
    $stmt->execute([$utilisateur_id, $cours_id]);
    $inscription_id = $pdo->lastInsertId(); // Récupère l'ID de la nouvelle inscription

    // Simulation de l'appel à l'API Mvola
    // Remplacez cette section par votre VRAI code d'appel à l'API Mvola
    // avec vos identifiants du sandbox.
    
    $transaction_reference = uniqid('mvola_'); // Une référence unique pour la transaction

    // Données de la transaction pour l'API Mvola
    $api_call_success = true; // Simule un appel réussi
    // ... Le code cURL réel pour l'appel à l'API Mvola ...

    if ($api_call_success) {
        // Le paiement a été initié avec succès.
        // On met à jour l'inscription pour lier la référence de Mvola
        $stmt = $pdo->prepare("UPDATE inscriptions SET reference_paiement = ? WHERE id = ?");
        $stmt->execute([$transaction_reference, $inscription_id]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Transaction initiée. Veuillez valider le paiement sur votre téléphone.',
        ]);
    } else {
        // Erreur de l'API Mvola
        // Si l'API renvoie une erreur, il faut annuler l'inscription "en attente"
        $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id = ?");
        $stmt->execute([$inscription_id]);

        echo json_encode([
            'status' => 'error',
            'message' => 'Erreur lors de l\'initialisation du paiement.',
        ]);
    }
    
} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>