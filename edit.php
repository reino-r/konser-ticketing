<?php
require_once __DIR__ . '/db.php';

$db     = getDB();
$errors = [];
$row    = null;

// Ambil data existing
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}
if (!$id) {
  header('Location: index.php');
  exit;
}

$stmt = $db->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
  header('Location: index.php');
  exit;
}

// Proses update
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $artist = trim($_POST['artist'] ?? '');
  $venue  = trim($_POST['venue'] ?? '');
  $date   = trim($_POST['date'] ?? '');
  $time   = trim($_POST['time'] ?? '');
  $price  = trim($_POST['price'] ?? '');
  $quota  = trim($_POST['quota'] ?? '');

  if ($artist === '')  $errors[] = 'Nama artis wajib diisi.';
  if ($venue === '')   $errors[] = 'Venue wajib diisi.';
  if ($date === '')    $errors[] = 'Tanggal konser wajib diisi.';
  if ($time === '')    $errors[] = 'Waktu konser wajib diisi.';
  if ($price === '' || !is_numeric($price) || $price < 0) $errors[] = 'Harga harus berupa angka positif.';
  if ($quota === '' || !is_numeric($quota) || $quota < 0) $errors[] = 'Kuota harus berupa angka positif.';

  $posterPath = $row['poster'];
  if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $_FILES['poster']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
      $errors[] = 'Poster harus berupa file gambar (JPG, PNG, WEBP, GIF).';
    } else {
      $ext      = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
      $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
      $dest     = UPLOAD_DIR . $filename;

      if (move_uploaded_file($_FILES['poster']['tmp_name'], $dest)) {
        // Hapus poster lama
        if ($row['poster'] && file_exists(__DIR__ . '/' . $row['poster'])) {
          unlink(__DIR__ . '/' . $row['poster']);
        }
        $posterPath = 'uploads/' . $filename;
      } else {
        $errors[] = 'Gagal mengupload poster.';
      }
    }
  }

  if (empty($errors)) {
    $stmt = $db->prepare(
      "UPDATE concerts SET artist=?, venue=?, date=?, time=?, price=?, quota=?, poster=? WHERE id=?"
    );
    $stmt->bind_param('ssssiisi', $artist, $venue, $date, $time, $price, $quota, $posterPath, $id);
    $stmt->execute();
    $stmt->close();

    // Refresh data
    $stmt = $db->prepare("SELECT * FROM concerts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $success = true;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Konser — KonserTicketing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --bg-dark: #0f0f1a;
      --card-bg: #1a1a2e;
      --accent: #b44aff;
      --accent2: #ff2d95;
      --accent-glow: 0 0 20px rgba(180,74,255,.35);
    }
    body {
      background: var(--bg-dark);
      color: #e0e0e0;
      font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      min-height: 100vh;
    }
    .navbar {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
      border-bottom: 1px solid rgba(180,74,255,.25);
    }
    .navbar-brand {
      font-weight: 800;
      font-size: 1.5rem;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .form-card {
      background: var(--card-bg);
      border: 1px solid rgba(180,74,255,.15);
      border-radius: 16px;
      padding: 2rem;
      max-width: 700px;
      margin: 0 auto;
    }
    .form-control, .form-select {
      background: #12122a;
      border: 1px solid rgba(180,74,255,.2);
      color: #e0e0e0;
      border-radius: 10px;
      padding: .65rem 1rem;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
      background: #12122a;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(180,74,255,.2);
      color: #e0e0e0;
    }
    .form-control::placeholder { color: #666; }
    .form-label {
      font-weight: 600;
      color: #ccc;
      margin-bottom: .35rem;
    }
    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      border: none;
      color: #fff;
      font-weight: 600;
      padding: .65rem 2rem;
      transition: all .25s;
      box-shadow: var(--accent-glow);
      border-radius: 10px;
    }
    .btn-accent:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 30px rgba(180,74,255,.5);
      color: #fff;
    }
    .btn-outline-accent {
      background: transparent;
      border: 2px solid var(--accent);
      color: var(--accent);
      font-weight: 600;
      padding: .65rem 2rem;
      transition: all .25s;
      border-radius: 10px;
    }
    .btn-outline-accent:hover {
      background: var(--accent);
      color: #fff;
    }
    .btn-outline-danger {
      background: transparent;
      border: 2px solid #ff2d95;
      color: #ff2d95;
      font-weight: 600;
      padding: .65rem 2rem;
      transition: all .25s;
      border-radius: 10px;
    }
    .btn-outline-danger:hover {
      background: #ff2d95;
      color: #fff;
    }
    .current-poster {
      border-radius: 12px;
      overflow: hidden;
      max-width: 200px;
      border: 1px solid rgba(180,74,255,.2);
    }
    .current-poster img {
      width: 100%;
      height: auto;
      display: block;
    }
    .alert-danger {
      background: rgba(255,45,149,.15);
      border: 1px solid rgba(255,45,149,.3);
      color: #ff6bb5;
      border-radius: 10px;
    }
    .alert-success {
      background: rgba(0,200,83,.15);
      border: 1px solid rgba(0,200,83,.3);
      color: #69f0ae;
      border-radius: 10px;
    }
    .form-text { color: #777; }
    ::file-selector-button {
      background: var(--accent) !important;
      color: #fff !important;
      border: none !important;
      padding: .4rem 1rem !important;
      border-radius: 8px !important;
      font-weight: 600;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark px-3 py-3">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="bi bi-music-note-beamed me-2"></i>KonserTicketing
    </a>
    <div>
      <a href="index.php" class="btn btn-outline-accent btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="form-card">
    <h4 class="fw-bold mb-3">
      <i class="bi bi-pencil-square me-2" style="color:var(--accent)"></i>Edit Konser
    </h4>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger py-2 px-3">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success py-2 px-3">
        <i class="bi bi-check-circle me-1"></i>Konser berhasil diperbarui!
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="id" value="<?= $row['id'] ?>">

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label" for="artist">Nama Artis</label>
          <input type="text" class="form-control" id="artist" name="artist"
                 value="<?= htmlspecialchars($row['artist']) ?>" required>
        </div>

        <div class="col-12">
          <label class="form-label" for="venue">Venue</label>
          <input type="text" class="form-control" id="venue" name="venue"
                 value="<?= htmlspecialchars($row['venue']) ?>" required>
        </div>

        <div class="col-sm-6">
          <label class="form-label" for="date">Tanggal</label>
          <input type="date" class="form-control" id="date" name="date"
                 value="<?= htmlspecialchars($row['date']) ?>" required>
        </div>

        <div class="col-sm-6">
          <label class="form-label" for="time">Waktu</label>
          <input type="time" class="form-control" id="time" name="time"
                 value="<?= htmlspecialchars($row['time']) ?>" required>
        </div>

        <div class="col-sm-6">
          <label class="form-label" for="price">Harga (Rp)</label>
          <input type="number" class="form-control" id="price" name="price" min="0" step="1"
                 value="<?= htmlspecialchars($row['price']) ?>" required>
        </div>

        <div class="col-sm-6">
          <label class="form-label" for="quota">Kuota Tiket</label>
          <input type="number" class="form-control" id="quota" name="quota" min="0" step="1"
                 value="<?= htmlspecialchars($row['quota']) ?>" required>
        </div>

        <div class="col-12">
          <label class="form-label">Poster Saat Ini</label>
          <?php if ($row['poster'] && file_exists(__DIR__ . '/' . $row['poster'])): ?>
            <div class="current-poster mb-2">
              <img src="<?= htmlspecialchars($row['poster']) ?>" alt="Current poster">
            </div>
          <?php else: ?>
            <p class="text-secondary small"><i class="bi bi-image me-1"></i>Tidak ada poster</p>
          <?php endif; ?>
          <label class="form-label" for="poster">Ganti Poster (opsional)</label>
          <input type="file" class="form-control" id="poster" name="poster"
                 accept="image/jpeg,image/png,image/webp,image/gif">
          <div class="form-text">Kosongkan jika tidak ingin mengganti poster.</div>
        </div>

        <div class="col-12 d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-accent flex-fill">
            <i class="bi bi-save me-1"></i>Perbarui
          </button>
          <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger flex-fill"
             onclick="return confirm('Yakin ingin menghapus konser ini?')">
            <i class="bi bi-trash3 me-1"></i>Hapus
          </a>
          <a href="index.php" class="btn btn-outline-accent flex-fill">
            <i class="bi bi-x-lg me-1"></i>Batal
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
