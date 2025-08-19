<?php
session_start();
include 'dbConnection.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // student or teacher

    if ($role === 'student') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    } else if ($role === 'teacher') {
        $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
    } else {
        die("Invalid role selected.");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;

        if ($role === 'student') {
            header("Location: student_management/student_profile.php");
        } else {
            header("Location: teacher_management/teachers_profile.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Decorative background elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #0000FF;
        }

        .login-container {
            background: white;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            border: 2px solid #008000;
            position: relative;
        }

        .login-container::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            height: 6px;
            background: #FF0000;
            border-radius: 16px 16px 0 0;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: #0000FF;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .welcome-text {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
        }

        .error-message {
            background: #ffebee;
            color: #FF0000;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 24px;
            border: 1px solid #ffcdd2;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #0000FF;
            box-shadow: 0 0 0 3px rgba(0, 0, 255, 0.1);
        }

        .role-select {
            width: 100%;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-select:focus {
            outline: none;
            border-color: #008000;
            box-shadow: 0 0 0 3px rgba(0, 128, 0, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 16px;
            background: #008000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .login-button:hover {
            background: #0000FF;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 255, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #f0f0f0;
            color: #888;
            font-size: 14px;
        }

        .role-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .student-indicator {
            background: #0000FF;
        }

        .teacher-indicator {
            background: #008000;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 24px;
            }
            
            .welcome-text {
                font-size: 24px;
            }
        }

        /* Subtle animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo">SMS</div>
            <h1 class="welcome-text">Welcome Back</h1>
            <p class="subtitle">Sign in to your account</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       placeholder="Enter your email address"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       placeholder="Enter your password"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">Select Your Role</label>
                <select name="role" id="role" class="role-select" required>
                    <option value="">Choose your role</option>
                    <option value="student">
                        Student
                    </option>
                    <option value="teacher">
                        Teacher
                    </option>
                </select>
            </div>

            <button type="submit" name="login" class="login-button">
                Sign In
            </button>
        </form>

        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> School Management System</p>
            <p>Secure • Professional • Reliable</p>
        </div>
    </div>
</body>
</html>