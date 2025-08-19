<?php
include '../dbConnection.php';

// Get student ID from URL
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No student ID provided.";
    exit();
}

// Fetch current student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Student not found.";
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $name  = $_POST['name'];
    $age   = $_POST['age'];
    $grade = $_POST['grade'];
    $email = $_POST['email'];

    $updateStmt = $conn->prepare("UPDATE students SET name=?, age=?, grade=?, email=? WHERE id=?");
    $updateStmt->bind_param("sissi", $name, $age, $grade, $email, $id);

    if ($updateStmt->execute()) {
        header("Location: student_list.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
</head>
<body>
    <h2>Edit Student</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required><br>
        
        <label>Age:</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($row['age']); ?>" required><br>
        
        <label>Grade:</label>
        <input type="text" name="grade" value="<?php echo htmlspecialchars($row['grade']); ?>" required><br>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>
        
        <button type="submit" name="update">Update</button>
    </form>
    <a href="student_list.php">Back</a>
</body>
</html>