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
    $values = [
        'fullname' => trim($_POST['fullname'] ?? ''),
        'matricule' => trim($_POST['matricule'] ?? ''),
        'group_id' => trim($_POST['group_id'] ?? ''),
    ];

    if ($values['fullname'] === '' || !preg_match('/^[\p{L}\'\- ]+$/u', $values['fullname'])) {
        $errors[] = 'Nom complet invalide.';
    }
    if ($values['matricule'] === '') {
        $errors[] = 'Le matricule est obligatoire.';
    }
    if ($values['group_id'] === '' || !preg_match('/^[A-Za-z0-9_\- ]+$/', $values['group_id'])) {
        $errors[] = 'Le groupe est invalide.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE students SET fullname = :fullname, matricule = :matricule, group_id = :group_id WHERE id = :id');
        $stmt->execute([
            ':fullname' => $values['fullname'],
            ':matricule' => $values['matricule'],
            ':group_id' => $values['group_id'],
            ':id' => $id,
        ]);
        $message = 'Étudiant mis à jour.';
    }
} elseif ($method === 'GET' && !$errors) {
    $stmt = $pdo->prepare('SELECT fullname, matricule, group_id FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $values = $stmt->fetch();
    if (!$values) {
        $errors[] = 'Étudiant introuvable.';
    }
}

if (!isset($values)) {
    $values = ['fullname' => '', 'matricule' => '', 'group_id' => ''];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier un étudiant</title>
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
        .container { max-width: 820px; margin: 32px auto; padding: 0 20px; }
        .card { background: #f8fafc; border: 1px solid #d0d7de; border-radius: 16px; padding: 32px; box-shadow: 0 10px 24px rgba(30, 144, 255, 0.08); }
        h1 { font-size: 1.75rem; color: #1e90ff; margin-bottom: 12px; }
        form { margin-top: 16px; display: grid; gap: 16px; }
        label { font-size: 0.95rem; font-weight: 600; color: #1b2a4a; }
        input { width: 100%; padding: 12px; border: 1px solid #b6c0cf; border-radius: 8px; font-size: 0.95rem; }
        button { width: fit-content; background: #1e90ff; color: #fff; border: none; padding: 12px 22px; border-radius: 8px; cursor: pointer; font-size: 0.95rem; }
        button:hover { background: #0f60a6; }
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
            <h1>Modifier un étudiant</h1>

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

            <?php if (!$errors || $method === 'POST'): ?>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
                    <label>
                        Nom complet
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($values['fullname'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <label>
                        Matricule
                        <input type="text" name="matricule" value="<?php echo htmlspecialchars($values['matricule'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <label>
                        Groupe
                        <input type="text" name="group_id" value="<?php echo htmlspecialchars($values['group_id'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <button type="submit">Mettre à jour</button>
                </form>
            <?php endif; ?>

            <div class="nav-links">
                <a href="list_students.php">Retour à la liste</a>
            </div>
        </section>
    </main>
</body>
</html>

