<?php
$studentsFile = __DIR__ . '/students.json';
$students = [];

if (file_exists($studentsFile)) {
    $raw = file_get_contents($studentsFile);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $students = $decoded;
    }
}

$today = date('Y-m-d');
$attendanceFile = __DIR__ . "/attendance_{$today}.json";
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$message = '';
$error = '';

if ($method === 'POST') {
    if (file_exists($attendanceFile)) {
        $error = 'La présence pour aujourd’hui a déjà été enregistrée.';
    } else {
        $submitted = $_POST['status'] ?? [];
        $attendance = [];

        foreach ($students as $student) {
            $sid = (string)($student['student_id'] ?? $student['studentId'] ?? '');
            if ($sid === '') {
                continue;
            }
            $status = isset($submitted[$sid]) && $submitted[$sid] === 'present' ? 'present' : 'absent';
            $attendance[] = [
                'student_id' => $sid,
                'status' => $status,
            ];
        }

        file_put_contents(
            $attendanceFile,
            json_encode($attendance, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            LOCK_EX
        );
        $message = 'Présence enregistrée avec succès.';
    }
}
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Prise de présence</title>
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
        .container { max-width: 1100px; margin: 32px auto; padding: 0 20px; }
        .card { background: #f8fafc; border: 1px solid #d0d7de; border-radius: 16px; padding: 32px; box-shadow: 0 10px 24px rgba(30, 144, 255, 0.08); }
        h1 { font-size: 1.75rem; color: #1e90ff; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #e0f2ff; color: #1e90ff; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.8px; }
        tr:nth-child(even) { background: #fafdff; }
        button { background: #1e90ff; color: #fff; border: none; padding: 12px 22px; border-radius: 8px; cursor: pointer; font-size: 0.95rem; margin-top: 20px; }
        button:hover { background: #0f60a6; }
        label { font-weight: 600; color: #1b2a4a; margin-right: 12px; }
        .status { margin-top: 16px; padding: 12px 16px; border-radius: 10px; font-weight: 600; }
        .status.success { background: #dcfce7; color: #166534; border: 1px solid #22c55e; }
        .status.error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .empty { margin-top: 16px; font-style: italic; color: #6b7280; }
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
        <h1>Prise de présence - <?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?></h1>

        <?php if ($message !== ''): ?>
            <p class="status success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="status error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($students): ?>
            <form method="post" action="">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Groupe</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php
                        $sid = (string)($student['student_id'] ?? $student['studentId'] ?? '');
                        if ($sid === '') {
                            continue;
                        }
                        $nm = $student['name'] ?? trim(($student['firstName'] ?? '') . ' ' . ($student['lastName'] ?? ''));
                        $grp = $student['group'] ?? $student['course'] ?? '';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sid, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($nm, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($grp, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <label>
                                    <input type="radio" name="status[<?php echo htmlspecialchars($sid, ENT_QUOTES, 'UTF-8'); ?>]"
                                           value="present" checked>
                                    Présent
                                </label>
                                <label>
                                    <input type="radio" name="status[<?php echo htmlspecialchars($sid, ENT_QUOTES, 'UTF-8'); ?>]"
                                           value="absent">
                                    Absent
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Enregistrer la présence</button>
            </form>
        <?php else: ?>
            <p class="empty">Aucun étudiant trouvé. Ajoutez des étudiants avant de prendre la présence.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>

