<?php
require_once __DIR__ . '/db.php';

$db   = getDB();
$rows = $db->query("SELECT * FROM concerts ORDER BY date ASC, time ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KonserTicketing</title>
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
    .btn-accent {
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      border: none;
      color: #fff;
      font-weight: 600;
      transition: all .25s;
      box-shadow: var(--accent-glow);
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
      transition: all .25s;
    }
    .btn-outline-accent:hover {
      background: var(--accent);
      color: #fff;
      box-shadow: var(--accent-glow);
    }
    .concert-card {
      background: var(--card-bg);
      border: 1px solid rgba(180,74,255,.15);
      border-radius: 16px;
      overflow: hidden;
      transition: all .3s ease;
      height: 100%;
    }
    .concert-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 40px rgba(180,74,255,.25);
      border-color: var(--accent);
    }
    .card-img-top-wrapper {
      height: 220px;
      overflow: hidden;
      background: #111;
      position: relative;
    }
    .card-img-top-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .4s ease;
    }
    .concert-card:hover .card-img-top-wrapper img {
      transform: scale(1.08);
    }
    .card-img-top-wrapper .placeholder-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      font-size: 3rem;
      color: #444;
    }
    .price-badge {
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color: #fff;
      font-weight: 700;
      padding: 4px 14px;
      border-radius: 20px;
      font-size: .9rem;
    }
    .quota-badge {
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.12);
      color: #aaa;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: .85rem;
    }
    .artist-name {
      font-weight: 700;
      font-size: 1.2rem;
      color: #fff;
    }
    .info-icon {
      color: var(--accent);
      margin-right: 6px;
    }
    .page-header {
      border-bottom: 1px solid rgba(180,74,255,.15);
      padding-bottom: 1rem;
      margin-bottom: 2rem;
    }
    .empty-state {
      text-align: center;
      padding: 4rem 1rem;
      color: #666;
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1rem;
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
      <a href="create.php" class="btn btn-accent btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Konser
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <h4 class="mb-0 fw-bold">
      <i class="bi bi-calendar-event me-2" style="color:var(--accent)"></i>Daftar Konser
    </h4>
    <span class="text-secondary"><?= $rows->num_rows ?> konser</span>
  </div>

  <?php if ($rows->num_rows === 0): ?>
    <div class="empty-state">
      <i class="bi bi-emoji-frown"></i>
      <p class="fs-5">Belum ada konser tersedia.</p>
      <a href="create.php" class="btn btn-accent">
        <i class="bi bi-plus-lg me-1"></i>Tambah Konser Pertama
      </a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php while ($row = $rows->fetch_assoc()): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="concert-card">
            <div class="card-img-top-wrapper">
              <?php if ($row['poster'] && file_exists(__DIR__ . '/' . $row['poster'])): ?>
                <img src="<?= htmlspecialchars($row['poster']) ?>" alt="Poster">
              <?php else: ?>
                <div class="placeholder-icon">
                  <i class="bi bi-image"></i>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column p-3">
              <div class="artist-name mb-1"><?= htmlspecialchars($row['artist']) ?></div>

              <div class="mb-2 small">
                <div class="mb-1">
                  <i class="bi bi-geo-alt info-icon"></i>
                  <?= htmlspecialchars($row['venue']) ?>
                </div>
                <div class="mb-1">
                  <i class="bi bi-calendar3 info-icon"></i>
                  <?= date('d M Y', strtotime($row['date'])) ?>
                </div>
                <div>
                  <i class="bi bi-clock info-icon"></i>
                  <?= date('H:i', strtotime($row['time'])) ?> WIB
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                <span class="price-badge">
                  Rp <?= number_format($row['price'], 0, ',', '.') ?>
                </span>
                <span class="quota-badge">
                  <i class="bi bi-ticket-perforated me-1"></i><?= $row['quota'] ?>
                </span>
              </div>

              <div class="d-flex gap-2 mt-3">
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-accent btn-sm flex-fill">
                  <i class="bi bi-pencil-square me-1"></i>Edit
                </a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm flex-fill"
                   onclick="return confirm('Yakin ingin menghapus konser ini?')">
                  <i class="bi bi-trash3 me-1"></i>Hapus
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
