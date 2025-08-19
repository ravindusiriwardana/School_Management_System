<?php
session_start();
include '../dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if (isset($_POST['add'])) {
    $subject_name = trim($_POST['subject_name']);

    if (!empty($subject_name)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
        $stmt->bind_param("s", $subject_name);
        $stmt->execute();
        header("Location: subject_list.php");
        exit();
    } else {
        $error = "Please enter a subject name.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - School Management System</title>
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

        /* Top accent bar */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #0000FF;
            z-index: 1000;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 2px solid #008000;
            position: relative;
        }



        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .error-message {
            background: #ffebee;
            color: #FF0000;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid #FF0000;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            color: #333;
        }

        .form-input:focus {
            outline: none;
            border-color: #0000FF;
            box-shadow: 0 0 0 3px rgba(0, 0, 255, 0.1);
        }

        .form-input::placeholder {
            color: #999;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 32px;
        }

        .submit-button {
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
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .submit-button:hover {
            background: #0000FF;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 255, 0.3);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-block;
            width: 100%;
            padding: 14px;
            text-align: center;
            color: #0000FF;
            text-decoration: none;
            border: 2px solid #0000FF;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .back-link:hover {
            background: #0000FF;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 255, 0.2);
        }

        .success-indicator {
            width: 24px;
            height: 24px;
            background: #008000;
            border-radius: 50%;
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        /* Responsive design */
        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 30px 24px;
            }
            
            .header h2 {
                font-size: 24px;
            }
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            animation: slideIn 0.5s ease-out;
        }

        .icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .subject-icon {
            background: #008000;
            border-radius: 4px;
            position: relative;
        }

        .subject-icon::after {
            content: '📚';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-indicator">+</div>
        
        <div class="header">
            <h2><span class="icon subject-icon"></span>Add New Subject</h2>
            <p>Create a new subject for the curriculum</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="subject_name">Subject Name</label>
                <input type="text" 
                       id="subject_name"
                       name="subject_name" 
                       class="form-input"
                       placeholder="Enter subject name (e.g., Mathematics, Science, English)"
                       required
                       maxlength="100">
            </div>

            <div class="button-group">
                <input type="submit" name="add" value="Add Subject" class="submit-button">
                <a href="subject_list.php" class="back-link">← Back to Subjects</a>
            </div>
        </form>
    </div>
</body>
</html>