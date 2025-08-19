<?php
session_start();
include '../dbConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: subject_list.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch subject
$stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    echo "Subject not found";
    exit();
}

// Handle update
if (isset($_POST['update'])) {
    $name = trim($_POST['subject_name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE subjects SET subject_name = ? WHERE subject_id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        header("Location: subject_list.php"); // Redirect to subject_list.php
        exit();
    } else {
        $error = "Subject name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Subject</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; margin: 0; padding: 0; }
        .container { width: 400px; margin: 100px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        input[type="text"] { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ddd; }
        input[type="submit"] { padding: 10px 20px; background-color: #f1c40f; color: #333; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; }
        h2 { text-align: center; margin-bottom: 20px; }
        a { display: block; margin-top: 15px; text-align: center; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Subject</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST">
            <label>Subject Name</label>
            <input type="text" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
            <input type="submit" name="update" value="Update Subject">
        </form>
        <a href="subject_list.php">Back to Subjects</a>
    </div>
</body>
</html>