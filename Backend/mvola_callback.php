<?php
require_once 'config.php';

// Le script doit accepter les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// Récupérer le contenu brut de la requête JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['transactionReference']) || !isset($data['status'])) {
    http_response_code(400); // Bad Request
    exit();
}

$transaction_reference = $data['transactionReference'];
$transaction_status = $data['status']; // 'completed', 'failed', 'cancelled', etc.

// Étape de sécurité : Vérification de la signature
// (Code de vérification non inclus, il dépend de la doc Mvola)
// ...

try {
    if ($transaction_status === 'completed') {
        // Le paiement a réussi
        $stmt = $pdo->prepare("UPDATE inscriptions SET statut_paiement = 'paye' WHERE reference_paiement = ? AND statut_paiement = 'en_attente'");
        $stmt->execute([$transaction_reference]);
        
        // Log ou envoi d'une notification de succès
        // ...
        
    } elseif ($transaction_status === 'failed' || $transaction_status === 'cancelled') {
        // Le paiement a échoué
        $stmt = $pdo->prepare("UPDATE inscriptions SET statut_paiement = 'echec' WHERE reference_paiement = ? AND statut_paiement = 'en_attente'");
        $stmt->execute([$transaction_reference]);

        // Log ou envoi d'une notification d'échec
        // ...
    }

    http_response_code(200); // OK
    echo "Callback traité avec succès.";

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo "Erreur de base de données : " . $e->getMessage();
}
?>