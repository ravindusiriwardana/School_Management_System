<?php
session_start();
include './config/dbConnection.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Always teacher role
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'teacher';

        header("Location: ./teacher_management/teachers_profile.php");
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
    <title>Teacher Login - School Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: white; min-height: 100vh; display: flex; justify-content: center; align-items: center; position: relative; }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #0000FF; }

        .login-container {
            background: white;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            border: 2px solid #008000;
            position: relative;
            animation: fadeIn 0.6s ease-out;
        }

        .logo-section { text-align: center; margin-bottom: 40px; }
        .logo { width: 60px; height: 60px; background: #0000FF; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; }
        .welcome-text { color: #333; font-size: 28px; font-weight: 600; margin-bottom: 8px; }
        .subtitle { color: #666; font-size: 16px; }

        .error-message { background: #ffebee; color: #FF0000; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 24px; border: 1px solid #ffcdd2; font-size: 14px; }

        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        .form-input { width: 100%; padding: 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: all 0.3s ease; background: white; }
        .form-input:focus { outline: none; border-color: #0000FF; box-shadow: 0 0 0 3px rgba(0, 0, 255, 0.1); }

        .login-button, .home-button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .login-button { background: #008000; color: white; }
        .login-button:hover { background: #0000FF; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 255, 0.3); }
        .login-button:active { transform: translateY(0); }

        .home-button { background: #e2e8f0; color: #0f172a; }
        .home-button:hover { background: #cbd5e1; }

        .footer { text-align: center; margin-top: 32px; padding-top: 24px; border-top: 1px solid #f0f0f0; color: #888; font-size: 14px; }

        @media (max-width: 480px) {
            .login-container { margin: 20px; padding: 30px 24px; }
            .welcome-text { font-size: 24px; }
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo">SMS</div>
            <h1 class="welcome-text">Teacher Login</h1>
            <p class="subtitle">Sign in to your account</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
            </div>

            <button type="submit" name="login" class="login-button">Sign In</button>
        </form>

        <!-- Home button -->
        <a href="home.php" class="home-button" style="display:block; text-align:center; text-decoration:none; padding:16px; margin-top:12px; border-radius:8px;">Go to Home</a>

        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> School Management System</p>
            <p>Secure • Professional • Reliable</p>
        </div>
    </div>
</body>
</html>