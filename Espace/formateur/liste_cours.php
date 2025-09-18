<?php
session_start();
require_once '../config/db.php';

// Vérifier si le formateur est connecté
if (!isset($_SESSION['formateur_id'])) {
    header("Location: ../../authentification/logout.php");
    exit;
}

$formateur_id = $_SESSION['formateur_id'];

// Récupérer le nom du formateur
$trainer_name = "Formateur";
try {
    $stmt = $pdo->prepare("SELECT nom_prenom FROM formateurs WHERE id = ?");
    $stmt->execute([$formateur_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $trainer_name = htmlspecialchars($row['nom_prenom']);
    }
} catch (PDOException $e) {
    error_log("Erreur de requête : " . $e->getMessage());
}

// Récupérer les cours du formateur
$stmt = $pdo->prepare("SELECT id, titre, description, prix, photo FROM cours WHERE formateur_id = ?");
$stmt->execute([$formateur_id]);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les modules, leçons et forums pour chaque cours
$modules = [];
$lecons = [];
$forums = [];
foreach ($cours as $c) {
    // Modules
    $stmt = $pdo->prepare("SELECT id, titre, description FROM modules WHERE cours_id = ?");
    $stmt->execute([$c['id']]);
    $modules[$c['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Leçons
    foreach ($modules[$c['id']] as $module) {
        $stmt = $pdo->prepare("SELECT id, titre, format, fichier FROM lecons WHERE module_id = ?");
        $stmt->execute([$module['id']]);
        $lecons[$module['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Forums
    $stmt = $pdo->prepare("SELECT id, titre FROM forum WHERE cours_id = ?");
    $stmt->execute([$c['id']]);
    $forums[$c['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yitro Learning - Mes cours</title>
    <link rel="stylesheet" href="../../asset/css/styles/style-formateur.css">
    <link rel="stylesheet" href="../../asset/css/styles/liste_cours.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo"></div>
        <ul class="menu">
            <li>
                <a href="espace_formateur.php"><i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span></a>
            </li>
            <li>
                <a href="create_cours.php"><i class="fas fa-user-cog"></i><span>Créer un cours</span></a>
            </li>
            <li class="active">
                <a href="#"><i class="fas fa-folder-open"></i><span>Mes cours</span></a>
            </li>
            <li>
                <a href="progression_apprenants.php"><i class="fas fa-chart-line"></i><span>Progression des apprenants</span></a>
            </li>
            <li>
                <a href="liste_quiz.php"><i class="fas fa-question-circle"></i><span>Gestion des quiz</span></a>
            </li>
            <li class="logout">
                <a href="../../authentification/logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
            </li>
        </ul>
    </div>
    <div class="main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Primary</span>
                <h2>Mes cours</h2>
            </div>
            <div class="user--info">
                <div class="search--box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                <i class="fas fa-user-circle"></i>
                <span class="trainer-name"><?php echo $trainer_name; ?></span>
            </div>
        </div>

        <div class="course-section">
            <h3>Mes cours</h3>
            <?php if (empty($cours)): ?>
                <div class="no-courses">
                    Aucun cours créé pour le moment. <a href="create_cours.php">Créer un cours</a>.
                </div>
            <?php else: ?>
                <?php foreach ($cours as $c): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <img src="<?php echo htmlspecialchars($c['photo'] && file_exists(__DIR__ . '/../../Uploads/cours/' . $c['photo']) ? '../../Uploads/cours/' . $c['photo'] : '../../asset/images/default_course.jpg'); ?>" alt="Photo du cours" class="course-image">
                            <div class="course-info">
                                <h4><?php echo htmlspecialchars($c['titre']); ?> <span class="forum-badge"><?php echo count($forums[$c['id']]); ?> Forum(s)</span></h4>
                                <p><?php echo htmlspecialchars(substr($c['description'], 0, 100)) . (strlen($c['description']) > 100 ? '...' : ''); ?></p>
                                <p class="price"><?php echo number_format($c['prix'], 2); ?> €</p>
                                <div class="course-actions">
                                    <a href="edit_cours.php?id=<?php echo $c['id']; ?>" class="btn btn-success"><i class="fas fa-edit"></i> Modifier</a>
                                    <a href="delete_cours.php?id=<?php echo $c['id']; ?>" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce cours ?');"><i class="fas fa-trash"></i> Supprimer</a>
                                    <a href="forum_cours.php?id=<?php echo $c['id']; ?>" class="btn btn-info"><i class="fas fa-comments"></i> Accéder au forum</a>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($modules[$c['id']])): ?>
                            <div class="module-list">
                                <?php foreach ($modules[$c['id']] as $module): ?>
                                    <div class="module-item">
                                        <h5><?php echo htmlspecialchars($module['titre']); ?></h5>
                                        <p><?php echo htmlspecialchars(substr($module['description'], 0, 80)) . (strlen($module['description']) > 80 ? '...' : ''); ?></p>
                                        <div class="course-actions">
                                            <a href="edit_module.php?id=<?php echo $module['id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Modifier</a>
                                            <a href="delete_module.php?id=<?php echo $module['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce module ?');"><i class="fas fa-trash"></i> Supprimer</a>
                                        </div>
                                        <?php if (!empty($lecons[$module['id']])): ?>
                                            <div class="lesson-list">
                                                <?php foreach ($lecons[$module['id']] as $lecon): ?>
                                                    <div class="lesson-item">
                                                        <div class="lesson-info">
                                                            <i class="fas <?php
                                                                echo $lecon['format'] === 'pdf' ? 'fa-file-pdf' :
                                                                    ($lecon['format'] === 'audio' ? 'fa-file-audio' : 'fa-file-video');
                                                            ?>"></i>
                                                            <span><?php echo htmlspecialchars($lecon['titre']) . ' (' . $lecon['format'] . ')'; ?></span>
                                                            <?php if ($lecon['fichier'] && file_exists(__DIR__ . '/../../Uploads/lecons/' . $lecon['fichier'])): ?>
                                                                <a href="../../Uploads/lecons/<?php echo htmlspecialchars($lecon['fichier']); ?>" class="file-link" target="_blank"><?php echo htmlspecialchars($lecon['fichier']); ?></a>
                                                            <?php else: ?>
                                                                <span class="file-missing">Fichier non trouvé</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="course-actions">
                                                            <?php if ($lecon['fichier'] && file_exists(__DIR__ . '/../../Uploads/lecons/' . $lecon['fichier'])): ?>
                                                                <a href="../../Uploads/lecons/<?php echo htmlspecialchars($lecon['fichier']); ?>" class="btn btn-info btn-sm" target="_blank"><i class="fas fa-eye"></i> Voir</a>
                                                            <?php endif; ?>
                                                            <a href="edit_lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Modifier</a>
                                                            <a href="delete_lecon.php?id=<?php echo $lecon['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer cette leçon ?');"><i class="fas fa-trash"></i> Supprimer</a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Animations GSAP
        gsap.from(".header--wrapper", { opacity: 0, y: -60, duration: 1.2, ease: "elastic.out(1, 0.5)" });
        gsap.from(".course-card", { opacity: 0, y: 40, rotationX: 10, duration: 1, stagger: 0.2, ease: "power3.out", delay: 0.3 });
        gsap.from(".module-item", { opacity: 0, y: 30, duration: 0.8, stagger: 0.1, ease: "power2.out", delay: 0.5 });
        gsap.from(".lesson-item", { opacity: 0, x: 30, rotationX: 5, duration: 0.6, stagger: 0.1, ease: "power2.out", delay: 0.7 });
        gsap.from(".btn", { opacity: 0, scale: 0.7, duration: 0.6, stagger: 0.1, ease: "back.out(2)", delay: 0.9 });
        gsap.from(".no-courses", { opacity: 0, y: 30, duration: 1, ease: "power3.out", delay: 0.3 });
        // Animation bouton survol
        document.querySelectorAll(".btn").forEach(btn => {
            btn.addEventListener("mouseenter", () => {
                gsap.to(btn, { scale: 1.05, boxShadow: "0 6px 12px rgba(0,0,0,0.2)", duration: 0.3 });
            });
            btn.addEventListener("mouseleave", () => {
                gsap.to(btn, { scale: 1, boxShadow: "0 4px 10px rgba(0,0,0,0.15)", duration: 0.3 });
            });
        });
    </script>
</body>
</html>