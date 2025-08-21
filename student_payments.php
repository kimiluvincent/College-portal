<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit;
}

include 'config.php';

$student_id = $_SESSION['user_id'];

// Get course_id for logged-in student
$stmt = $conn->prepare("SELECT course_id FROM users WHERE user_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$res = $stmt->get_result();
$student = $res->fetch_assoc();
$course_id = $student['course_id'] ?? null;

if (!$course_id) {
    die("❌ No course assigned to your profile. Contact admin.");
}

// Fetch all course fee requirements
$stmt2 = $conn->prepare("
    SELECT academic_year, semester, fee_amount 
    FROM course_fees 
    WHERE course_id = ? 
    ORDER BY academic_year ASC, semester ASC
");
$stmt2->bind_param("i", $course_id);
$stmt2->execute();
$fees_res = $stmt2->get_result();

$semesters_data = [];
while ($row = $fees_res->fetch_assoc()) {
    $academic_year = $row['academic_year'];
    $semester = $row['semester'];
    $required_fee = $row['fee_amount'];

    // Total paid for this semester
    $stmt3 = $conn->prepare("
        SELECT SUM(amount) as paid 
        FROM payments 
        WHERE student_id = ? 
          AND course_id = ? 
          AND semester = ? 
          AND academic_year = ?
    ");
    $stmt3->bind_param("siis", $student_id, $course_id, $semester, $academic_year);
    $stmt3->execute();
    $pay_res = $stmt3->get_result();
    $pay_data = $pay_res->fetch_assoc();

    $paid = $pay_data['paid'] ?? 0;
    $balance = $required_fee - $paid;
    $status = ($balance <= 0) ? "Paid" : "Pending";

    // Get detailed payments
    $stmt4 = $conn->prepare("
        SELECT method, amount, reference_no, status, payment_date
        FROM payments
        WHERE student_id = ?
          AND course_id = ?
          AND semester = ?
          AND academic_year = ?
        ORDER BY payment_date ASC
    ");
    $stmt4->bind_param("siis", $student_id, $course_id, $semester, $academic_year);
    $stmt4->execute();
    $details_res = $stmt4->get_result();
    $details = $details_res->fetch_all(MYSQLI_ASSOC);

    $semesters_data[] = [
        'academic_year' => $academic_year,
        'semester' => $semester,
        'required_fee' => $required_fee,
        'paid' => $paid,
        'balance' => $balance,
        'status' => $status,
        'details' => $details
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Payments</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 1000px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007BFF; color: white; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: red; font-weight: bold; }
        h2 { text-align: center; }
        .details { display: none; margin-top: 10px; }
        .details table { width: 90%; margin: auto; margin-top: 5px; }
        .toggle-btn { cursor: pointer; color: #007BFF; text-decoration: underline; font-size: 14px; }
    </style>
    <script>
        function toggleDetails(id) {
            var el = document.getElementById("details-" + id);
            el.style.display = (el.style.display === "none" || el.style.display === "") ? "block" : "none";
        }
    </script>
</head>
<body>
<div class="container">
    <h2>💰 My Payments</h2>
    <table>
        <tr>
            <th>Academic Year</th>
            <th>Semester</th>
            <th>Required Fee</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Details</th>
        </tr>
        <?php foreach ($semesters_data as $i => $data): ?>
        <tr>
            <td><?= htmlspecialchars($data['academic_year']); ?></td>
            <td><?= $data['semester']; ?></td>
            <td><?= number_format($data['required_fee'], 2); ?></td>
            <td><?= number_format($data['paid'], 2); ?></td>
            <td><?= number_format($data['balance'], 2); ?></td>
            <td class="<?= $data['status'] == 'Paid' ? 'status-paid' : 'status-pending'; ?>">
                <?= $data['status']; ?>
            </td>
            <td><span class="toggle-btn" onclick="toggleDetails(<?= $i ?>)">View Details</span></td>
        </tr>
        <tr id="details-<?= $i ?>" class="details">
            <td colspan="7">
                <?php if (!empty($data['details'])): ?>
                <table>
                    <tr>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Reference No</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                    <?php foreach ($data['details'] as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['method']); ?></td>
                        <td><?= number_format($d['amount'], 2); ?></td>
                        <td><?= htmlspecialchars($d['reference_no']); ?></td>
                        <td><?= htmlspecialchars($d['status']); ?></td>
                        <td><?= htmlspecialchars($d['payment_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <em>No payments made yet.</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
