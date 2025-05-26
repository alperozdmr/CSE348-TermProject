<?php


$servername = "localhost";
$dbUser     = "root";
$dbPassword = "mysql";      
$dbName     = "alper_ozdemir";

$conn = new mysqli($servername, $dbUser, $dbPassword);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS `$dbName`
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

if (! $conn->select_db($dbName)) {
    die("Error selecting database: " . $conn->error);
}


$createQueries = [

  "CREATE TABLE IF NOT EXISTS `COUNTRY` (
     `country_id`   INT AUTO_INCREMENT PRIMARY KEY,
     `country_name` VARCHAR(100) NOT NULL,
     `country_code` VARCHAR(5)  NOT NULL
   ) ",

  "CREATE TABLE IF NOT EXISTS `USERS` (
     `user_id`            INT AUTO_INCREMENT PRIMARY KEY,
     `country_id`         INT NOT NULL,
     `age`                INT,
     `name`               VARCHAR(100),
     `username`           VARCHAR(50)  UNIQUE,
     `email`              VARCHAR(100) UNIQUE,
     `password`           VARCHAR(255),
     `date_joined`        DATETIME,
     `last_login`         DATETIME,
     `follower_num`       INT DEFAULT 0,
     `subscription_type`  VARCHAR(50),
     `top_genre`          VARCHAR(50),
     `num_songs_liked`    INT DEFAULT 0,
     `most_played_artist` INT,
     `image`              VARCHAR(255),
     FOREIGN KEY (`country_id`) REFERENCES `COUNTRY`(`country_id`)
   ) ",

  "CREATE TABLE IF NOT EXISTS `ARTISTS` (
     `artist_id`       INT AUTO_INCREMENT PRIMARY KEY,
     `name`            VARCHAR(100),
     `genre`           VARCHAR(50),
     `date_joined`     DATETIME,
     `total_num_music` INT,
     `total_albums`    INT,
     `listeners`       INT,
     `bio`             TEXT,
     `country_id`      INT,
     `image`           VARCHAR(255),
     FOREIGN KEY (`country_id`) REFERENCES `COUNTRY`(`country_id`)
   ) ",

  "CREATE TABLE IF NOT EXISTS `ALBUMS` (
     `album_id`     INT AUTO_INCREMENT PRIMARY KEY,
     `artist_id`    INT,
     `name`         VARCHAR(100),
     `release_date` DATE,
     `genre`        VARCHAR(50),
     `music_number` INT,
     `image`        VARCHAR(255),
     FOREIGN KEY (`artist_id`) REFERENCES `ARTISTS`(`artist_id`)
   ) ",

  "CREATE TABLE IF NOT EXISTS `SONGS` (
     `song_id`      INT AUTO_INCREMENT PRIMARY KEY,
     `album_id`     INT,
     `title`        VARCHAR(100),
     `duration`     TIME,
     `genre`        VARCHAR(50),
     `release_date` DATE,
     `rank`         INT,
     `image`        VARCHAR(255),
     FOREIGN KEY (`album_id`) REFERENCES `ALBUMS`(`album_id`)
   ) ",

  "CREATE TABLE IF NOT EXISTS `PLAY_HISTORY` (
     `play_id`  INT AUTO_INCREMENT PRIMARY KEY,
     `user_id`  INT,
     `song_id`  INT,
     `playtime` DATETIME,
     FOREIGN KEY (`user_id`) REFERENCES `USERS`(`user_id`),
     FOREIGN KEY (`song_id`) REFERENCES `SONGS`(`song_id`)
   ) ",

  "CREATE TABLE IF NOT EXISTS `PLAYLISTS` (
     `playlist_id` INT AUTO_INCREMENT PRIMARY KEY,
     `user_id`     INT,
     `title`       VARCHAR(100),
     `description` TEXT,
     `date_created`DATETIME,
     `image`       VARCHAR(255),
     FOREIGN KEY (`user_id`) REFERENCES `USERS`(`user_id`)
   ) ",

  "CREATE TABLE IF NOT EXISTS `PLAYLIST_SONGS` (
     `playlistsong_id` INT AUTO_INCREMENT PRIMARY KEY,
     `playlist_id`     INT,
     `song_id`         INT,
     `date_added`      DATETIME,
     FOREIGN KEY (`playlist_id`) REFERENCES `PLAYLISTS`(`playlist_id`),
     FOREIGN KEY (`song_id`)     REFERENCES `SONGS`(`song_id`)
   ) "
];

foreach ($createQueries as $query) {
    if ($conn->query($query) === FALSE) {
        die("Error creating table: " . $conn->error);
    }
}

if (file_exists('output.sql')) {
    $conn->query('SET FOREIGN_KEY_CHECKS = 0;');

    $tables = [
        'PLAYLIST_SONGS',
        'PLAYLISTS',
        'PLAY_HISTORY',
        'SONGS',
        'ALBUMS',
        'ARTISTS',
        'USERS',
        'COUNTRY'
    ];
    foreach ($tables as $tbl) {
        $conn->query("TRUNCATE TABLE `$tbl`");
    }

    $conn->query('SET FOREIGN_KEY_CHECKS = 1;');

    $allSql = file_get_contents('output.sql');
    $allSql = str_replace(
        'release_date,rank,image',
        'release_date,`rank`,image',
        $allSql
    );
    if ($conn->multi_query($allSql)) {
        do {} while ($conn->more_results() && $conn->next_result());
    } else {
        die("Error inserting initial data: " . $conn->error);
    }
}

header('Location: login.html');
exit;
?>
