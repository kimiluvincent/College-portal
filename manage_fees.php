<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}
include 'config.php';

$msg = "";

// Add Fee
if (isset($_POST['add_fee'])) {
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $fee_amount = $_POST['fee_amount'];

    $stmt = $conn->prepare("INSERT INTO course_fees (course_id, academic_year, semester, fee_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isid", $course_id, $academic_year, $semester, $fee_amount);

    $msg = $stmt->execute() ? "✅ Fee added successfully!" : "❌ Error: " . $stmt->error;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Course Fees</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        select, input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #28a745; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #28a745; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Course Fees</h2>
    <?php if ($msg) echo "<p>$msg</p>"; ?>

    <form method="POST">
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php
            $courses = $conn->query("SELECT id, course_name FROM courses");
            while ($c = $courses->fetch_assoc()) {
                echo "<option value='{$c['id']}'>{$c['course_name']}</option>";
            }
            ?>
        </select>

        <input type="text" name="academic_year" placeholder="Academic Year (e.g., 2024/2025)" required>
        <input type="number" name="semester" placeholder="Semester" min="1" required>
        <input type="number" step="0.01" name="fee_amount" placeholder="Fee Amount" required>
        <button type="submit" name="add_fee">Add Fee</button>
    </form>

    <h3>Existing Fees</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Course</th>
            <th>Academic Year</th>
            <th>Semester</th>
            <th>Fee Amount</th>
        </tr>
        <?php
        $result = $conn->query("
            SELECT cf.id, c.course_name, cf.academic_year, cf.semester, cf.fee_amount
            FROM course_fees cf
            JOIN courses c ON cf.course_id = c.id
        ");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['course_name']}</td>
                    <td>{$row['academic_year']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['fee_amount']}</td>
                  </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
