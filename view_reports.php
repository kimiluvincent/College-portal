<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>ðŸ“Š View Reports</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 1200px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        h3 { margin-top: 30px; }

        /* âœ… DataTables fixes */
        table.dataTable {
            width: 100% !important;
            border-collapse: collapse;
            table-layout: fixed; /* ensures headers align with rows */
            font-size: 14px;
        }
        table.dataTable th, 
        table.dataTable td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        table.dataTable th {
            background: #007BFF;
            color: white;
        }
        tfoot {
            font-weight: bold;
            background: #f1f1f1;
        }
        .dataTables_wrapper {
            overflow-x: auto;
        }

        /* âœ… Zebra striping */
        table.dataTable tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        table.dataTable tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>ðŸ“Š College Reports</h2>

    <!-- ðŸ”¹ Courses & Units Report -->
    <h3>ðŸ“š Courses & Units Report</h3>
    <table id="coursesTable" class="display">
        <thead>
            <tr>
                <th>Course</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Fee</th>
                <th>Units</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("
                SELECT c.course_name, cf.academic_year, cf.semester, cf.fee_amount, 
                       GROUP_CONCAT(u.unit_name SEPARATOR ', ') AS units
                FROM courses c
                LEFT JOIN course_fees cf ON c.id = cf.course_id
                LEFT JOIN units u ON c.id = u.course_id AND cf.semester = u.semester
                GROUP BY c.course_name, cf.academic_year, cf.semester, cf.fee_amount
                ORDER BY c.course_name, cf.academic_year, cf.semester
            ");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['course_name']}</td>
                    <td>{$row['academic_year']}</td>
                    <td>{$row['semester']}</td>
                    <td>" . number_format($row['fee_amount'], 2) . "</td>
                    <td>{$row['units']}</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- ðŸ”¹ Payments Report -->
    <h3>ðŸ’° Payments Report</h3>
    <table id="paymentsTable" class="display">
        <thead>
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
        </thead>
        <tbody>
            <?php
            $result = $conn->query("
                SELECT p.*, u.name, u.user_id, c.course_name
                FROM payments p
                JOIN users u ON p.student_id = u.id
                JOIN courses c ON p.course_id = c.id
                ORDER BY p.payment_date DESC
            ");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['name']} ({$row['user_id']})</td>
                    <td>{$row['course_name']}</td>
                    <td>{$row['academic_year']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['purpose']}</td>
                    <td>{$row['method']}</td>
                    <td>" . number_format($row['amount'], 2) . "</td>
                    <td>{$row['status']}</td>
                    <td>{$row['payment_date']}</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#coursesTable').DataTable({
        scrollX: true,
        paging: true,
        searching: true,
        ordering: true,
        info: true
    });

    $('#paymentsTable').DataTable({
        scrollX: true,
        paging: true,
        searching: true,
        ordering: true,
        info: true
    });
});
</script>
</body>
</html>
