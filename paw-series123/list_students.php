<?php
require __DIR__ . '/db_connect.php';

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query('SELECT id, fullname, matricule, group_id FROM students ORDER BY id DESC');
    $students = $stmt->fetchAll();
} catch (Throwable $e) {
    $students = [];
    $error = 'Impossible de charger les étudiants : ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste des étudiants</title>
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
        .container { max-width: 1050px; margin: 32px auto; padding: 0 20px; }
        .card { background: #f8fafc; border: 1px solid #d0d7de; border-radius: 16px; padding: 32px; box-shadow: 0 10px 24px rgba(30, 144, 255, 0.08); }
        h1 { font-size: 1.75rem; color: #1e90ff; margin-bottom: 12px; }
        .actions a { margin-right: 12px; color: #1e90ff; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #e0f2ff; color: #1e90ff; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.8px; }
        tr:nth-child(even) { background: #fafdff; }
        .status { margin: 16px 0; padding: 12px 16px; border-radius: 10px; font-weight: 600; }
        .status.error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .empty { margin-top: 16px; font-style: italic; color: #6b7280; }
        .page-actions { margin-top: 12px; }
        .page-actions a { color: #1e90ff; font-weight: 600; }
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
            <div class="page-actions">
                <a href="add_student.php">+ Ajouter un étudiant</a>
            </div>

            <h1>Liste des étudiants</h1>

            <?php if (!empty($error)): ?>
                <p class="status error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if (!$students): ?>
                <p class="empty">Aucun étudiant enregistré.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Matricule</th>
                            <th>Groupe</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($student['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($student['matricule'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($student['group_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="actions">
                                    <a href="update_student.php?id=<?php echo urlencode((string)$student['id']); ?>">Modifier</a>
                                    <a href="delete_student.php?id=<?php echo urlencode((string)$student['id']); ?>" onclick="return confirm('Supprimer cet étudiant ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>

