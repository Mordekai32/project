<?php
$conn = new mysqli("localhost", "root", "", "mordekai");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
