<?php
include '../dbConnection.php';

// Get student ID from URL
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No student ID provided.";
    exit();
}

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: student_list.php");
    exit();
} else {
    echo "Error deleting record: " . $conn->error;
}
?>