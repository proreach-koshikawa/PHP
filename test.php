<?php
require_once("./phpQuery-onefile.php");

$pdo = new PDO('sqlite:test.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// テーブルの作成
$sql = 'CREATE TABLE IF NOT EXISTS test_table (
  id INTEGER NOT NULL PRIMARY KEY,
  name TEXT NOT NULL,
  ruby TEXT NOT NULL,
  url TEXT NOT NULL,
  grades TEXT NOT NULL,
  types TEXT NOT NULL
);';
$pdo->exec($sql);

// ユニークインデックスの作成
$sql = 'CREATE UNIQUE INDEX IF NOT EXISTS id_index on test_table(id);';
$pdo->exec($sql);

// テーブルの作成
$sql = 'CREATE TABLE IF NOT EXISTS schools_table (
  id INTEGER NOT NULL PRIMARY KEY,
  brand_id TEXT NOT NULL,
  name TEXT NOT NULL,
  address TEXT NOT NULL
);';
$pdo->exec($sql);

// ユニークインデックスの作成
$sql = 'CREATE UNIQUE INDEX IF NOT EXISTS id_index on schools_table(id);';
$pdo->exec($sql);

$link = 'https://www.jyukunavi.jp/brand/';
$html = file_get_contents($link);
$doc = phpQuery::newDocument($html);

$links = $doc[".blist-name-unit a"];
foreach ($links as $link) {
  $pref_text = pq($link)->text();
  $pref_url = "https://www.jyukunavi.jp" . pq($link)->attr("href");

  print_r($pref_text . "\n");
  print_r($pref_url . "\n");

  $pref_html = file_get_contents($pref_url);
  $pref_doc = phpQuery::newDocument($pref_html);
  $list_link = "https://www.jyukunavi.jp" . $pref_doc[".navigationTab-item__list"][0]->attr("href");
  print_r($list_link . "\n");

  // 塾名の取得
  $name = pq($pref_doc)->find('.bd-title-groupe-name .item')->text();
  $name = preg_replace("/(\r\n|\r|\n|\t)/", "", $name);
  // ふりがなの取得
  $ruby = pq($pref_doc)->find('.bd-title-groupe-name .ruby')->text();
  $ruby = preg_replace("/(\r\n|\r|\n|\t)/", "", $ruby);
  // 学年の取得
  $grades = $pref_doc->find('.sunit-list-spec-ic.sunit-list-grade li');
  $grade_array = array();
  foreach ($grades as $grade) {
    $grade_text = pq($grade)->text();
    $grade_text = preg_replace("/(\r\n|\r|\n|\t)/", "", $grade_text);
    array_push($grade_array, $grade_text);
  }
  $grades_str = implode(', ', $grade_array);
  // 授業形式の取得
  $types = $pref_doc->find('.sunit-list-spec-ic.sunit-list-classType li');
  $type_array = array();
  foreach ($types as $type) {
    $type_text = pq($type)->text();
    $type_text = preg_replace("/(\r\n|\r|\n|\t)/", "", $type_text);
    array_push($type_array, $type_text);
  }
  $types_str = implode(', ', $type_array);

  var_dump($ruby);
  var_dump($name);
  var_dump($grades_str);
  var_dump($types_str);

  $stmt = $pdo->prepare("INSERT INTO test_table(name, ruby, url, grades, types) VALUES (:name, :ruby, :url, :grades, :types)");

  $stmt->bindValue(':name', $name, PDO::PARAM_STR);
  $stmt->bindValue(':ruby', $ruby, PDO::PARAM_STR);
  $stmt->bindValue(':url', $pref_url, PDO::PARAM_STR);
  $stmt->bindValue(':grades', $grades_str, PDO::PARAM_STR);
  $stmt->bindValue(':types', $types_str, PDO::PARAM_STR);

  $res = $stmt->execute();

  $brand_id = $pdo->lastInsertId();

  // 校舎の取得
  $list_html = file_get_contents($list_link);
  $list_doc = phpQuery::newDocument($list_html);

  $buildings = $list_doc->find('.modal-searchsch-list-table tr');
  foreach ($buildings as $building) {
    $building_text = pq($building)->find('.col-sch-name')->text();
    $building_text = preg_replace("/(\r\n|\r|\n|\t)/", "", $building_text);
    $address = pq($building)->find('.col-sch-line')->text();

    $stmt = $pdo->prepare("INSERT INTO schools_table(brand_id, name, address) VALUES (:brand_id, :name, :address)");

    $stmt->bindValue(':brand_id', $brand_id, PDO::PARAM_STR);
    $stmt->bindValue(':name', $building_text, PDO::PARAM_STR);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    
    $res = $stmt->execute();
  }
}
?>
