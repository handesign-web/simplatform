<?php
header('Content-Type: application/json');
require '../config/db.php';

// Menerima input JSON atau Form Data
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? $_POST['user_id'] ?? '';

if(empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required']);
    exit;
}

$stmt = $conn->prepare("SELECT api_key, secret_key FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if($row = $res->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>