<?php
include '../dbConnection.php';

$result = $conn->query("SELECT * FROM teachers");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher List</title>
</head>
<body>
<h2>Teacher List</h2>
<a href="add_teachers.php">Add New Teacher</a>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Age</th>
        <th>Subject</th>
        <th>Email</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo $row['age']; ?></td>
        <td><?php echo htmlspecialchars($row['subject']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td>
            <a href="edit_teacher.php?id=<?php echo $row['id']; ?>">Edit</a> | 
            <a href="delete_teacher.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>
</body>
</html>