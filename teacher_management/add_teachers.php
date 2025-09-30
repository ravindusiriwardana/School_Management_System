<?php
include '../config/dbConnection.php';


if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $subject = $_POST['subject'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password

    $stmt = $conn->prepare("INSERT INTO teachers (name, age, subject, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $name, $age, $subject, $email, $password);

    if ($stmt->execute()) {
        header("Location: teachers_list.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
</head>
<body>
<h2>Add New Teacher</h2>
<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" required><br>
    <label>Age:</label>
    <input type="number" name="age" required><br>
    <label>Subject:</label>
    <input type="text" name="subject" required><br>
    <label>Email:</label>
    <input type="email" name="email" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit" name="add">Add Teacher</button>
</form>
<a href="teachers_list.php">Back to Teacher List</a>
</body>
</html>