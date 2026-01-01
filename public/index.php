<?php
session_start();
require '../config/db.php';

// Generate Random String
function generateKey($length = 32) {
    return bin2hex(random_bytes($length));
}

// Logic Register
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $api_key = generateKey(16); // 32 chars
    $secret_key = generateKey(32); // 64 chars

    $stmt = $conn->prepare("INSERT INTO users (username, password, api_key, secret_key) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $api_key, $secret_key);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        echo "<script>alert('Regis Berhasil! User ID: $user_id, API Key: $api_key, Secret: $secret_key');</script>";
    } else {
        echo "<script>alert('Username sudah ada');</script>";
    }
}

// Logic Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit;
        }
    }
    echo "<script>alert('Login Gagal');</script>";
}
?>

<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-5">
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Login</h3>
            <form method="POST">
                <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
        </div>
        <div class="col-md-6">
            <h3>Register</h3>
            <form method="POST">
                <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                <button type="submit" name="register" class="btn btn-success">Register</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>