<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit;
}

include 'config.php';
$name = $_SESSION['name'];
$student_userid = $_SESSION['user_id'];

// ✅ Get student numeric ID + course_id
$studentQuery = $conn->prepare("SELECT id, course_id FROM users WHERE user_id = ?");
$studentQuery->bind_param("s", $student_userid);
$studentQuery->execute();
$studentRow = $studentQuery->get_result()->fetch_assoc();

if (!$studentRow) {
    die("❌ Student not found. Contact admin.");
}

$student_id = $studentRow['id'];
$course_id  = $studentRow['course_id'];

// ✅ Total units in student’s course
$total_units = $conn->query("SELECT COUNT(*) as total FROM units WHERE course_id = '$course_id'")
                   ->fetch_assoc()['total'] ?? 0;

// ✅ Total payments made
$total_paid = $conn->query("SELECT SUM(amount) as total FROM payments WHERE student_id = '$student_id'")
                   ->fetch_assoc()['total'] ?? 0;

// ✅ Average marks
$avg_marks = $conn->query("SELECT AVG(marks) as avg FROM marks WHERE student_id = '$student_id'")
                  ->fetch_assoc()['avg'] ?? 0;
$avg_marks = round($avg_marks, 2);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .navbar { background: #343a40; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: white; margin: 0; font-size: 20px; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; font-size: 14px; }
        .navbar a:hover { text-decoration: underline; }
        .container { padding: 20px; }
        h2 { color: #333; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 18px;
        }
        .stat-card h3 { margin: 0; font-size: 22px; }
        .stat-card p { margin: 5px 0 0; font-size: 16px; }

        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: transform 0.2s ease; }
        .card:hover { transform: scale(1.03); }
        .card a { text-decoration: none; color: #007bff; font-weight: bold; font-size: 16px; display: block; }
        .card a:hover { color: #0056b3; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Student Dashboard</h1>
    <div>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>👋 Welcome, <?= htmlspecialchars($name) ?> — This is your student dashboard.</h2>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_units ?></h3>
            <p>Enrolled Units</p>
        </div>
        <div class="stat-card">
            <h3>Ksh <?= number_format($total_paid, 2) ?></h3>
            <p>Total Payments Made</p>
        </div>
        <div class="stat-card">
            <h3><?= $avg_marks ?>%</h3>
            <p>Average Marks</p>
        </div>
    </div>

    <!-- Navigation -->
    <div class="card-grid">
        <div class="card"><a href="student_units.php">📘 My Units</a></div>
        <div class="card"><a href="student_payments.php">💰 My Payments</a></div>
        <div class="card"><a href="student_marks.php">📝 My Marks</a></div>
    </div>
</div>

</body>
</html>
