<?php
include '../dbConnection.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No teacher ID provided.";
    exit();
}

// Fetch teacher data
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Teacher not found.";
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $subject = $_POST['subject'];
    $email = $_POST['email'];

    $updateStmt = $conn->prepare("UPDATE teachers SET name=?, age=?, subject=?, email=? WHERE id=?");
    $updateStmt->bind_param("sissi", $name, $age, $subject, $email, $id);

    if ($updateStmt->execute()) {
        header("Location: teachers_list.php"); // redirect to the correct page
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Teacher</title>
</head>
<body>
<h2>Edit Teacher</h2>
<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required><br>
    <label>Age:</label>
    <input type="number" name="age" value="<?php echo $row['age']; ?>" required><br>
    <label>Subject:</label>
    <input type="text" name="subject" value="<?php echo htmlspecialchars($row['subject']); ?>" required><br>
    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>
    <button type="submit" name="update">Update</button>
</form>
<a href="teachers_list.php">Back to Teacher List</a>
</body>
</html>