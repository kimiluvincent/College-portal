<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: index.php");
    exit;
}

include 'config.php';

$name = $_SESSION['name'];
$staff_userid = $_SESSION['user_id'];

// ✅ Get staff numeric ID
$staffQuery = $conn->prepare("SELECT id FROM users WHERE user_id = ?");
$staffQuery->bind_param("s", $staff_userid);
$staffQuery->execute();
$staffRow = $staffQuery->get_result()->fetch_assoc();

if (!$staffRow) {
    die("❌ Staff not found. Contact admin.");
}
$staff_id = $staffRow['id'];

// ✅ Quick stats
$total_students = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student'")
                      ->fetch_assoc()['total'] ?? 0;

$total_units = $conn->query("SELECT COUNT(*) as total FROM units")
                   ->fetch_assoc()['total'] ?? 0;

$marks_uploaded = $conn->query("SELECT COUNT(*) as total FROM marks WHERE uploaded_by = '$staff_id'")
                      ->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #007BFF, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 18px;
        }
        .stat-card h3 { margin: 0; font-size: 22px; }
        .stat-card p { margin: 5px 0 0; font-size: 16px; }

        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .card { background: #007BFF; color: white; padding: 20px; border-radius: 10px; text-align: center; font-size: 18px; font-weight: bold; transition: transform 0.2s ease; }
        .card a { color: white; text-decoration: none; display: block; }
        .card:hover { background: #0056b3; transform: scale(1.05); }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Staff Dashboard</h1>
    <div>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>👨‍🏫 Welcome, <?= htmlspecialchars($name) ?> — Staff Dashboard</h2>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_students ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_units ?></h3>
            <p>Total Units</p>
        </div>
        <div class="stat-card">
            <h3><?= $marks_uploaded ?></h3>
            <p>Marks Uploaded</p>
        </div>
    </div>

    <!-- Navigation -->
    <div class="card-grid">
        <div class="card"><a href="upload_marks.php">✍️ Upload Marks</a></div>
        <div class="card"><a href="view_students.php">👨‍🎓 View Students</a></div>
    </div>
</div>

</body>
</html>
