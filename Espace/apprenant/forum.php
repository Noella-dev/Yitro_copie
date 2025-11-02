<?php
session_start();
require_once '../config/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../../authentification/connexion.php");
    exit();
}

$stmt = $pdo->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../../authentification/connexion.php");
    exit();
}
/*
if (!isset($_SESSION['user_id']) && !isset($_SESSION['formateur_id'])) {
    header("Location: ../../authentification/connexion.php");
    exit();
}
*/
// Récupérer le nom et l'ID de l'utilisateur ou du formateur
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, nom FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_user_id = $_SESSION['user_id'];
}/* elseif (isset($_SESSION['formateur_id'])) {
    $stmt = $pdo->prepare("SELECT id, nom_prenom AS nom FROM formateurs WHERE id = ?");
    $stmt->execute([$_SESSION['formateur_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Récupérer l'ID utilisateur correspondant au formateur
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = (SELECT email FROM formateurs WHERE id = ?)");
    $stmt->execute([$_SESSION['formateur_id']]);
    $current_user_id = $stmt->fetchColumn();
}

if (!$user || !$current_user_id) {
    header("Location: ../../authentification/connexion.php");
    exit();
}
*/
// Traitement de la création d'un nouveau forum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titre'], $_POST['cours_id'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description'] ?? '');
    $cours_id = (int)$_POST['cours_id'];
    
    $stmt = $pdo->prepare("INSERT INTO forum (cours_id, titre, description) VALUES (?, ?, ?)");
    $stmt->execute([$cours_id, $titre, $description]);
    
    header("Location: espace_apprenant.php");
    exit();
}

// Traitement de la soumission d'un post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'], $_POST['forum_id'])) {
    $contenu = trim($_POST['contenu']);
    $forum_id = (int)$_POST['forum_id'];
    $auteur_id = $current_user_id;
    
    $stmt = $pdo->prepare("INSERT INTO post (auteur_id, forum_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$auteur_id, $forum_id, $contenu]);
    
    header("Location: forum.php?forum_id=$forum_id&success=Post ajouté avec succès");
    exit();
}

// Récupérer les détails du forum
$forum_id = isset($_GET['forum_id']) ? (int)$_GET['forum_id'] : 0;
if ($forum_id) {
    $stmt = $pdo->prepare("SELECT f.*, c.titre AS cours_titre FROM forum f JOIN cours c ON f.cours_id = c.id WHERE f.id = ?");
    $stmt->execute([$forum_id]);
    $forum = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$forum) {
        header("Location: espace_apprenant.php");
        exit();
    }
    
    // Récupérer les posts avec le nom et indicateur si c'est l'utilisateur connecté
    $stmt = $pdo->prepare("
        SELECT p.*, 
               COALESCE(f.nom_prenom, u.nom) AS auteur_nom,
               CASE 
                   WHEN p.auteur_id = ? THEN 1 
                   ELSE 0 
               END AS is_self,
               CASE 
                   WHEN f.id IS NOT NULL THEN 1 
                   ELSE 0 
               END AS is_formateur
        FROM post p 
        JOIN utilisateurs u ON p.auteur_id = u.id 
        LEFT JOIN formateurs f ON u.email = f.email 
        WHERE p.forum_id = ? 
        ORDER BY p.date_post ASC
    ");
    $stmt->execute([$current_user_id, $forum_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Yitro Learning</title>
    <link rel="stylesheet" href="../../asset/css/styles.css">
    <link rel="icon" href="asset/images/Yitro_consulting.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
    <style>
        header {
            background: linear-gradient(109deg, #132F3F, #01ae8f);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .main-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 45px;
            border-radius: 50%;
            transition: transform 0.3s ease;
            height: 50px;border-radius:5px;background: wheat;
        }

        .logo img:hover {
            transform: scale(1.1);
        }

        .logo-text {
            text-decoration: none;
            font-weight: 700;
            font-size: 20px;
            color: #ffffff;
            letter-spacing: 1px;
        }

        .nav-list {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 25px;
            margin: 0;
            padding: 0;
        }

        .nav-list a {
            text-decoration: none;
            color: #e0e0e0;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .nav-list a:hover {
            color: #9b8227;
            transform: translateY(-2px);
        }

        .auth-links .nav-list {
            gap: 15px;
        }

        .auth-links .btn-primary {
            background-color: #9b8227;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auth-links .btn-primary:hover {
            background-color: #e68c32;
            transform: scale(1.05);
        }

        .main--content {
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f0 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .forum-section {
            margin-bottom: 50px;
        }

        .forum-section h3 {
            color: #2c3e50;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .forum-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.4s, box-shadow 0.4s;
        }

        .forum-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .forum-card h4 {
            color: #2c3e50;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .forum-card p {
            color: #4a5568;
            font-size: 1rem;
            margin-bottom: 12px;
        }

        .post-list {
            display: flex;
            flex-direction: column;
            gap: 25px;
            max-height: 400px;
            overflow-y: auto;
            padding: 20px 30px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .post-item {
            max-width: 70%;
            padding: 15px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .post-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
        }

        .post-apprenant {
            margin-right: auto;
            background: linear-gradient(145deg, #f1f5f9, #e6f0fa);
            color: #2c3e50;
            border-radius: 16px 4px 16px 16px;
        }

        .post-formateur {
            margin-left: auto;
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: #fff;
            border-radius: 16px 16px 4px 16px;
        }

        .post-item .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
        }

        .post-apprenant .avatar {
            background: linear-gradient(45deg, #01ae8f, #008f75);
        }

        .post-formateur .avatar {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
        }

        .post-item .author {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-apprenant .author i {
            color: #01ae8f;
        }

        .post-formateur .author i {
            color: #fff;
        }

        .post-item .date {
            font-size: 0.8rem;
            margin-bottom: 8px;
            opacity: 0.8;
        }

        .post-apprenant .date {
            color: #6b7280;
        }

        .post-formateur .date {
            color: #e6f0fa;
        }

        .post-item .content {
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-group {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.95rem;
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
            width: 100%;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
        }

        .form-control:focus {
            border-color: #01ae8f;
            box-shadow: 0 0 10px rgba(1, 174, 143, 0.3);
            transform: scale(1.01);
            outline: none;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-success {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: #fff;
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }

        .btn:active {
            transform: scale(0.95);
            transition: transform 0.1s;
        }

        .no-posts {
            color: #4a5568;
            font-size: 0.95rem;
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .success {
            padding: 12px 20px;
            border-radius: 12px;
            margin: 25px auto;
            font-size: 0.95rem;
            background: #d1fae5;
            color: #2ecc71;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 600px;
        }

        .footer {
            background: #132F3F;
            color: #e0e0e0;
            padding: 40px 20px;
        }

        .footer .container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .footer-column h4 {
            font-size: 1.2em;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
        }

        .footer-column ul li {
            margin-bottom: 10px;
        }

        .footer-column ul li a {
            color: #cbcbcb;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .footer-column ul li a:hover {
            color: #9b8227;
        }

        .footer-bottom {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #2a3f50;
            margin-top: 20px;
            font-size: 0.9em;
            color: #cbcbcb;
        }

        @media (max-width: 768px) {
            .main-nav {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }

            .nav-list {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            .auth-links .nav-list {
                width: 100%;
                justify-content: center;
            }

            .main--content {
                padding: 25px;
            }

            .forum-section h3 {
                font-size: 1.3rem;
            }

            .post-list {
                padding: 15px 20px;
            }

            .post-item {
                max-width: 80%;
            }

            .form-group {
                margin: 20px 15px;
            }

            .success {
                margin: 25px 15px;
            }

            .no-posts {
                margin: 20px 15px;
            }
        }

        @media (max-width: 480px) {
            .logo img {
                height: 35px;
            }

            .logo-text {
                font-size: 18px;
            }

            .main--content {
                padding: 15px;
            }

            .forum-section h3 {
                font-size: 1.2rem;
            }

            .post-list {
                padding: 10px 15px;
            }

            .post-item {
                max-width: 90%;
                padding: 10px;
            }

            .post-item .author {
                font-size: 0.85rem;
            }

            .post-item .content {
                font-size: 0.9rem;
            }

            .post-item .date {
                font-size: 0.75rem;
            }

            .form-group {
                margin: 20px 10px;
                padding: 12px;
            }

            .form-control {
                padding: 10px;
            }

            .btn-success {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .success {
                margin: 20px 10px;
                font-size: 0.9rem;
            }

            .no-posts {
                margin: 20px 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="logo">
                <img src="../../asset/images/logo.png" alt="Yitro E-Learning">
                <a href="espace_apprenant.php" class="logo-text">Yitro Learning</a>
            </div>
            <ul class="nav-list">
                <li><a href="espace_apprenant.php">Catalogues</a></li>
                <li><a href="mes_cours.php">Mes cours</a></li>
                <li><a href="#">Forum</a></li>

            </ul>
            <div class="auth-links">
                <ul class="nav-list">
                    <li>
                        <a href="#" class="btn-primary">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($user['nom']); ?>
                        </a>
                    </li>
                    <li><a href="../../authentification/logout.php" class="btn-primary">Déconnexion</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="main--content">
        <div class="forum-section">
            <?php if ($forum_id && $forum): ?>
                <h3>Forum</h3>
                <div class="forum-card">
                    <h4><?php echo htmlspecialchars($forum['titre']); ?></h4>
                    <p>Dans le cours : <?php echo htmlspecialchars($forum['cours_titre']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($forum['description'])); ?></p>
                    <p class="date">Créé le : <?php echo date('d/m/Y H:i', strtotime($forum['date_creation'])); ?></p>
                    <div class="post-list">
                        <?php if (empty($posts)): ?>
                            <div class="no-posts">Aucun message dans ce forum pour le moment.</div>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <div class="post-item <?php echo $post['is_self'] ? 'post-formateur' : 'post-apprenant'; ?>">
                                    <div class="author">
                                        <div class="avatar">
                                            <i class="fas <?php echo $post['is_self'] ? 'fa-user-circle' : ($post['is_formateur'] ? 'fa-user' : 'fa-user-graduate'); ?>"></i>
                                        </div>
                                        <?php echo htmlspecialchars($post['auteur_nom'] ?? 'Inconnu'); ?>
                                    </div>
                                    <div class="date"><?php echo date('d/m/Y H:i', strtotime($post['date_post'])); ?></div>
                                    <div class="content"><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <form action="forum.php" method="POST">
                            <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>">
                            <label for="contenu">Répondre au forum</label>
                            <textarea name="contenu" id="contenu" class="form-control" rows="4" required placeholder="Votre réponse..."></textarea>
                            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Publier</button>
                        </form>
                    </div>
                </div>
                <?php if (isset($_GET['success'])): ?>
                    <div class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-posts">Sélectionnez un forum depuis la page d'accueil.</div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-column">
                <h4>Suivez-nous</h4>
                <ul>
                    <li><a href="#">Suivez-nous sur les Réseaux Sociaux. Restez connecté avec SK Yitro Consulting pour les dernières mises à jour et actualités.</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Yitro Learning</h4>
                <ul>
                    <li><a href="#">Lot 304-D_240 Andafiatsimo Ambohitrinibe</a></li>
                    <li><a href="#">110 Antsirabe</a></li>
                    <li><a href="#">Lun – Ven | 08h – 12h | 14h – 18h</a></li>
                    <li><a href="#">Sam – Dim | Fermé</a></li>
                    <li><a href="#">contact@yitro-consulting.com</a></li>
                    <li><a href="#">+261 34 53 313 87</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>À propos</h4>
                <ul>
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#">Formations</a></li>
                    <li><a href="#">Certifications</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Légal</h4>
                <ul>
                    <li><a href="#">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Tous droits réservés – SK Yitro Consulting © 2024</p>
        </div>
    </footer>

    <script>
        // Animations GSAP
        gsap.from(".forum-card", { opacity: 0, y: 40, duration: 1, ease: "power3.out", delay: 0.3 });
        gsap.from(".forum-section h3", { opacity: 0, y: 20, duration: 0.8, ease: "power2.out", delay: 0.2 });
        gsap.from(".post-apprenant", { opacity: 0, x: -20, duration: 0.6, stagger: 0.1, ease: "power2.out", delay: 0.5 });
        gsap.from(".post-formateur", { opacity: 0, x: 20, duration: 0.6, stagger: 0.1, ease: "power2.out", delay: 0.5 });
        gsap.from(".form-group", { opacity: 0, y: 30, duration: 0.8, ease: "power2.out", delay: 0.7 });
        gsap.from(".btn-success", { opacity: 0, scale: 0.7, duration: 0.6, ease: "back.out(2)", delay: 0.9 });
        gsap.from(".success, .no-posts", { opacity: 0, x: -30, duration: 1, ease: "power3.out", delay: 0.3 });

        // Animation bouton survol et clic
        document.querySelectorAll(".btn").forEach(btn => {
            btn.addEventListener("mouseenter", () => {
                gsap.to(btn, { scale: 1.05, boxShadow: "0 6px 12px rgba(0,0,0,0.2)", duration: 0.3 });
            });
            btn.addEventListener("mouseleave", () => {
                gsap.to(btn, { scale: 1, boxShadow: "0 4px 10px rgba(0,0,0,0.15)", duration: 0.3 });
            });
            btn.addEventListener("click", () => {
                gsap.to(btn, { scale: 0.95, duration: 0.1, yoyo: true, repeat: 1 });
            });
        });

        // Faire défiler post-list vers le bas
        document.querySelectorAll(".post-list").forEach(postList => {
            postList.scrollTop = postList.scrollHeight;
        });

        // Défiler vers le bas après ajout d'un post (si succès)
        <?php if (isset($_GET['success'])): ?>
            document.querySelectorAll(".post-list").forEach(postList => {
                gsap.to(postList, {
                    scrollTo: { y: "max" },
                    duration: 0.5,
                    ease: "power2.out"
                });
            });
        <?php endif; ?>
    </script>
</body>
</html>

