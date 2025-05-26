<?php


session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$songId = intval($_GET['id'] ?? 0);
if ($songId <= 0) {
    die('Geçersiz şarkı ID.');
}

$host   = 'localhost';
$dbUser = 'root';
$dbPass = 'mysql';
$dbName = 'alper_ozdemir';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("DB bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['play'])) {
    $stmt = $conn->prepare("
        INSERT INTO PLAY_HISTORY (user_id, song_id, playtime)
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param('ii', $_SESSION['user_id'], $songId);
    $stmt->execute();
    $stmt->close();
}

$stmt = $conn->prepare("
    SELECT 
      s.title        AS song_title,
      s.duration     AS song_duration,
      al.name        AS album_name,
      al.image       AS album_image,
      ar.artist_id   AS artist_id,
      ar.name        AS artist_name
    FROM SONGS s
    JOIN ALBUMS al ON s.album_id = al.album_id
    JOIN ARTISTS ar ON al.artist_id = ar.artist_id
    WHERE s.song_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $songId);
$stmt->execute();
$stmt->bind_result(
    $songTitle,
    $songDuration,
    $albumName,
    $albumImage,
    $artistId,
    $artistName
);
if (!$stmt->fetch()) {
    die('Şarkı bulunamadı.');
}
$stmt->close();
$conn->close();

require 'currentmusic.html';
