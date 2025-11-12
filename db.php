<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "whiskerlink"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Remove the echo statement - you don't want this showing on every page
// echo "Connected successfully";

// Don't close the connection here - let the including script manage it
?>