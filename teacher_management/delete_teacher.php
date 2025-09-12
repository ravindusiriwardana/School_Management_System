<?php
include '../config/dbConnection.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No teacher ID provided.";
    exit();
}

// Prepare delete statement
$stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirect to the correct teacher list page
    header("Location: /teacher_management/teachers_list.php");
    exit();
} else {
    echo "Error deleting teacher: " . $conn->error;
}
?>