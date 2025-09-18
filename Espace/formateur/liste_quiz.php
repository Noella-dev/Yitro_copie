<?php
session_start();
require_once '../config/db.php';

// Vérifier si le formateur est connecté
if (!isset($_SESSION['formateur_id'])) {
    header("Location: ../../authentification/connexion.php");
    exit;
}

$formateur_id = $_SESSION['formateur_id'];

// Récupérer le nom du formateur
$trainer_name = "Formateur";
if (isset($pdo)) {
    try {
        $query = "SELECT nom_prenom FROM formateurs WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $formateur_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $trainer_name = htmlspecialchars($row['nom_prenom']);
        }
    } catch (PDOException $e) {
        error_log("Erreur de requête : " . $e->getMessage());
    }
}

// Récupérer les quiz
$stmt = $pdo->prepare("
    SELECT q.id, q.titre, q.description, q.score_minimum, c.titre AS cours_titre, m.titre AS module_titre,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS nb_questions
    FROM quiz q
    JOIN modules m ON q.module_id = m.id
    JOIN cours c ON m.cours_id = c.id
    WHERE c.formateur_id = ?
");
$stmt->execute([$formateur_id]);
$quiz = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Supprimer un quiz
if (isset($_GET['delete'])) {
    $quiz_id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM quiz WHERE id = ?");
        $stmt->execute([$quiz_id]);
        header("Location: liste_quiz.php?success=Quiz supprimé avec succès");
        exit;
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yitro Learning - Gestion des quiz</title>
    <link rel="stylesheet" href="../../asset/css/styles/style-formateur.css">
    <link rel="stylesheet" href="../../asset/css/quiz.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            <li>
                <a href="liste_cours.php"><i class="fas fa-folder-open"></i><span>Mes cours</span></a>
            </li>
            <li>
                <a href="progression_apprenants.php"><i class="fas fa-chart-line"></i><span>Progression des apprenants</span></a>
            </li>
            <li class="active">
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
                <h2>Gestion des quiz</h2>
            </div>
            <div class="user--info">
                <div class="search--box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
                <i class="fas fa-users"></i>
                <span class="trainer-name"><?php echo $trainer_name; ?></span>
            </div>
        </div>
        <h1>Mes quiz</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($quiz)): ?>
            <div class="no-quiz">
                Aucun quiz disponible. Créez votre premier quiz dès maintenant !
                <br>
                <a href="create_quiz.php" class="btn btn-primary mt-3">Créer un quiz</a>
            </div>
        <?php else: ?>
            <?php foreach ($quiz as $q): ?>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($q['titre']); ?></h4>
                        <p class="card-text">
                            <strong>Cours :</strong> <?php echo htmlspecialchars($q['cours_titre']); ?><br>
                            <strong>Module :</strong> <?php echo htmlspecialchars($q['module_titre']); ?><br>
                            <strong>Nombre de questions :</strong> <?php echo $q['nb_questions']; ?><br>
                            <strong>Score minimum :</strong> <?php echo $q['score_minimum']; ?>%
                        </p>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($q['description'])); ?></p>
                        <a href="edit_quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-primary">Modifier</a>
                        <a href="liste_quiz.php?delete=<?php echo $q['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce quiz ?')">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="create_quiz.php" class="btn btn-primary">Créer un nouveau quiz</a>
        <?php endif; ?>
    </div>

    <script>
        gsap.from(".main--content", { opacity: 0, y: 50, duration: 1, ease: "power3.out" });
        gsap.from(".card, .no-quiz", { opacity: 0, y: 30, duration: 0.8, stagger: 0.1, ease: "power2.out", delay: 0.2 });
        gsap.from(".btn", { opacity: 0, scale: 0.8, duration: 0.5, stagger: 0.1, ease: "back.out(1.7)", delay: 0.5 });
    </script>
</body>
</html>