<?php
session_start();
include '../config/dbConnection.php'; // fixed path

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
        $stmt->close();
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
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background:#f9fafb;
        min-height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        position:relative;
    }

    /* Top accent bar */
    body::before {
        content:'';
        position:fixed;
        top:0; left:0; right:0;
        height:4px;
        background:#2563eb;
        z-index:1000;
    }

    .container {
        background:white;
        width:100%;
        max-width:500px;
        padding:50px 40px;
        border-radius:16px;
        box-shadow:0 20px 60px rgba(0,0,0,0.1);
        border:2px solid #16a34a;
        position:relative;
        animation:slideIn 0.5s ease-out;
    }

    .header { text-align:center; margin-bottom:40px; }
    .header h2 { color:#111827; font-size:28px; font-weight:600; margin-bottom:8px; }
    .header p { color:#6b7280; font-size:16px; }

    .error-message {
        background:#fee2e2;
        color:#b91c1c;
        padding:12px 16px;
        border-radius:8px;
        margin-bottom:24px;
        border:1px solid #b91c1c;
        font-size:14px;
        font-weight:500;
    }

    .form-group { margin-bottom:24px; }
    .form-label { display:block; margin-bottom:8px; color:#111827; font-weight:600; font-size:14px; text-transform:uppercase; letter-spacing:0.5px; }
    .form-input {
        width:100%;
        padding:16px;
        border:2px solid #e5e7eb;
        border-radius:8px;
        font-size:16px;
        transition:all 0.3s ease;
        background:white;
        color:#111827;
    }
    .form-input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
    .form-input::placeholder { color:#9ca3af; }

    .button-group { display:flex; flex-direction:column; gap:16px; margin-top:32px; }
    .submit-button {
        width:100%;
        padding:16px;
        background:#16a34a;
        color:white;
        border:none;
        border-radius:8px;
        font-size:16px;
        font-weight:600;
        cursor:pointer;
        transition:all 0.3s ease;
        text-transform:uppercase;
        letter-spacing:0.5px;
    }
    .submit-button:hover {
        background:#2563eb;
        transform:translateY(-2px);
        box-shadow:0 8px 25px rgba(37,99,235,0.3);
    }
    .submit-button:active { transform:translateY(0); }

    .back-link {
        display:inline-block;
        width:100%;
        padding:14px;
        text-align:center;
        color:#2563eb;
        text-decoration:none;
        border:2px solid #2563eb;
        border-radius:8px;
        font-weight:600;
        transition:all 0.3s ease;
        font-size:14px;
        text-transform:uppercase;
        letter-spacing:0.5px;
    }
    .back-link:hover {
        background:#2563eb;
        color:white;
        transform:translateY(-2px);
        box-shadow:0 8px 25px rgba(37,99,235,0.2);
    }

    .success-indicator {
        width:24px;
        height:24px;
        background:#16a34a;
        border-radius:50%;
        position:absolute;
        top:20px;
        right:20px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:white;
        font-size:12px;
        font-weight:bold;
    }

    .icon { display:inline-block; width:20px; height:20px; margin-right:8px; vertical-align:middle; }
    .subject-icon { background:#16a34a; border-radius:4px; position:relative; }
    .subject-icon::after {
        content:'📚';
        position:absolute;
        top:50%; left:50%;
        transform:translate(-50%,-50%);
        font-size:12px;
    }

    @media (max-width:600px) {
        .container { margin:20px; padding:30px 24px; }
        .header h2 { font-size:24px; }
    }

    @keyframes slideIn { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
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
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="subject_name">Subject Name</label>
            <input type="text" id="subject_name" name="subject_name" class="form-input" placeholder="Enter subject name (e.g., Mathematics, Science, English)" required maxlength="100">
        </div>

        <div class="button-group">
            <input type="submit" name="add" value="Add Subject" class="submit-button">
            <a href="subject_list.php" class="back-link">← Back to Subjects</a>
        </div>
    </form>
</div>
</body>
</html>