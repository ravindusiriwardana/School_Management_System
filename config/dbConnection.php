<?php

$host = "localhost";
$user = "root";
$pass = "root"; // empty password for default setups like XAMPP
$db   = "school_management"; // must be created manually
$port = 8889;
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
    
}
?>
