<?php
header('Content-Type: application/json');
require '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Parameter wajib
if(!isset($input['msisdn']) || !isset($input['client_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
    exit;
}

// Cari Client Code berdasarkan Client Name (Generate jika belum ada bisa ditambahkan, tapi sesuai flow, input client dulu)
$client_name = $input['client_name'];
$stmtC = $conn->prepare("SELECT client_code FROM clients WHERE client_name = ?");
$stmtC->bind_param("s", $client_name);
$stmtC->execute();
$resC = $stmtC->get_result();

if($resC->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Client Name not found. Please create client first.']);
    exit;
}
$client_row = $resC->fetch_assoc();
$client_code = $client_row['client_code'];

// Insert SIM
$stmt = $conn->prepare("INSERT INTO simcards (msisdn, imsi, iccid, sn, client_code, data_package) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $input['msisdn'], $input['imsi'], $input['iccid'], $input['sn'], $client_code, $input['data_package']);

if($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'SIM Data Inserted']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>