<?php
require __DIR__ . '/db_connect.php';

$pdo = getDatabaseConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$errors = [];
$message = '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    $errors[] = 'Identifiant d’étudiant manquant.';
}

if ($method === 'POST' && !$errors) {
    $stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $message = 'Étudiant supprimé.';
}

if ($method === 'GET' && !$errors) {
    $stmt = $pdo->prepare('SELECT fullname, matricule FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $student = $stmt->fetch();
    if (!$student) {
        $errors[] = 'Étudiant introuvable.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Supprimer un étudiant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; list-style: none; text-decoration: none; }
        body { font-family: "Poppins", system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; overflow-x: hidden; color: #223656; }
        nav { width: 100vw; height: 70px; display: flex; justify-content: space-between; align-items: center; background-color: #1e90ff; color: #fff; padding-inline: 20px; }
        .left img { border-radius: 50%; height: 50px; width: 50px; }
        .right ul { display: flex; gap: 20px; }
        .right a { color: #fff; }
        .container { max-width: 720px; margin: 32px auto; padding: 0 20px; }
        .card { background: #fef2f2; border: 1px solid #fecaca; border-radius: 16px; padding: 32px; box-shadow: 0 10px 24px rgba(248, 113, 113, 0.2); }
        h1 { font-size: 1.75rem; color: #b91c1c; margin-bottom: 12px; }
        p { margin-top: 12px; line-height: 1.5; }
        button { background: #dc2626; color: #fff; border: none; padding: 12px 22px; border-radius: 8px; cursor: pointer; font-size: 0.95rem; margin-top: 12px; }
        button:hover { background: #991b1b; }
        form { margin-top: 24px; }
        .status { margin-top: 16px; padding: 12px 16px; border-radius: 10px; font-weight: 600; }
        .status.success { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
        .status.error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        ul.status { list-style: disc; padding-left: 20px; }
        .nav-links { margin-top: 24px; }
        .nav-links a { color: #1e90ff; font-weight: 600; }
    </style>
</head>
<body>
    <nav>
        <div class="left">
            <img src="https://scontent.faae2-3.fna.fbcdn.net/v/t39.30808-6/450160642_446636948221828_7355207663232850990_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=6ee11a&_nc_eui2=AeFSvJKo3TCcJtv0vjq7Nzj8Es_e8psi6NoSz97ymyLo2kfVK2JW4c-bb9GVbik3VqLKlGTWTpHtp4b1Sf_IGOYV&_nc_ohc=_AYGKz3aLQkQ7kNvwHqwr4g&_nc_oc=AdkVfnNVP-LRRs85_pL0-NuCRN23zD1EMAyvdcw0GAN7mzlkyN41VlA4wU4wbbHsB-s&_nc_zt=23&_nc_ht=scontent.faae2-3.fna&_nc_gid=_vhrljlaLtRS0YD923HWeg&oh=00_AfgkeT-glvpSDA_qIIHLeRmQtiayqoQ_pDcna_tQXR0MFg&oe=69250BBA" alt="Logo-Faculty">
        </div>
        <div class="right">
            <ul>
                <li><a href="home.html">Home</a></li>
                <li><a href="attendance.html">Attendance List</a></li>
                <li><a href="add_student.php">Add Student</a></li>
                <li><a href="reports.html">Reports</a></li>
                <li><a href="#">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <section class="card">
            <h1>Supprimer un étudiant</h1>

            <?php if ($message !== ''): ?>
                <p class="status success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if ($errors): ?>
                <ul class="status error">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($method === 'GET' && empty($errors)): ?>
                <p>
                    Confirmer la suppression de l’étudiant
                    <strong><?php echo htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    (matricule <?php echo htmlspecialchars($student['matricule'], ENT_QUOTES, 'UTF-8'); ?>) ?
                </p>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit">Supprimer</button>
                </form>
            <?php endif; ?>

            <div class="nav-links">
                <a href="list_students.php">Retour à la liste</a>
            </div>
        </section>
    </main>
</body>
</html>

