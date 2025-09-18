<?php
require_once '../../Backend/config.php'; 

$nouveau_mot_de_passe = "admin"; 

$email_admin = "admin@gmail.com"; 

$mot_de_passe_hache = password_hash($nouveau_mot_de_passe, PASSWORD_BCRYPT);

if ($mot_de_passe_hache === false) {
    die('Erreur lors du hachage du mot de passe.');
}

$sql = "UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erreur de préparation de la requête: " . $conn->error);
}

// Lier les variables à la requête préparée et l'exécuter
$stmt->bind_param("ss", $mot_de_passe_hache, $email_admin);

if ($stmt->execute()) {
    echo "Le mot de passe de l'administrateur a été mis à jour avec succès.";
} else {
    echo "Erreur lors de la mise à jour du mot de passe : " . $stmt->error;
}

$stmt->close();
$conn->close();

?>