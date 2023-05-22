<?php
  // データベースからデータを取得
  $stmt = $pdo->query('SELECT * FROM test_table');
  $data = $stmt->fetchAll();

  // データを画面に表示
  foreach ($data as $row) {
    echo 'Name: ' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'Ruby: ' . htmlspecialchars($row['ruby'], ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'URL: ' . htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'Grades: ' . htmlspecialchars($row['grades'], ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'Types: ' . htmlspecialchars($row['types'], ENT_QUOTES, 'UTF-8') . '<br>';
    echo '<hr>';
  }
?>
