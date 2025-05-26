<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$query = $_POST['query'] ?? '';
$error = '';
$results = [];
$rowCount = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = trim($query);
    if ($raw === '') {
        $error = 'Lütfen bir sorgu girin.';
    } elseif (stripos($raw, 'select') !== 0) {
        $error = 'Yalnızca SELECT sorgusu çalıştırılabilir.';
    } else {
        if (!preg_match('/\blimit\b/i', $raw)) {
            $raw .= ' LIMIT 5';
        }
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=alper_ozdemir;charset=utf8mb4',
                'root',
                'mysql',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $executedQuery = $raw;
            $stmt = $pdo->prepare($executedQuery);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rowCount = count($results);
            if ($rowCount === 0) {
                $error = 'Sorgu çalıştı ama 0 satır döndü.';
            }
        } catch (PDOException $e) {
            $error = 'SQL Hatası: ' . htmlspecialchars($e->getMessage());
            $executedQuery = $raw;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8"><meta name="viewport"content="width=device-width, initial-scale=1">
  <title>Genel SQL Sonuç</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Genel SQL Sonuç</h2>

  <?php if (!empty($query)): ?>
    <div class="mb-3">
      <strong>Çalıştırılan Sorgu:</strong>
      <pre class="bg-white p-2 border"><?= htmlspecialchars($executedQuery ?? $query) ?></pre>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-warning"><?= $error ?></div>
    <a href="generalSQL.html" class="btn btn-secondary">Geri</a>
  <?php else: ?>
    <div class="mb-2">
      <span class="badge bg-info">Toplam <?= $rowCount ?> satır döndü</span>
    </div>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <?php foreach (array_keys($results[0]) as $col): ?>
            <th><?= htmlspecialchars($col) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td><?= htmlspecialchars($cell) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
   
     <div class="mt-4 d-flex gap-2">
  <a href="generalSQL.html" class="btn btn-secondary">Yeni Sorgu</a>
  <a href="homepage.php"     class="btn btn-outline-primary">Anasayfaya Dön</a>
</div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
