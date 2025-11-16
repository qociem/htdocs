<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!-- lalu HTML index.php seperti biasa mengikuti -->

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Produk & Catatan</title>
  <link rel="stylesheet" href="style.css">
  <a href="logout.php" style="position:fixed;right:16px;top:16px;">Logout</a>
  <!-- edit tampilan log out -->
  <style>
    body {
      background: #f5f7fa;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 1200px;
      margin: 20px auto;
    }
    .app {
      display: grid;
      grid-template-columns: 320px 1fr; /* kiri dan kanan fix */
      gap: 20px;
      align-items: start;
      min-height: 80vh; /* tinggi tetap */
    }
    .left, .right {
      background: #fff;
      padding: 16px;
      border-radius: 10px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    .left { overflow-y: auto; }
    .right { overflow-y: auto; }
    .list { flex: 1; overflow-y: auto; margin-top:10px; }
    .project-item { padding:8px; border-bottom:1px solid #eee; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
    .project-item.active { background:#eef6ff; border-left:4px solid #3498db; }
    .btn { padding:6px 10px; border:none; border-radius:6px; cursor:pointer; }
    .btn.primary { background:#3498db; color:#fff; }
    .btn.danger { background:#e74c3c; color:#fff; }
    .form-box { border:1px dashed #ccc; padding:10px; border-radius:8px; margin-top:10px; display:none; }
    .note-card { border:1px solid #eee; padding:10px; border-radius:8px; margin-bottom:8px; }
    .small { font-size:0.85em; color:#666; }
    .attachment { margin-top:8px; }
    #notesList {
      flex: 1;
      overflow-y: auto;
      padding-top: 8px;
      min-height: 300px; /* agar area catatan tetap ada walau kosong */
    }
    .empty-placeholder {
      color:#888;
      text-align:center;
      margin-top:40px;
      font-size:0.9em;
    }
  </style>
</head>
<body>
  <div class="container">
    <header><h1>Produk & Catatan</h1></header>

    <div class="app">
      <!-- kiri -->
      <div class="left">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <h3>Produk</h3>
          <button id="showAddProject" class="btn primary">+ Tambah</button>
        </div>

        <div id="projectForm" class="form-box">
          <input id="projectName" placeholder="Nama produk" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd">
          <textarea id="projectDesc" placeholder="Deskripsi (opsional)" style="width:100%; margin-top:8px; padding:8px; border-radius:6px; border:1px solid #ddd"></textarea>
          <div style="margin-top:8px; display:flex; gap:8px;">
            <button id="addProjectBtn" class="btn primary">Simpan</button>
            <button id="cancelProjectBtn" class="btn">Batal</button>
          </div>
        </div>

        <div class="list" id="projectsList"></div>
      </div>

      <!-- kanan -->
      <div class="right">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <h3>Catatan untuk: <span id="activeProjectName" class="small">(pilih Produk)</span></h3>
          <button id="showAddNote" class="btn primary" style="display:none;">+ Tambah</button>
        </div>

        <div id="noteForm" class="form-box">
          <input id="noteTitle" placeholder="Judul catatan" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd">
          <textarea id="noteContent" placeholder="Isi catatan..." style="width:100%; margin-top:8px; padding:8px; border-radius:6px; border:1px solid #ddd"></textarea>
          <div style="margin-top:8px;">
            <label class="small">Lampiran (foto/video): </label>
            <input type="file" id="noteAttachment" accept="image/*,video/*">
          </div>
          <div style="margin-top:8px; display:flex; gap:8px;">
            <button id="addNoteBtn" class="btn primary">Simpan</button>
            <button id="cancelNoteBtn" class="btn">Batal</button>
          </div>
        </div>

        <hr>
        <div id="notesList"><div class="empty-placeholder">Belum ada catatan untuk ditampilkan.</div></div>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
