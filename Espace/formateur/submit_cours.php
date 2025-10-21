<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['formateur_id'])) {
    header("Location: ../../authentification/login.php");
    exit;
}

$formateur_id = $_SESSION['formateur_id'];
$upload_dir = '../../Uploads/cours/';
$lecon_upload_dir = '../../Uploads/lecons/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
// Types autorisés pour les leçons (PDF, MP3, MP4)
$allowed_lecon_types = ['application/pdf', 'audio/mpeg', 'video/mp4', 'video/x-mp4', 'video/mpeg'];
$max_size = 100 * 1024 * 1024; // 100MB pour un seul fichier

try {
    // Créer les dossiers d'upload s'ils n'existent pas
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($lecon_upload_dir)) {
        mkdir($lecon_upload_dir, 0755, true);
    }

    // VÉRIFICATION DES CHAMPS OBLIGATOIRES (Non-fichiers)
    if (!isset($_POST['formation_id']) || !isset($_POST['contenu_formation_id']) || !isset($_POST['titre_cours'])
        || !isset($_POST['description_cours']) || !isset($_POST['prix_cours']) || !isset($_POST['niveau_cours'])) { 
        throw new Exception("Tous les champs obligatoires du cours doivent être remplis : Thème, Sous-Thème, Niveau, Titre, Description, Prix.");
    }

    // Récupération des données du POST
    $formation_id = $_POST['formation_id']; 
    $contenu_formation_id = $_POST['contenu_formation_id'];
    $titre = $_POST['titre_cours'];
    $description = $_POST['description_cours'];
    $prix = $_POST['prix_cours'];
    $niveau = $_POST['niveau_cours']; 
    $photo = null;

    // --- 1. GESTION DE LA PHOTO DU COURS (FACULTATIVE) ---
    if (isset($_FILES['photo_cours']) && $_FILES['photo_cours']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo_cours'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        $file_error = $file['error'];

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $photo = 'course_' . time() . '.' . $extension;
            $dest = $upload_dir . $photo;

            if (!move_uploaded_file($file_tmp, $dest)) {
                throw new Exception("Échec de l'upload de la photo vers $dest.");
            }
        } else {
            // Affiche l'erreur si le fichier est présent mais non valide
            throw new Exception("Photo: Type de fichier non autorisé ($file_type) ou taille excessive ($file_size octets). Erreur code: $file_error");
        }
    }

    // --- 2. INSÉRER LE COURS ---
    $sql_insert_cours = "INSERT INTO cours 
                         (formateur_id, formation_id, contenu_formation_id, titre, description, prix, niveau, photo) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; 
                         
    $stmt = $pdo->prepare($sql_insert_cours);
    
    $stmt->execute([
        $formateur_id,
        $formation_id,     
        $contenu_formation_id, 
        $titre,
        $description,
        $prix,
        $niveau,
        $photo
    ]);
    $cours_id = $pdo->lastInsertId();

    // --- 3. GESTION DES MODULES ET LEÇONS ---
    if (isset($_POST['modules']) && is_array($_POST['modules'])) {
        foreach ($_POST['modules'] as $module_index => $module) {
            // Un module nécessite au moins un titre et une description
            if (!isset($module['titre']) || !isset($module['description'])) {
                continue;
            }

            $module_titre = $module['titre'];
            $module_description = $module['description'];

            // Insérer le module
            $stmt = $pdo->prepare("INSERT INTO modules (cours_id, titre, description) VALUES (?, ?, ?)");
            $stmt->execute([$cours_id, $module_titre, $module_description]);
            $module_id = $pdo->lastInsertId();

            // Gérer les leçons
            if (isset($module['lecons']) && is_array($module['lecons'])) {
                foreach ($module['lecons'] as $lecon_index => $lecon) {
                    
                    // Vérification de la présence des champs de la leçon (titre et format)
                    if (!isset($lecon['titre']) || !isset($lecon['format'])) {
                        continue;
                    }
                    
                    // Vérification de la présence du fichier de leçon (le champ est requis en HTML)
                    $file_key = $_FILES['modules']['name'][$module_index]['lecons'][$lecon_index]['fichier'] ?? null;
                    
                    if (!$file_key) {
                        // Si le champ n'existe pas dans $_FILES, cela signifie qu'un fichier était requis mais non soumis.
                        throw new Exception("Fichier de leçon obligatoire manquant pour: " . htmlspecialchars($lecon['titre']));
                    }

                    $lecon_file = $file_key;
                    $lecon_tmp = $_FILES['modules']['tmp_name'][$module_index]['lecons'][$lecon_index]['fichier'];
                    $lecon_size = $_FILES['modules']['size'][$module_index]['lecons'][$lecon_index]['fichier'];
                    $lecon_type = $_FILES['modules']['type'][$module_index]['lecons'][$lecon_index]['fichier'];
                    $lecon_error = $_FILES['modules']['error'][$module_index]['lecons'][$lecon_index]['fichier'];

                    // GESTION ET UPLOAD DU FICHIER DE LEÇON
                    if ($lecon_error === UPLOAD_ERR_OK && $lecon_size <= $max_size && in_array($lecon_type, $allowed_lecon_types)) {
                        $lecon_extension = pathinfo($lecon_file, PATHINFO_EXTENSION);
                        $lecon_filename = 'lecon_' . $cours_id . '_' . $module_id . '_' . time() . '_' . $lecon_index . '.' . $lecon_extension;
                        $lecon_dest = $lecon_upload_dir . $lecon_filename;

                        if (move_uploaded_file($lecon_tmp, $lecon_dest)) {
                            // Insertion de la leçon réussie
                            $stmt = $pdo->prepare("INSERT INTO lecons (module_id, titre, format, fichier) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$module_id, $lecon['titre'], $lecon['format'], $lecon_filename]);
                        } else {
                            throw new Exception("Échec de l'upload du fichier de la leçon vers $lecon_dest. Vérifiez les permissions du dossier d'upload.");
                        }
                    } else if ($lecon_error === UPLOAD_ERR_NO_FILE) {
                        // Si l'erreur est "No file", on lève une exception car le champ est REQUIRED en HTML
                        throw new Exception("Fichier de leçon obligatoire non soumis pour: " . htmlspecialchars($lecon['titre']));
                    } else {
                        // Autres erreurs (taille, type, erreur interne)
                        throw new Exception("Erreur de fichier de leçon. Code: $lecon_error. Type: $lecon_type. Taille maximale: " . ($max_size/1024/1024) . "MB");
                    }
                }
            }
            // Si le formateur a soumis un module mais a oublié toutes les leçons
            // Vous pouvez ajouter une vérification ici pour rendre les leçons obligatoires, si vous le souhaitez.
        }
    }

    // Succès total
    header("Location: liste_cours.php");
    exit;

} catch (Exception $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
    // OPTIONNEL: Ajoutez un lien de retour pour l'utilisateur
    echo '<p><a href="create_cours.php">Retourner au formulaire de création de cours</a></p>';
}
?>