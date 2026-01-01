<?php
$host = 'localhost';
$db   = 'sim_platform';
$user = 'sim_platform'; // Sesuaikan user db aaPanel
$pass = 'Kumisan5'; // Sesuaikan pass db aaPanel

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>