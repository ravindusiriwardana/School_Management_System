<?php
session_start();

// Fix paths according to your folder structure
include __DIR__ . '/../config/dbConnection.php';

// Only allow teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $grade = $_POST['grade'];
    $email = $_POST['email'];

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Student already enrolled with this email.";
    } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO students (name, age, grade, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $age, $grade, $email);

        if ($stmt->execute()) {
            $success = "Student successfully added!";
            // Optionally, redirect to student list:
            // header("Location: student_list.php");
            // exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Student - LMS</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; min-height: 100vh; }

.navbar-container { position: fixed; top: 0; width: 100%; z-index: 1000; background: #1e293b; }

.page-wrapper { padding-top: 80px; display: flex; align-items: center; justify-content: center; padding-bottom: 40px; }

.form-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    max-width: 500px;
    width: 100%;
    padding: 40px 30px;
    animation: fadeInUp 0.6s ease-out;
}

.form-header { text-align: center; margin-bottom: 30px; }
.form-title { font-size: 28px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
.form-subtitle { font-size: 16px; color: #6b7280; }

.form-group { margin-bottom: 25px; position: relative; }
label { display: flex; align-items: center; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
label i { margin-right: 8px; width: 16px; color: #2563eb; }

input {
    width: 100%;
    padding: 16px 18px;
    font-size: 16px;
    color: #1e293b;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    outline: none;
    transition: all 0.3s ease;
}
input:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
input:hover { border-color: #2563eb; }

.error-message { color: #dc2626; font-size: 14px; margin-top: 8px; display: <?php echo isset($error) ? 'block' : 'none'; ?>; }
.success-message { color: #16a34a; font-size: 14px; margin-top: 8px; display: <?php echo isset($success) ? 'block' : 'none'; ?>; }

.btn {
    display: flex; align-items: center; justify-content: center;
    width: 100%; padding: 14px 20px; border-radius: 12px;
    font-weight: 600; font-size: 14px; text-transform: uppercase;
    letter-spacing: 0.5px; transition: all 0.3s ease; position: relative;
    overflow: hidden; background: #16a34a; color: white; box-shadow: 0 4px 15px rgba(22,163,74,0.3);
    border: none; cursor: pointer;
}
.btn i { margin-right: 8px; font-size: 16px; }
.btn:hover { background: #15803d; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(22,163,74,0.4); }

.back-link { display: inline-flex; align-items: center; margin-top: 20px; text-decoration: none; color: #2563eb; font-weight: 600; font-size: 14px; transition: all 0.3s ease; }
.back-link i { margin-right: 8px; }
.back-link:hover { color: #1e40af; }

@media (max-width: 480px) { .form-container { margin: 10px; padding: 30px 20px; } .form-title { font-size: 24px; } }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar-container">
    <?php include __DIR__ . '/../navbar.php'; ?>
</div>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Add New Student</h2>
            <p class="form-subtitle">Learning Management System</p>
        </div>
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Age</label>
                <input type="number" name="age" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-graduation-cap"></i> Grade</label>
                <input type="text" name="grade" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" required>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <button type="submit" name="submit" class="btn">
                <i class="fas fa-user-plus"></i> Add Student
            </button>
        </form>
        <a href="student_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Student List</a>
    </div>
</div>

</body>
</html>