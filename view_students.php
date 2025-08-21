<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Fetch enrolled students (linked via user_id instead of id)
$stmt = $conn->prepare("
    SELECT se.*, u.name, u.user_id, c.course_name
    FROM student_enrollment se
    JOIN users u ON se.student_id = u.user_id
    JOIN courses c ON se.course_id = c.id
    ORDER BY se.academic_year, se.semester
");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Students</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 900px; margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007BFF; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .no-data { text-align: center; padding: 20px; font-size: 16px; color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍🎓 Enrolled Students</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Admission No</th>
                <th>Name</th>
                <th>Course</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Enrollment Date</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_id']); ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['course_name']); ?></td>
                    <td><?= htmlspecialchars($row['academic_year']); ?></td>
                    <td><?= htmlspecialchars($row['semester']); ?></td>
                    <td><?= htmlspecialchars($row['enrollment_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="no-data">❌ No students found. Please check enrollments.</div>
    <?php endif; ?>
</div>
</body>
</html>
