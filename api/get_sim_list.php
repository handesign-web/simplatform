<?php
header('Content-Type: application/json');
require '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$code = $input['Client Code'] ?? ''; // Sesuai request body: Client Code

$stmt = $conn->prepare("SELECT msisdn, iccid, sn, data_package FROM simcards WHERE client_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while($row = $res->fetch_assoc()) $data[] = $row;

echo json_encode($data);
?>