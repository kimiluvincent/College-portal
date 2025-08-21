<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = trim($_POST['user_id']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($user_id) || empty($password)) {
        $error = "❌ Please fill in both fields.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        
        

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] == 'staff') {
                    header("Location: staff_dashboard.php");
                } elseif ($user['role'] == 'student') {
                    header("Location: student_dashboard.php");
                } else {
                    $error = "❌ Invalid role.";
                }
                exit;
            } else {
                $error = "❌ Incorrect password.";
            }
        } else {
            $error = "❌ User ID not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background: #28a745;
            color: white;
            font-weight: bold;
            border: none;
        }
        button:hover {
            background: #218838;
        }
        .error {
            background: #f8d7da;
            padding: 10px;
            color: #721c24;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .register-link {
            text-align: center;
            margin-top: 10px;
        }
        .register-link a {
            color: #007BFF;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>User Login</h2>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="user_id" placeholder="Admission No / Staff ID" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="register-link">
        New here? <a href="register.php">Register Now</a>
    </div>
</div>

</body>
</html>
