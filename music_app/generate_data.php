<?php

// Kaç kayıt üreteceğimizi tanımlayalım:
const NUM_USERS        = 100;
const NUM_ARTISTS      = 100;
const NUM_ALBUMS       = 200;
const NUM_SONGS        = 1000;
const NUM_PLAYHISTORY  = 100;
const NUM_PLAYLISTS    = 500;
const NUM_PLAYLISTSONG = 500;

// 1) Yardımcı fonksiyon: bir .txt dosyasını satır satır okur ve dizesel dizi döner.
function loadList(string $filename): array {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_map('trim', $lines);
}

// 2) Tüm input listelerini yükleme
$names        = loadList(__DIR__.'/data/names.txt');
$surnames     = loadList(__DIR__.'/data/surnames.txt');
$countries    = loadList(__DIR__.'/data/countries.txt');      
$genres       = loadList(__DIR__.'/data/genres.txt');
$artistNames  = loadList(__DIR__.'/data/artist_names.txt');
$albumTitles  = loadList(__DIR__.'/data/album_titles.txt');
$songTitles   = loadList(__DIR__.'/data/song_titles.txt');
$playlistTitles = loadList(__DIR__.'/data/playlist_titles.txt');
$descriptions = loadList(__DIR__.'/data/descriptions.txt');

function randomDate(string $start, string $end): string {
    $min = strtotime($start);
    $max = strtotime($end);
    $ts  = rand($min, $max);
    return date('Y-m-d H:i:s', $ts);
}


$output = fopen(__DIR__.'/output.sql', 'w');
if (!$output) {
    die("output.sql oluşturulamadı.\n");
}


fwrite($output, "-- COUNTRY kayıtları\n");
$id = 1;
foreach ($countries as $cLine) {
    
    list($cName, $cCode) = array_map('trim', explode(';', $cLine));
    $sql = sprintf(
        "INSERT INTO COUNTRY (country_id,country_name,country_code) VALUES (%d, '%s', '%s');\n",
        $id++, addslashes($cName), addslashes($cCode)
    );
    fwrite($output, $sql);
}

// 6) USERS tablosu
fwrite($output, "\n-- USERS kayıtları\n");
for ($i = 1; $i <= NUM_USERS; $i++) {
    $first = $names[array_rand($names)];
    $last  = $surnames[array_rand($surnames)];
    $full  = "$first $last";
    // $user  = strtolower($first.'.'.$last);
    // $email = strtolower($first.'.'.$last).'@example.com';
    $baseUser = strtolower($first . '.' . $last);
    // Sonuna ID ekleyerek kesinlikle unique yap
    $username = $baseUser . $i;

    // Email de username üzerinden oluşsun
    $email = $username . '@example.com';
    $countryId = rand(1, count($countries));
    $age = rand(18, 60);
    $joined = randomDate('2018-01-01','2025-05-17');
    $lastLogin = randomDate($joined, '2025-05-17');
    $follower = rand(0, 1000);
    $subType = ['free','premium'][rand(0,1)];
    $topGenre = $genres[array_rand($genres)];
    $liked = rand(0, 200);
    $mostArtist = rand(1, NUM_ARTISTS);
    $img = "user{$i}.jpg";


    $sql = sprintf(
        "INSERT INTO USERS (user_id,country_id,age,name,username,email,password,date_joined,last_login,follower_num,subscription_type,top_genre,num_songs_liked,most_played_artist,image) VALUES
         (%d,%d,%d,'%s','%s','%s','%s','%s','%s',%d,'%s','%s',%d,%d,'%s');\n",
        $i, $countryId, $age, addslashes($full), addslashes($username),
        addslashes($email), password_hash('password', PASSWORD_DEFAULT),
        $joined, $lastLogin, $follower, $subType,
        addslashes($topGenre), $liked, $mostArtist, $img
    );
    fwrite($output, $sql);
}

// 7) ARTISTS tablosu
fwrite($output, "\n-- ARTISTS kayıtları\n");
for ($i = 1; $i <= NUM_ARTISTS; $i++) {
    $name      = addslashes($artistNames[array_rand($artistNames)]);
    $genre     = addslashes($genres[array_rand($genres)]);
    $joined    = randomDate('2015-01-01','2025-05-17');
    $totalMusic= rand(5, 100);
    $albums    = rand(1, 20);
    $listeners = rand(100, 10000);
    $bio       = addslashes("Bio of $name...");
    $countryId = rand(1, count($countries));
    //$img       = "artist{$i}.jpg";
    $seed = rawurlencode(strtolower(str_replace(' ', '_', $name)));
    
    $img  = "https://picsum.photos/seed/{$seed}/300/300";

    $sql = sprintf(
        "INSERT INTO ARTISTS (artist_id,name,genre,date_joined,total_num_music,total_albums,listeners,bio,country_id,image) VALUES
         (%d,'%s','%s','%s',%d,%d,%d,'%s',%d,'%s');\n",
        $i, $name, $genre, $joined, $totalMusic, $albums, $listeners, $bio, $countryId, $img
    );
    fwrite($output, $sql);
}


// 8) ALBUMS tablosu

fwrite($output, "\n-- ALBUMS kayıtları\n");
// Albüm resimlerini tutacak dizi
$albumImages = [];

for ($i = 1; $i <= NUM_ALBUMS; $i++) {
    $artistId = rand(1, NUM_ARTISTS);
    $title    = addslashes($albumTitles[array_rand($albumTitles)]);
    $release  = date('Y-m-d', strtotime(randomDate('2015-01-01','2025-05-17')));
    $genre    = addslashes($genres[array_rand($genres)]);
    $musicNum = rand(5,20);
    // Albüm resmi (örneğin album1.jpg, album2.jpg…)
    //$img      = "album{$i}.jpg";
    $seed = rawurlencode(strtolower(str_replace(' ', '_', $title)));
    
    $img  = "https://picsum.photos/seed/{$seed}/300/300";

    // Bu albümün resmini dizide saklıyoruz
    $albumImages[$i] = $img;

    $sql = sprintf(
        "INSERT INTO ALBUMS (album_id,artist_id,name,release_date,genre,music_number,image) VALUES
         (%d,%d,'%s','%s','%s',%d,'%s');\n",
        $i, $artistId, $title, $release, $genre, $musicNum, $img
    );
    fwrite($output, $sql);
}

// 9) SONGS tablosu
fwrite($output, "\n-- SONGS kayıtları\n");
for ($i = 1; $i <= NUM_SONGS; $i++) {
    // Rastgele bir albüm seç
    $albumId = rand(1, NUM_ALBUMS);
    $title   = addslashes($songTitles[array_rand($songTitles)]);
    $duration = sprintf("00:%02d:%02d", rand(2,5), rand(0,59));
    $genre   = addslashes($genres[array_rand($genres)]);
    $release = date('Y-m-d', strtotime(randomDate('2015-01-01','2025-05-17')));
    $rank    = rand(1,1000);

    // Albüm resmini diziden alıyoruz
    $img     = $albumImages[$albumId];

    $sql = sprintf(
        "INSERT INTO SONGS (song_id,album_id,title,duration,genre,release_date,`rank`,image) VALUES
         (%d,%d,'%s','%s','%s','%s',%d,'%s');\n",
        $i, $albumId, $title, $duration, $genre, $release, $rank, $img
    );
    fwrite($output, $sql);
}

// 10) PLAY_HISTORY tablosu
fwrite($output, "\n-- PLAY_HISTORY kayıtları\n");
for ($i = 1; $i <= NUM_PLAYHISTORY; $i++) {
    $userId   = rand(1, NUM_USERS);
    $songId   = rand(1, NUM_SONGS);
    $ptime    = randomDate('2025-01-01','2025-05-17');

    $sql = sprintf(
        "INSERT INTO PLAY_HISTORY (play_id,user_id,song_id,playtime) VALUES
         (%d,%d,%d,'%s');\n",
        $i, $userId, $songId, $ptime
    );
    fwrite($output, $sql);
}

// 11) PLAYLISTS tablosu
fwrite($output, "\n-- PLAYLISTS kayıtları\n");
for ($i = 1; $i <= NUM_PLAYLISTS; $i++) {
    $userId  = rand(1, NUM_USERS);
    $title   = addslashes($playlistTitles[array_rand($playlistTitles)]);
    $desc    = addslashes($descriptions[array_rand($descriptions)]);
    $created = randomDate('2020-01-01','2025-05-17');
    //$img     = "playlist{$i}.jpg";
    $seed = rawurlencode(strtolower(str_replace(' ', '_', $title)));
    // İstediğiniz boyutu /300/300 olarak belirledik:
    $img  = "https://picsum.photos/seed/{$seed}/300/300";

    $sql = sprintf(
        "INSERT INTO PLAYLISTS (playlist_id,user_id,title,description,date_created,image) VALUES
         (%d,%d,'%s','%s','%s','%s');\n",
        $i, $userId, $title, $desc, $created, $img
    );
    fwrite($output, $sql);
}

// 12) PLAYLIST_SONGS tablosu
fwrite($output, "\n-- PLAYLIST_SONGS kayıtları\n");
for ($i = 1; $i <= NUM_PLAYLISTSONG; $i++) {
    $plistId = rand(1, NUM_PLAYLISTS);
    $songId  = rand(1, NUM_SONGS);
    $added   = randomDate('2023-01-01','2025-05-17');

    $sql = sprintf(
        "INSERT INTO PLAYLIST_SONGS (playlistsong_id,playlist_id,song_id,date_added) VALUES
         (%d,%d,%d,'%s');\n",
        $i, $plistId, $songId, $added
    );
    fwrite($output, $sql);
}

// 13) Dosyayı kapat ve bilgi ver
fclose($output);
echo "output.sql başarıyla oluşturuldu (" . __DIR__ . "/output.sql).\n";
