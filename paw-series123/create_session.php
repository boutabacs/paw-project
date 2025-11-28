<?php
require __DIR__ . '/db_connect.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    header('Content-Type: application/json');

    $courseId = trim($_POST['course_id'] ?? '');
    $groupId = trim($_POST['group_id'] ?? '');
    $professorId = trim($_POST['opened_by'] ?? '');
    $date = trim($_POST['date'] ?? date('Y-m-d'));
    $errors = [];

    if ($courseId === '') {
        $errors[] = 'Le cours est requis.';
    }
    if ($groupId === '') {
        $errors[] = 'Le groupe est requis.';
    }
    if ($professorId === '') {
        $errors[] = 'Le professeur est requis.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
        $errors[] = 'Date invalide (format attendu AAAA-MM-JJ).';
    }

    if ($errors) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        exit;
    }

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('INSERT INTO attendance_sessions (course_id, group_id, session_date, opened_by, status) VALUES (:course_id, :group_id, :session_date, :opened_by, :status)');
        $stmt->execute([
            ':course_id' => $courseId,
            ':group_id' => $groupId,
            ':session_date' => $date,
            ':opened_by' => $professorId,
            ':status' => 'open',
        ]);

        echo json_encode([
            'session_id' => $pdo->lastInsertId(),
            'status' => 'open',
            'message' => 'Session créée.',
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Impossible de créer la session.']);
    }
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Créer une session de présence</title>
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
        .container { max-width: 780px; margin: 32px auto; padding: 0 20px; }
        .card { background: #f8fafc; border: 1px solid #d0d7de; border-radius: 16px; padding: 32px; box-shadow: 0 10px 24px rgba(30, 144, 255, 0.08); }
        h1 { font-size: 1.7rem; color: #1e90ff; margin-bottom: 12px; }
        form { display: grid; gap: 16px; margin-top: 16px; }
        label { font-size: 0.95rem; font-weight: 600; color: #1b2a4a; }
        input { width: 100%; padding: 12px; border: 1px solid #b6c0cf; border-radius: 8px; font-size: 0.95rem; }
        button { width: fit-content; background: #1e90ff; color: #fff; border: none; padding: 12px 22px; border-radius: 8px; cursor: pointer; font-size: 0.95rem; }
        button:hover { background: #0f60a6; }
        pre { background: #0f1729; color: #e2e8f0; padding: 16px; border-radius: 10px; overflow-x: auto; margin-top: 20px; font-size: 0.9rem; }
        .status { margin-top: 16px; padding: 12px 16px; border-radius: 10px; font-weight: 600; display:none; }
        .status.success { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
        .status.error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
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
            <li><a href="close_session.php">Fermer une session</a></li>
        </ul>
    </div>
</nav>

<main class="container">
    <section class="card">
        <h1>Créer une session</h1>
        <form id="createSessionForm">
            <label>
                Identifiant du cours
                <input type="text" name="course_id" required>
            </label>
            <label>
                Groupe
                <input type="text" name="group_id" required>
            </label>
            <label>
                ID du professeur
                <input type="text" name="opened_by" required>
            </label>
            <label>
                Date (AAAA-MM-JJ)
                <input type="date" name="date" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
            <button type="submit">Créer la session</button>
        </form>
        <div id="status" class="status"></div>
        <pre id="response" style="display:none;"></pre>
    </section>
</main>

<script>
const form = document.getElementById('createSessionForm');
const statusBox = document.getElementById('status');
const responseBox = document.getElementById('response');

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    statusBox.style.display = 'none';
    responseBox.style.display = 'none';

    const formData = new FormData(form);

    try {
        const res = await fetch('', { method: 'POST', body: formData });
        const data = await res.json();
        responseBox.style.display = 'block';
        responseBox.textContent = JSON.stringify(data, null, 2);

        statusBox.style.display = 'block';
        if (res.ok) {
            statusBox.className = 'status success';
            statusBox.textContent = 'Session créée avec succès.';
        } else {
            statusBox.className = 'status error';
            statusBox.textContent = 'Erreur : ' + (data.error ?? (Array.isArray(data.errors) ? data.errors.join(', ') : 'Action impossible'));
        }
    } catch (error) {
        statusBox.style.display = 'block';
        statusBox.className = 'status error';
        statusBox.textContent = 'Erreur réseau ou serveur.';
    }
});
</script>
</body>
</html>

