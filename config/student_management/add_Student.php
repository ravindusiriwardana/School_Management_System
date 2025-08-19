<?php 
include '../dbConnection.php'; 

if (isset($_POST['submit'])) {
    $name  = $_POST['name'];
    $age   = $_POST['age'];
    $grade = $_POST['grade'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password

    $stmt = $conn->prepare("INSERT INTO students (name, age, grade, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $name, $age, $grade, $email, $password);

    if ($stmt->execute()) {
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
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Add New Student</h2>
    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" required><br>
        
        <label>Age:</label>
        <input type="number" name="age" required><br>
        
        <label>Grade:</label>
        <input type="text" name="grade" required><br>
        
        <label>Email:</label>
        <input type="email" name="email" required><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br>
        
        <button type="submit" name="submit">Add Student</button>
    </form>

    <a href="student_list.php">Back to Student List</a>
</body>
</html>