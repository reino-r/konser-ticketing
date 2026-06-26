<?php
require_once __DIR__ . '/config.php';

function getDB(): mysqli {
  static $conn = null;
  if ($conn === null) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
    $conn->set_charset('utf8mb4');
  }
  return $conn;
}
