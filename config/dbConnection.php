<?php
$host = "127.0.0.1";   // use IP instead of "localhost" to avoid socket issues
$user = "root";        // or Ravindu if you created a custom user
$pass = "Ravindu18007";            // set this to your MySQL password (check Workbench)
$db   = "school_management";
$port = 3306;          // important for your setup

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
    // echo "Connected successfully";
}
?>
