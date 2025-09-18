<?php
session_start();
require_once '../config/db.php';

// Vérifier si le formateur est connecté
if (!isset($_SESSION['formateur_id'])) {
    header("Location: ../../authentification/login.php");
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

// Nombre total de cours créés
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_cours FROM cours WHERE formateur_id = ?");
$stmt->execute([$formateur_id]);
$total_cours = $stmt->fetch(PDO::FETCH_ASSOC)['total_cours'];

// Données des ventes
$stmt = $pdo->prepare("
    SELECT c.titre, c.prix, COUNT(i.id) AS inscriptions, SUM(c.prix) AS revenu
    FROM cours c
    LEFT JOIN inscriptions i ON c.id = i.cours_id AND i.statut_paiement = 'paye'
    WHERE c.formateur_id = ?
    GROUP BY c.id, c.titre, c.prix
");
$stmt->execute([$formateur_id]);
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Progression des apprenants
$progression = [];
$stmt = $pdo->prepare("
    SELECT c.id, c.titre, u.id AS utilisateur_id, u.nom AS utilisateur_nom,
           COUNT(DISTINCT m.id) AS total_modules,
           COUNT(DISTINCT comp.id) AS modules_termines
    FROM cours c
    LEFT JOIN inscriptions i ON c.id = i.cours_id AND i.statut_paiement = 'paye'
    LEFT JOIN utilisateurs u ON i.utilisateur_id = u.id
    LEFT JOIN modules m ON m.cours_id = c.id
    LEFT JOIN completions comp ON comp.module_id = m.id AND comp.utilisateur_id = u.id
    WHERE c.formateur_id = ?
    GROUP BY c.id, c.titre, u.id, u.nom
    HAVING u.id IS NOT NULL
    ORDER BY c.id, u.nom
");
$stmt->execute([$formateur_id]);
$apprenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($apprenants as $a) {
    $progression[$a['id']]['titre'] = $a['titre'];
    $progression[$a['id']]['apprenants'][$a['utilisateur_id']] = [
        'nom' => $a['utilisateur_nom'],
        'progression' => $a['total_modules'] > 0 ? ($a['modules_termines'] / $a['total_modules']) * 100 : 0
    ];
}

// Notifications des messages du forum
$stmt = $pdo->prepare("
    SELECT fm.id, fm.message, fm.date, u.nom AS utilisateur_nom, c.titre AS cours_titre
    FROM forum_messages fm
    JOIN utilisateurs u ON fm.utilisateur_id = u.id
    JOIN cours c ON fm.cours_id = c.id
    WHERE c.formateur_id = ? AND fm.lu = FALSE
    ORDER BY fm.date DESC
    LIMIT 5
");
$stmt->execute([$formateur_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yitro Learning - Tableau de bord formateur</title>
    <link rel="stylesheet" href="../../asset/css/styles/style-formateur.css">
    <link rel="stylesheet" href="../../asset/css/styles/espace-formateur.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo"></div>
        <ul class="menu">
            <li class="active">
                <a href="#"><i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span></a>
            </li>
            <li>
                <a href="create_cours.php"><i class="fas fa-user-cog"></i><span>Créer un cours</span></a>
            </li>
            <li>
                <a href="liste_cours.php"><i class="fas fa-folder-open"></i><span>Mes cours</span></a>
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
                <h2>Tableau de bord</h2>
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

        <!-- Nombre de cours créés -->
        <div class="dashboard-section">
            <div class="stats-card">
                <i class="fas fa-book"></i>
                <div>
                    <h4>Nombre de cours créés</h4>
                    <span><?php echo $total_cours; ?></span>
                </div>
            </div>
        </div>

        <!-- Tableau des ventes -->
        <div class="dashboard-section">
            <h3>Ventes des cours</h3>
            <?php if (empty($ventes)): ?>
                <div class="card">
                    <p>Aucun cours vendu pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Cours</th>
                                <th>Inscriptions</th>
                                <th>Revenu (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventes as $vente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vente['titre']); ?></td>
                                    <td><?php echo $vente['inscriptions']; ?></td>
                                    <td><?php echo number_format($vente['revenu'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Progression des apprenants -->
        <div class="dashboard-section">
            <h3>Progression des apprenants</h3>
            <?php if (empty($progression)): ?>
                <div class="card">
                    <p>Aucun apprenant inscrit à vos cours.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="progression-list">
                        <?php foreach ($progression as $cours_id => $data): ?>
                            <h4><?php echo htmlspecialchars($data['titre']); ?></h4>
                            <?php foreach ($data['apprenants'] as $apprenant): ?>
                                <div class="progression-item">
                                    <h5><?php echo htmlspecialchars($apprenant['nom']); ?></h5>
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill" style="width: <?php echo $apprenant['progression']; ?>%;"></div>
                                    </div>
                                    <div class="progression-text"><?php echo number_format($apprenant['progression'], 1); ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                    <a href="progression_apprenants.php" class="btn btn-primary">Voir toutes les progressions</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notifications du forum -->
        <div class="dashboard-section">
            <h3>Notifications du forum</h3>
            <?php if (empty($notifications)): ?>
                <div class="card">
                    <p>Aucun message non lu dans les forums.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notification-item">
                                <span><strong><?php echo htmlspecialchars($notif['utilisateur_nom']); ?></strong> dans <strong><?php echo htmlspecialchars($notif['cours_titre']); ?></strong>: <?php echo htmlspecialchars(substr($notif['message'], 0, 50)); ?>...</span>
                                <span class="date"><?php echo date('d/m/Y H:i', strtotime($notif['date'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Animations GSAP
        gsap.from(".header--wrapper", { opacity: 0, y: -50, duration: 1, ease: "power3.out" });
        gsap.from(".stats-card", { opacity: 0, scale: 0.8, duration: 0.8, ease: "back.out(1.7)" });
        gsap.from(".card", { opacity: 0, y: 30, duration: 0.8, stagger: 0.2, ease: "power2.out", delay: 0.2 });
        gsap.from(".progress-bar-fill", { width: 0, duration: 1, ease: "power2.out", stagger: 0.1, delay: 0.5 });
        gsap.from(".notification-item", { opacity: 0, x: -20, duration: 0.6, stagger: 0.1, ease: "power2.out", delay: 0.7 });
    </script>
</body>
</html>