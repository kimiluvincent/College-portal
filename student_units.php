<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit;
}

include 'config.php';

$student_id = $_SESSION['user_id'];

// Fetch enrolled courses and units
$stmt = $conn->prepare("
    SELECT u.unit_code, u.unit_name, se.academic_year, se.semester, c.course_name
    FROM student_enrollment se
    JOIN courses c ON se.course_id = c.id
    JOIN units u ON se.course_id = u.course_id AND se.semester = u.semester
    WHERE se.student_id = ?
    ORDER BY se.academic_year DESC, se.semester ASC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Units</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 900px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007BFF; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
    </style>
</head>
<body>
<div class="container">
    <h2>ðŸ“˜ My Units</h2>
    <table>
 <tr>
  <th>Course</th>
  <th>Academic Year</th>
  <th>Semester</th>
  <th>Unit Code</th>
  <th>Unit Name</th>
</tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['academic_year']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['unit_code']) ?></td>
                    <td><?= htmlspecialchars($row['unit_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No units assigned yet.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
