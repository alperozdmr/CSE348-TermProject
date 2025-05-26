<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$playlistId = intval($_GET['id'] ?? $_POST['playlist_id'] ?? 0);
if ($playlistId <= 0) {
    die('Geçersiz çalma listesi ID.');
}

$host   = 'localhost';
$dbUser = 'root';
$dbPass = 'mysql';
$dbName = 'alper_ozdemir';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("DB bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_song'])) {
    $songToAdd = intval($_POST['song_id']);
    $stmt = $conn->prepare("
      INSERT IGNORE INTO PLAYLIST_SONGS (playlist_id,song_id,date_added)
      VALUES (?, ?, NOW())
    ");
    $stmt->bind_param('ii', $playlistId, $songToAdd);
    $stmt->execute();
    $stmt->close();
    header("Location: playlistpage.php?id={$playlistId}");
    exit;
}

$stmt = $conn->prepare("
    SELECT title 
    FROM PLAYLISTS 
    WHERE playlist_id = ? AND user_id = ?
");
$stmt->bind_param('ii', $playlistId, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($playlistTitle);
if (!$stmt->fetch()) {
    die('Bu liste size ait değil veya bulunamadı.');
}
$stmt->close();

$songs = [];
$stmt = $conn->prepare("
    SELECT 
      s.song_id, 
      s.title, 
      ar.name    AS artist, 
      c.country_name
    FROM PLAYLIST_SONGS ps
    JOIN SONGS   s  ON ps.song_id    = s.song_id
    JOIN ALBUMS  al ON s.album_id    = al.album_id
    JOIN ARTISTS ar ON al.artist_id  = ar.artist_id
    JOIN COUNTRY c  ON ar.country_id = c.country_id
    WHERE ps.playlist_id = ?
    ORDER BY ps.date_added DESC
");
$stmt->bind_param('i', $playlistId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $songs[] = $row;
}
$stmt->close();

$searchTerm    = trim($_GET['song'] ?? '');
$searchResults = [];
if ($searchTerm !== '') {
    $like = "%{$searchTerm}%";
    $stmt = $conn->prepare("
      SELECT song_id, title 
      FROM SONGS 
      WHERE title LIKE ?
      LIMIT 5
    ");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $searchResults[] = $r;
    }
    $stmt->close();
}

$conn->close();

require 'playlistpage.html';
