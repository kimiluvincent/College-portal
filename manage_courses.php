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

// Add Course
if (isset($_POST['add_course'])) {
    $name = trim($_POST['course_name']);
    $desc = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO courses (course_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $desc);

    $msg = $stmt->execute() ? "✅ Course added successfully!" : "❌ Error: " . $stmt->error;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        input, textarea, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #007bff; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Courses</h2>
    <?php if ($msg) echo "<p>$msg</p>"; ?>

    <form method="POST">
        <input type="text" name="course_name" placeholder="Course Name" required>
        <textarea name="description" placeholder="Course Description"></textarea>
        <button type="submit" name="add_course">Add Course</button>
    </form>

    <h3>Existing Courses</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Course Name</th>
            <th>Description</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM courses");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['course_name']}</td>
                    <td>{$row['description']}</td>
                  </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
