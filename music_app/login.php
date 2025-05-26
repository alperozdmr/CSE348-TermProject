<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    die('<p>Kullanıcı adı ve şifre girilmelidir.<br><a href="login.html">Geri dön</a></p>');
}

$host     = 'localhost';
$dbUser   = 'root';
$dbPass   = 'mysql';         
$dbName   = 'alper_ozdemir';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT user_id, name, password
    FROM USERS
    WHERE username = ?
    LIMIT 1
");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    // Bulunamadıysa
    $stmt->close();
    $conn->close();
    die('<p>Geçersiz kullanıcı adı.<br><a href="login.html">Geri dön</a></p>');
}

$stmt->bind_result($userId, $fullName, $hashedPassword);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hashedPassword)) {
    $conn->close();
    die('<p>Geçersiz şifre.<br><a href="login.html">Geri dön</a></p>');
}

$_SESSION['user_id']   = $userId;
$_SESSION['user_name'] = $fullName;

$conn->close();

header('Location: homepage.php');
exit;
?>
