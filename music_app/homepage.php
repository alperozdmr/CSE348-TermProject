<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.html');
    exit;
}

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$host   = 'localhost';
$dbUser = 'root';
$dbPass = 'mysql';
$dbName = 'alper_ozdemir';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("DB bağlantı hatası: " . $conn->connect_error);
}

$searchError = '';
if (isset($_GET['song_search'])) {
    $term = trim($_GET['song_search']);
    if ($term !== '') {
        $like = "%{$term}%";
        $stmt = $conn->prepare("
          SELECT s.song_id
          FROM SONGS s
          JOIN ALBUMS al ON s.album_id=al.album_id
          JOIN ARTISTS ar ON al.artist_id=ar.artist_id
          WHERE s.title LIKE ? OR ar.name LIKE ?
          LIMIT 1
        ");
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $stmt->bind_result($foundSong);
        if ($stmt->fetch()) {
            header("Location: currentmusic.php?id={$foundSong}");
            exit;
        } else {
            $searchError = 'Aranan şarkı veya sanatçı bulunamadı.';
        }
        $stmt->close();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_playlist'])) {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($title === '') {
        $_SESSION['playlist_error'] = 'Playlist başlığı boş bırakılamaz.';
    } else {
         $seed = rawurlencode(strtolower(str_replace(' ', '_', $name)));
    
        $img  = "https://picsum.photos/seed/{$seed}/300/300";


        $stmt = $conn->prepare("
            INSERT INTO PLAYLISTS (user_id, title, description, date_created, image)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param('isss', $_SESSION['user_id'], $title, $description, $img);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: homepage.php");
    exit;
}
$artistError = '';
if (isset($_GET['artist_search'])) {
    $term = trim($_GET['artist_search']);
    if ($term !== '') {
        $like = "%{$term}%";
        $stmt = $conn->prepare("
          SELECT artist_id
          FROM ARTISTS
          WHERE name LIKE ?
          LIMIT 1
        ");
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $stmt->bind_result($foundArtist);
        if ($stmt->fetch()) {
            header("Location: artistpage.php?id={$foundArtist}");
            exit;
        } else {
            $artistError = 'Aranan sanatçı bulunamadı.';
        }
        $stmt->close();
    }
}

$playlists = [];
$stmt = $conn->prepare("SELECT playlist_id, title, image FROM PLAYLISTS WHERE user_id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $playlists[] = $row;
}
$stmt->close();

$history = [];
$stmt = $conn->prepare("
    SELECT p.song_id, s.title
    FROM PLAY_HISTORY p
    JOIN SONGS s ON p.song_id=s.song_id
    WHERE p.user_id=?
    ORDER BY p.playtime DESC
    LIMIT 10
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();

$stmt = $conn->prepare("SELECT country_id FROM USERS WHERE user_id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($countryId);
$stmt->fetch();
$stmt->close();

$artists = [];
$stmt = $conn->prepare("
    SELECT artist_id, name, image
    FROM ARTISTS
    WHERE country_id=?
    ORDER BY listeners DESC
    LIMIT 5
");
$stmt->bind_param('i', $countryId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $artists[] = $row;
}
$stmt->close();

$conn->close();

require 'homepage.html';
