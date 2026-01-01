<?php
session_start();
require '../config/db.php';
// require '../vendor/autoload.php'; // Load PhpSpreadsheet

if (!isset($_SESSION['user_id'])) header("Location: index.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

// 1. Logic Input Client (Auto Code)
if (isset($_POST['add_client'])) {
    $name = $_POST['client_name'];
    // Generate Code: 3 huruf pertama Upper + 3 angka random
    $code = strtoupper(substr(str_replace(' ', '', $name), 0, 3)) . rand(100, 999);
    
    $stmt = $conn->prepare("INSERT INTO clients (client_name, client_code) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $code);
    $stmt->execute();
}

// 2. Logic Input SIM Manual
if (isset($_POST['add_sim'])) {
    // Ambil data dari POST
    $msisdn = $_POST['msisdn'];
    $imsi = $_POST['imsi'];
    $iccid = $_POST['iccid'];
    $sn = $_POST['sn'];
    $client_code = $_POST['client_code'];
    $pkg = $_POST['package'];
    
    $stmt = $conn->prepare("INSERT INTO simcards (msisdn, imsi, iccid, sn, client_code, data_package) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $msisdn, $imsi, $iccid, $sn, $client_code, $pkg);
    $stmt->execute();
}

// 3. Logic Import Excel
if (isset($_POST['import_excel'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $data = $spreadsheet->getActiveSheet()->toArray();
    
    // Asumsi baris 1 adalah Header. Loop mulai index 1
    foreach($data as $key => $row) {
        if($key == 0) continue; 
        $msisdn = $row[0];
        $imsi = $row[1];
        $iccid = $row[2];
        $sn = $row[3];
        $client_code = $row[4]; // Harus kode yg sudah ada
        $pkg = $row[5];
        
        $stmt = $conn->prepare("INSERT IGNORE INTO simcards (msisdn, imsi, iccid, sn, client_code, data_package) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $msisdn, $imsi, $iccid, $sn, $client_code, $pkg);
        $stmt->execute();
    }
    echo "<script>alert('Import Selesai');</script>";
}

// Ambil list client untuk dropdown
$clients = $conn->query("SELECT * FROM clients");
?>

<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4">
<div class="container">
    <h2>Dashboard</h2>
    <hr>
    
    <div class="card mb-4">
        <div class="card-header">1. Input Client</div>
        <div class="card-body">
            <form method="POST">
                <input type="text" name="client_name" class="form-control mb-2" placeholder="Nama Client" required>
                <button type="submit" name="add_client" class="btn btn-primary">Generate Code & Save</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">2. Input SIM Card (Manual)</div>
        <div class="card-body">
            <form method="POST">
                <select name="client_code" class="form-control mb-2" required>
                    <option value="">Pilih Client Code</option>
                    <?php while($c = $clients->fetch_assoc()): ?>
                        <option value="<?= $c['client_code'] ?>"><?= $c['client_name'] ?> (<?= $c['client_code'] ?>)</option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="msisdn" placeholder="MSISDN" class="form-control mb-2">
                <input type="text" name="imsi" placeholder="IMSI" class="form-control mb-2">
                <input type="text" name="iccid" placeholder="ICCID" class="form-control mb-2">
                <input type="text" name="sn" placeholder="SN" class="form-control mb-2">
                <input type="text" name="package" placeholder="Data Package" class="form-control mb-2">
                <button type="submit" name="add_sim" class="btn btn-success">Simpan SIM</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">3. Import Excel</div>
        <div class="card-body">
            <p>Format: A=MSISDN, B=IMSI, C=ICCID, D=SN, E=Client Code, F=Package</p>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="excel_file" class="form-control mb-2" required>
                <button type="submit" name="import_excel" class="btn btn-warning">Import</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>