<?php include '../dbConnection.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Students List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Students</h2>
    <a href="add_student.php">Add Student</a>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Grade</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM students");
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['id']."</td>
                    <td>".$row['name']."</td>
                    <td>".$row['age']."</td>
                    <td>".$row['grade']."</td>
                    <td>".$row['email']."</td>
                    <td>
                        <a href='edit_student.php?id=".$row['id']."'>Edit</a> | 
                        <a href='delete_student.php?id=".$row['id']."' onclick=\"return confirm('Are you sure?');\">Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>