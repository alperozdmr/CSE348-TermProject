<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$artistId = intval($_GET['id'] ?? 0);
if ($artistId <= 0) {
    die('Geçersiz sanatçı ID.');
}

$conn = new mysqli('localhost','root','mysql','alper_ozdemir');
if ($conn->connect_error) {
    die("DB bağlantı hatası: " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT name, genre, date_joined, bio, image
    FROM ARTISTS
    WHERE artist_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $artistId);
$stmt->execute();
$stmt->bind_result($artistName, $artistGenre, $artistJoined, $artistBio, $artistImage);
if (!$stmt->fetch()) {
    die('Sanatçı bulunamadı.');
}
$stmt->close();

$albums = [];
$stmt = $conn->prepare("
    SELECT album_id, name, release_date, image
    FROM ALBUMS
    WHERE artist_id = ?
    ORDER BY release_date DESC
    LIMIT 5
");
$stmt->bind_param('i', $artistId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $albums[] = $row;
}
$stmt->close();

$songs = [];
$stmt = $conn->prepare("
    SELECT s.song_id, s.title
    FROM SONGS s
    JOIN ALBUMS al ON s.album_id = al.album_id
    WHERE al.artist_id = ?
    ORDER BY s.`rank` DESC
    LIMIT 5
");
$stmt->bind_param('i', $artistId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $songs[] = $row;
}
$stmt->close();

$conn->close();

require 'artistpage.html';
