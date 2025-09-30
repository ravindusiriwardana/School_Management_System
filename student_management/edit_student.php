<?php
session_start();
include '../config/dbConnection.php';
include __DIR__ . '/../navbar.php';

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Get student ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID.");
}
$student_id = intval($_GET['id']);

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $age = intval($_POST['age']);
    $grade = trim($_POST['grade']);

    // Validate inputs
    if (!empty($name) && !empty($email) && $age >= 16 && $age <= 25 && !empty($grade)) {
        $stmt = $conn->prepare("UPDATE students SET name=?, email=?, age=?, grade=? WHERE id=?");
        $stmt->bind_param("ssisi", $name, $email, $age, $grade, $student_id);

        if ($stmt->execute()) {
            header("Location: student_list.php?success=1");
            exit();
        } else {
            $error = "Failed to update student. Please try again.";
        }
    } else {
        $error = "Please fill all fields correctly.";
    }
}

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - LMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Navbar fixed at top */
        .navbar-container {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: #1e293b;
        }

        /* Page wrapper for spacing below navbar */
        .page-wrapper {
            padding-top: 80px; /* Space for navbar */
            display: flex;
            justify-content: center;
            align-items: center;
            padding-bottom: 40px;
        }

        .edit-container {
            width: 100%;
            max-width: 600px;
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            animation: fadeInUp 0.6s ease-out;
        }

        .edit-title {
            font-size: 26px;
            margin-bottom: 20px;
            text-align: center;
            color: #1e293b;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #1e293b;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            background: #f9fafb;
            transition: border 0.3s ease;
        }

        .form-input:focus {
            border-color: #2563eb;
            outline: none;
            background: white;
        }

        .btn {
            padding: 12px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #1e293b;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }

        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 600;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar-container">
        <?php include __DIR__ . '/../navbar.php'; ?>
    </div>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="edit-container">
            <h2 class="edit-title">Edit Student</h2>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input type="number" name="age" class="form-input" value="<?php echo htmlspecialchars($student['age']); ?>" required min="16" max="25">
                </div>

                <div class="form-group">
                    <label class="form-label">Grade/Class</label>
                    <input type="text" name="grade" class="form-input" value="<?php echo htmlspecialchars($student['grade']); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="student_list.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>