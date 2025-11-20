<?php
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$errors = [];
$message = '';
$values = ['student_id' => '', 'name' => '', 'group' => '', 'email' => ''];
if ($method !== 'POST') {
    header('Location: add.html');
    exit;
}
if ($method === 'POST') {
    $sid = trim($_POST['student_id'] ?? ($_POST['studentId'] ?? ''));
    $first = trim($_POST['firstName'] ?? '');
    $last = trim($_POST['lastName'] ?? '');
    $nm = trim($_POST['name'] ?? (($first !== '' || $last !== '') ? trim($first . ' ' . $last) : ''));
    $grp = trim($_POST['group'] ?? ($_POST['course'] ?? 'CS101'));
    $email = trim($_POST['email'] ?? '');
    $values['student_id'] = $sid;
    $values['name'] = $nm;
    $values['group'] = $grp;
    $values['email'] = $email;
    if ($values['student_id'] === '' || !preg_match('/^\d+$/', $values['student_id'])) {
        $errors[] = 'Invalid student_id';
    }
    if ($values['name'] === '' || !preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\'\- ]+$/u', $values['name'])) {
        $errors[] = 'Invalid name';
    }
    if ($values['group'] === '' || !preg_match('/^[A-Za-z0-9_\.\- ]+$/', $values['group'])) {
        $errors[] = 'Invalid group';
    }
    if (!$errors) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'students.json';
        $students = [];
        if (file_exists($path)) {
            $raw = file_get_contents($path);
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $students = $decoded;
            }
        }
        $parts = preg_split('/\s+/', $values['name']);
        if (count($parts) > 1) {
            $lastName = array_pop($parts);
            $firstName = implode(' ', $parts);
        } else {
            $firstName = $values['name'];
            $lastName = '';
        }
        $new = [
            'student_id' => $values['student_id'],
            'name' => $values['name'],
            'group' => $values['group'],
            'studentId' => $values['student_id'],
            'firstName' => $firstName,
            'lastName' => $lastName,
            'course' => $values['group'],
            'email' => $values['email']
        ];
        $updated = false;
        foreach ($students as $i => $s) {
            $sidExisting = '';
            if (is_array($s)) {
                if (isset($s['student_id'])) {
                    $sidExisting = (string)$s['student_id'];
                } elseif (isset($s['studentId'])) {
                    $sidExisting = (string)$s['studentId'];
                }
            }
            if ($sidExisting !== '' && $sidExisting === (string)$values['student_id']) {
                $students[$i] = $new;
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $students[] = $new;
        }
        $jsonOut = json_encode($students, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($jsonOut === false) {
            $errors[] = 'Failed to encode data';
        } else {
            file_put_contents($path, $jsonOut, LOCK_EX);
            $message = 'Student saved successfully';
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Student</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 24px; }
    a { color: #562c22; }
    .success { color: #1a7f37; }
    .error { color: #b00020; }
  </style>
  </head>
<body>
  <?php if ($message !== ''): ?>
    <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <p><a href="add.html">Back to Add Student</a></p>
    <p><a href="attendance.html">Go to Attendance</a></p>
  <?php endif; ?>
  <?php if ($errors): ?>
    <ul class="error">
      <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
      <?php endforeach; ?>
    </ul>
    <p><a href="add.html">Back to Add Student</a></p>
  <?php endif; ?>
</body>
</html>