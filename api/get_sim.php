<?php
header('Content-Type: application/json');
require '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$msisdn = $input['msisdn'] ?? '';

$stmt = $conn->prepare("
    SELECT s.imsi, s.iccid, s.sn, c.client_name, s.data_package 
    FROM simcards s 
    JOIN clients c ON s.client_code = c.client_code 
    WHERE s.msisdn = ?
");
$stmt->bind_param("s", $msisdn);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode($result ? $result : ['message' => 'Not Found']);
?>