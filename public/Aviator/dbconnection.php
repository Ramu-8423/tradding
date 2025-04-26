<?php

$servername = "localhost";
$username = "u873167744_fomoplay";
$password = "u873167744_Fomoplay";
$dbname = "u873167744_fomoplay";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

