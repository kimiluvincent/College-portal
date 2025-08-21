<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';
$name = $_SESSION['name'];

// ✅ Fetch quick stats
$total_students = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student'")->fetch_assoc()['total'] ?? 0;
$total_courses = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'] ?? 0;
$total_payments = $conn->query("SELECT SUM(amount) as total FROM payments")->fetch_assoc()['total'] ?? 0;
$pending_payments = $conn->query("SELECT COUNT(*) as total FROM payments WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
        }
        .navbar {
            background: #343a40;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            color: white;
            margin: 0;
            font-size: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-size: 14px;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 18px;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 22px;
        }
        .stat-card p {
            margin: 5px 0 0;
            font-size: 16px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .card a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            font-size: 16px;
            display: block;
        }
        .card a:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Admin Dashboard</h1>
    <div>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>👋 Welcome, <?= htmlspecialchars($name) ?> — This is your admin dashboard.</h2>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_students ?></h3>
            <p>Students</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_courses ?></h3>
            <p>Courses</p>
        </div>
        <div class="stat-card">
            <h3>Ksh <?= number_format($total_payments, 2) ?></h3>
            <p>Total Payments</p>
        </div>
        <div class="stat-card">
            <h3><?= $pending_payments ?></h3>
            <p>Pending Payments</p>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="card-grid">
        <div class="card"><a href="manage_courses.php">📚 Manage Courses</a></div>
        <div class="card"><a href="manage_units.php">📖 Manage Units</a></div>
        <div class="card"><a href="manage_enrollments.php">👨‍🎓 Manage Enrollments</a></div>
        <div class="card"><a href="manage_fees.php">💵 Manage Fees</a></div>
        <div class="card"><a href="manage_payments.php">💰 Manage Payments</a></div>
        <div class="card"><a href="view_reports.php">📊 View Reports</a></div>
    </div>
</div>

</body>
</html>
