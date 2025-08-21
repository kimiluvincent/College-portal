<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';
$msg = "";

// Handle enrollment form submission
if (isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id']; // Admission No / Staff ID
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];

    $stmt = $conn->prepare("INSERT INTO student_enrollment (student_id, course_id, academic_year, semester) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $student_id, $course_id, $academic_year, $semester);

    if ($stmt->execute()) {
        $msg = "✅ Student enrolled successfully!";
    } else {
        $msg = "❌ Error: " . $stmt->error;
    }
}

// Fetch enrollments
$enrollments = $conn->query("
    SELECT se.student_id, u.name, c.course_name, se.academic_year, se.semester, se.enrollment_date
    FROM student_enrollment se
    JOIN users u ON se.student_id = u.user_id
    JOIN courses c ON se.course_id = c.id
    ORDER BY se.academic_year DESC, se.semester ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Enrollments</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 900px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        input, select, button {
            width: 100%; padding: 10px; margin-bottom: 12px;
            border-radius: 6px; border: 1px solid #ccc;
        }
        button {
            background: #007BFF; color: white; border: none;
            font-weight: bold; cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
        }
        th, td {
            padding: 10px; border: 1px solid #ddd; text-align: center;
        }
        th { background: #007BFF; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
    </style>
</head>
<body>

<div class="container">
    <h2>👨‍🎓 Manage Student Enrollments</h2>

    <?php if ($msg) {
        $class = strpos($msg, '✅') !== false ? 'success' : 'error';
        echo "<div class='msg $class'>$msg</div>";
    } ?>

    <!-- Enrollment Form -->
    <form method="POST">
        <label for="student_id">Select Student</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select Student --</option>
            <?php
            $res = $conn->query("SELECT user_id, name FROM users WHERE role='student'");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['user_id']}'>{$row['name']} ({$row['user_id']})</option>";
            }
            ?>
        </select>

        <label for="course_id">Select Course</label>
        <select name="course_id" id="course_id" required>
            <option value="">-- Select Course --</option>
            <?php
            $res = $conn->query("SELECT id, course_name FROM courses");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['course_name']}</option>";
            }
            ?>
        </select>

        <label for="academic_year">Academic Year</label>
        <input type="text" name="academic_year" id="academic_year" placeholder="e.g., 2024/2025" required>

        <label for="semester">Semester</label>
        <input type="number" name="semester" id="semester" min="1" required>

        <button type="submit" name="enroll_student">Enroll Student</button>
    </form>

    <!-- Enrollment List -->
    <h3>📋 Current Enrollments</h3>
    <table>
        <tr>
            <th>Student Name</th>
            <th>Admission No</th>
            <th>Course</th>
            <th>Academic Year</th>
            <th>Semester</th>
            <th>Enrollment Date</th>
        </tr>
        <?php if ($enrollments->num_rows > 0): ?>
            <?php while ($row = $enrollments->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['academic_year']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['enrollment_date']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No enrollments found</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
