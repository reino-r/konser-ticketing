<?php
require_once __DIR__ . '/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  header('Location: index.php');
  exit;
}

$db = getDB();

// Ambil data untuk hapus poster
$stmt = $db->prepare("SELECT poster FROM concerts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$concert = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($concert) {
  // Hapus file poster jika ada
  if ($concert['poster'] && file_exists(__DIR__ . '/' . $concert['poster'])) {
    unlink(__DIR__ . '/' . $concert['poster']);
  }

  // Hapus record
  $stmt = $db->prepare("DELETE FROM concerts WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
}

header('Location: index.php');
exit;
