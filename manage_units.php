<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}
include 'config.php';

$msg = "";




// Add Unit
if (isset($_POST['add_unit'])) {
    $course_id = $_POST['course_id'];
    $semester = $_POST['semester'];
    $unit_name = trim($_POST['unit_name']);

$stmt = $conn->prepare("INSERT INTO units (course_id, semester, unit_code, unit_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $course_id, $semester, $unit_code, $unit_name);

    $msg = $stmt->execute() ? "✅ Unit added successfully!" : "❌ Error: " . $stmt->error;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Units</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        select, input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #007bff; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Units</h2>
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

        <input type="number" name="semester" placeholder="Semester" min="1" required>
        
        <input type="text" name="unit_code" placeholder="Unit Code (e.g., CS101)" required>
<input type="text" name="unit_name" placeholder="Unit Name (e.g., Intro to Programming)" required>

        <button type="submit" name="add_unit">Add Unit</button>
    </form>
    
    

    <h3>Existing Units</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Unit Name</th>
        </tr>
        <?php
        $result = $conn->query("
            SELECT u.id, c.course_name, u.semester, u.unit_name 
            FROM units u
            JOIN courses c ON u.course_id = c.id
        ");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['course_name']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['unit_name']}</td>
                  </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
