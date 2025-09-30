<?php
session_start();
include __DIR__ . '/../config/dbConnection.php';

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$student = null;
$subjects = null;
$student_id = null;

// Validate student ID from query string
if (!isset($_GET['student_id']) || !is_numeric($_GET['student_id'])) {
    $error = "Invalid student ID.";
} else {
    $student_id = intval($_GET['student_id']);

    // Fetch student details
    $stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $student = $res->fetch_assoc();

    if (!$student) {
        $error = "Student not found.";
    } else {
        // Fetch subjects for dropdown
        $subjects = $conn->query("SELECT subject_id, subject_name FROM subjects");
        if (!$subjects) {
            $error = "Could not load subjects. Please check the subjects table.";
        }
    }
}

// Handle form submission (only if no prior error)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    if (!isset($_POST['subject_id']) || !is_numeric($_POST['subject_id'])) {
        $error = "Invalid subject selected.";
    } else {
        $subject_id = intval($_POST['subject_id']);

        // Check for duplicate enrollment
        $check = $conn->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND subject_id = ?");
        $check->bind_param("ii", $student_id, $subject_id);
        $check->execute();
        $checkRes = $check->get_result();

        if ($checkRes && $checkRes->num_rows > 0) {
            $error = "This student is already enrolled in the selected subject.";
        } else {
            // Insert enrollment
            $insert = $conn->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
            $insert->bind_param("ii", $student_id, $subject_id);
            if ($insert->execute()) {
                header("Location: student_list.php?enrolled=1");
                exit();
            } else {
                $error = "Failed to enroll student. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enroll Student - LMS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #f3f6fb; min-height: 100vh; }

        .page-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .card {
            width: 100%;
            max-width: 580px;
            background: white;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 14px 40px rgba(16,24,40,0.08);
            border: 1px solid #e6eef8;
        }

        .title { font-size: 20px; font-weight: 700; color: #102a43; margin-bottom: 12px; text-align: center; }
        .subtitle { color: #475569; margin-bottom: 18px; text-align: center; }

        .error {
            background: #fff1f2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            font-weight: 600;
            margin-bottom: 16px;
            text-align: center;
        }

        .form-group { margin-bottom: 16px; }
        label { display:block; font-weight:600; color:#0f172a; margin-bottom:8px; font-size:14px; }
        select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background:#f8fafc;
            font-size: 15px;
            outline: none;
        }
        select:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 6px rgba(37,99,235,0.06); }

        .btn {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 10px;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background:#1e40af; transform: translateY(-2px); }

        .btn-secondary { background:#eef2ff; color:#0f172a; border:1px solid #e2e8f0; }
        .btn-secondary:hover { background:#e6eef8; }

        .small { font-size: 13px; color:#475569; text-align:center; margin-top:8px; }

        @media (max-width: 520px) { .card { padding: 22px; } .title { font-size: 18px; } }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="card">
            <?php if (!empty($error) && !$student): ?>
                <div class="title">Enrollment</div>
                <p class="subtitle">Please resolve the issue below</p>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <a href="student_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left" style="margin-right:8px"></i> Back to Students</a>
            <?php elseif ($student): ?>
                <div class="title">Enroll Student</div>
                <p class="subtitle"><?php echo htmlspecialchars($student['name']); ?></p>

                <?php if ($subjects && $subjects->num_rows > 0): ?>
                    <?php if (!empty($error)): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="subject_id">Select Subject</label>
                            <select name="subject_id" id="subject_id" required>
                                <option value="">-- Choose Subject --</option>
                                <?php while ($sub = $subjects->fetch_assoc()): ?>
                                    <option value="<?php echo (int)$sub['subject_id']; ?>">
                                        <?php echo htmlspecialchars($sub['subject_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Enroll</button>
                        <a href="student_list.php" class="btn btn-secondary"><i class="fas fa-times" style="margin-right:8px"></i> Cancel</a>
                    </form>
                <?php else: ?>
                    <div class="error">No subjects available to enroll. Please add subjects first.</div>
                    <a href="student_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left" style="margin-right:8px"></i> Back to Students</a>
                <?php endif; ?>
            <?php endif; ?>
            <p class="small">Database: <strong>school_management</strong></p>
        </div>
    </div>
</body>
</html>