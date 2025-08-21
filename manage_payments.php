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

// Save payment
if (isset($_POST['add_payment'])) {
    $student_id = $_POST['student_id']; // now this is users.id
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $purpose = $_POST['purpose'];
    $method = $_POST['method'];
    $amount = $_POST['amount'];
    $reference_no = $_POST['reference_no'];
    $status = $_POST['status'];
    $payment_date = $_POST['payment_date'];

    $stmt = $conn->prepare("INSERT INTO payments (student_id, course_id, academic_year, semester, purpose, method, amount, reference_no, status, payment_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssdsss", $student_id, $course_id, $academic_year, $semester, $purpose, $method, $amount, $reference_no, $status, $payment_date);

    if ($stmt->execute()) {
        $msg = "✅ Payment recorded successfully!";
    } else {
        $msg = "❌ Error: " . $stmt->error;
    }
}

// Fetch payments
$payments = $conn->query("
    SELECT p.*, u.name, u.user_id, c.course_name 
    FROM payments p
    JOIN users u ON p.student_id = u.id
    JOIN courses c ON p.course_id = c.id
    ORDER BY p.payment_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Payments</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 1000px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        input, select, button { width: 100%; padding: 10px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #28a745; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #218838; }
        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007BFF; color: white; }
    </style>
    <script>
    function fetchFee() {
        var studentId = document.getElementById("student_id").value;
        var semester = document.getElementById("semester").value;

        if (studentId && semester) {
            fetch("get_fee.php?student_id=" + studentId + "&semester=" + semester)
            .then(res => res.json())
            .then(data => {
                document.getElementById("required_fee").value = data.fee_amount;
                document.getElementById("academic_year").value = data.academic_year;
                document.getElementById("course_id").value = data.course_id;
            });
        }
    }
    </script>
</head>
<body>
<div class="container">
    <h2>💰 Manage Payments</h2>
    <?php if ($msg) { echo "<div class='msg " . (strpos($msg,'✅')!==false ? 'success' : 'error') . "'>$msg</div>"; } ?>

    <!-- Payment Form -->
    <form method="POST">
        <select name="student_id" id="student_id" required onchange="fetchFee()">
            <option value="">-- Select Student --</option>
            <?php
            $res = $conn->query("SELECT id, name, user_id FROM users WHERE role='student'");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']} ({$row['user_id']})</option>";
            }
            ?>
        </select>

        <input type="number" name="semester" id="semester" placeholder="Semester" required onchange="fetchFee()">
        
        <!-- Auto-filled fields -->
        <input type="text" id="academic_year" name="academic_year" placeholder="Academic Year" readonly>
        <input type="hidden" id="course_id" name="course_id">
        <input type="text" id="required_fee" placeholder="Required Fee" readonly>

        <input type="text" name="purpose" placeholder="Purpose (e.g., Tuition Fees)" required>
        <select name="method" required>
            <option value="MPESA">MPESA</option>
            <option value="Bank Deposit">Bank Deposit</option>
            <option value="Cheque">Cheque</option>
            <option value="Cash">Cash</option>
        </select>
        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
        <input type="text" name="reference_no" placeholder="Reference Number">
        <select name="status" required>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
        </select>
        <input type="date" name="payment_date" required>
        <button type="submit" name="add_payment">Save Payment</button>
    </form>

    <!-- Payment Records -->
    <h3>📋 Payment Records</h3>
    <table>
        <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Academic Year</th>
            <th>Semester</th>
            <th>Purpose</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php if ($payments->num_rows > 0): ?>
            <?php while ($row = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['user_id']) ?>)</td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['academic_year']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['purpose']) ?></td>
                    <td><?= htmlspecialchars($row['method']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['payment_date']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">No payments recorded</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
