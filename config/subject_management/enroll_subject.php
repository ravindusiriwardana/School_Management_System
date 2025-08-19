<?php
session_start();
include '../dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

if (!isset($_GET['subject_id'])) {
    header("Location: student_subjects.php");
    exit();
}

$subject_id = intval($_GET['subject_id']);

// Get subject name for display
$subject_stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE subject_id = ?");
$subject_stmt->bind_param("i", $subject_id);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();
$subject = $subject_result->fetch_assoc();
$subject_name = $subject ? $subject['subject_name'] : 'Unknown Subject';

// Check if already enrolled
$stmt = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND subject_id = ?");
$stmt->bind_param("ii", $student_id, $subject_id);
$stmt->execute();
$result = $stmt->get_result();

$is_success = false;
$is_already_enrolled = false;

if ($result->num_rows > 0) {
    $message = "You are already enrolled in this subject.";
    $is_already_enrolled = true;
} else {
    $stmt = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $student_id, $subject_id);
    if ($stmt->execute()) {
        $message = "Enrollment successful!";
        $is_success = true;
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Status - LMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .status-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
        }

        .status-header {
            background: #2563eb;
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .status-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .status-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            z-index: 1;
            border: 4px solid rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }

        .status-icon.success {
            background: rgba(22, 163, 74, 0.2);
            border-color: rgba(22, 163, 74, 0.3);
        }

        .status-icon.warning {
            background: rgba(245, 158, 11, 0.2);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .status-icon.error {
            background: rgba(220, 38, 38, 0.2);
            border-color: rgba(220, 38, 38, 0.3);
        }

        .status-icon i {
            font-size: 42px;
            color: white;
        }

        .status-icon.success i {
            color: #16a34a;
        }

        .status-icon.warning i {
            color: #f59e0b;
        }

        .status-icon.error i {
            color: #dc2626;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .status-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .status-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .status-body {
            padding: 40px 30px;
            text-align: center;
        }

        .subject-info {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .subject-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .subject-info:hover::before {
            left: 100%;
        }

        .subject-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .subject-label i {
            margin-right: 8px;
            color: #2563eb;
        }

        .subject-name {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
        }

        .status-message {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .status-message.success {
            background: #f0fdf4;
            border: 2px solid #22c55e;
            color: #15803d;
        }

        .status-message.warning {
            background: #fffbeb;
            border: 2px solid #f59e0b;
            color: #d97706;
        }

        .status-message.error {
            background: #fef2f2;
            border: 2px solid #ef4444;
            color: #dc2626;
        }

        .status-message i {
            margin-right: 10px;
            font-size: 20px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn i {
            margin-right: 8px;
            font-size: 16px;
        }

        .btn-back {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-back:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-profile {
            background: #16a34a;
            color: white;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
        }

        .btn-profile:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.4);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        @media (max-width: 480px) {
            .status-container {
                margin: 10px;
            }
            
            .status-header {
                padding: 30px 20px 20px;
            }
            
            .status-body {
                padding: 30px 20px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .status-title {
                font-size: 24px;
            }

            .subject-name {
                font-size: 18px;
            }

            .status-message {
                font-size: 16px;
            }
        }

        /* Loading animation */
        .status-container {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .subject-info {
            animation: slideInLeft 0.5s ease-out 0.2s;
            animation-fill-mode: both;
        }

        .status-message {
            animation: slideInRight 0.5s ease-out 0.4s;
            animation-fill-mode: both;
        }

        .action-buttons {
            animation: fadeIn 0.5s ease-out 0.6s;
            animation-fill-mode: both;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="status-container">
        <div class="status-header">
            <div class="status-icon <?php echo $is_success ? 'success' : ($is_already_enrolled ? 'warning' : 'error'); ?>">
                <?php if ($is_success): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif ($is_already_enrolled): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                <?php else: ?>
                    <i class="fas fa-times-circle"></i>
                <?php endif; ?>
            </div>
            <h1 class="status-title">Enrollment Status</h1>
            <p class="status-subtitle">Subject Registration Update</p>
        </div>
        
        <div class="status-body">
            <div class="subject-info">
                <div class="subject-label">
                    <i class="fas fa-book"></i>
                    Subject
                </div>
                <div class="subject-name">
                    <?php echo htmlspecialchars($subject_name); ?>
                </div>
            </div>

            <div class="status-message <?php echo $is_success ? 'success' : ($is_already_enrolled ? 'warning' : 'error'); ?>">
                <?php if ($is_success): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif ($is_already_enrolled): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                <?php else: ?>
                    <i class="fas fa-times-circle"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($message); ?>
            </div>

            <div class="action-buttons">
                <a href="../student_management/student_subject.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Subjects
                </a>
                
                <a href="../student_management/student_profile.php" class="btn btn-profile">
                    <i class="fas fa-user"></i>
                    Go to Profile
                </a>
            </div>
        </div>
    </div>
</body>
</html>