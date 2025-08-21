<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit;
}

include 'config.php';

$student_userid = $_SESSION['user_id'];

// ✅ Get student's numeric ID
$stmt = $conn->prepare("SELECT id, name FROM users WHERE user_id = ?");
$stmt->bind_param("s", $student_userid);
$stmt->execute();
$res = $stmt->get_result();
$student = $res->fetch_assoc();

if (!$student) {
    die("❌ Student not found. Contact admin.");
}

$student_id = $student['id'];
$student_name = $student['name'];

// ✅ Fetch student marks
$query = "
    SELECT m.*, c.course_name, u.unit_name
    FROM marks m
    JOIN courses c ON m.course_id = c.id
    JOIN units u ON m.unit_id = u.id
    WHERE m.student_id = ?
    ORDER BY m.academic_year DESC, m.semester DESC
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$marksRes = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Marks</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 1000px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007BFF; color: white; }
        .grade-A { color: green; font-weight: bold; }
        .grade-B { color: blue; font-weight: bold; }
        .grade-C { color: orange; font-weight: bold; }
        .grade-D { color: brown; font-weight: bold; }
        .grade-F { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 My Marks (<?= htmlspecialchars($student_name) ?>)</h2>
    <table>
        <tr>
            <th>Academic Year</th>
            <th>Semester</th>
            <th>Course</th>
            <th>Unit</th>
            <th>Marks</th>
            <th>Grade</th>
        </tr>
        <?php if ($marksRes->num_rows > 0): ?>
            <?php while ($row = $marksRes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['academic_year']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['unit_name']) ?></td>
                    <td><?= number_format($row['marks'], 2) ?></td>
                    <td class="grade-<?= $row['grade'] ?>"><?= htmlspecialchars($row['grade']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">❌ No marks uploaded yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
