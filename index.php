<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Produk & Catatan</title>
  <!-- <link rel="stylesheet" href="style.css">  -->
  <link rel="stylesheet" href="style.css?v=9999">

</head>

<body>

  <a href="logout.php" class="logout-link">Logout</a>

  <div class="container">

    <header><h1>Produk & Catatan</h1></header>

    <div class="app">

      <!-- ===================== PANEL KIRI ===================== -->
      <div class="left">

        <!-- HEADER KIRI -->
        <div class="left-header">
          <h3>Produk</h3>
          <button id="showAddProject" class="btn primary">+ Tambah</button>
        </div>

        <!-- FORM TAMBAH PROJECT -->
        <div id="projectForm" class="form-box">
          <input id="projectName" class="input" placeholder="Nama produk">
          <textarea id="projectDesc" class="textarea" placeholder="Deskripsi (opsional)"></textarea>
          <div class="button-row">
            <button id="addProjectBtn" class="btn primary">Simpan</button>
            <button id="cancelProjectBtn" class="btn">Batal</button>
          </div>
        </div>

        <!-- LIST PRODUK -->
        <div id="projectsList" class="left-list"></div>

      </div>

      <!-- ===================== PANEL KANAN ===================== -->
      <div class="right">

        <!-- HEADER KANAN -->
        <div class="right-header">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Catatan untuk: <span id="activeProjectName" class="small">(pilih Produk)</span></h3>
            <button id="showAddNote" class="btn primary" style="display:none;">+ Tambah</button>
          </div>

          <!-- FORM CATATAN -->
          <div id="noteForm" class="form-box">
            <input id="noteTitle" class="input" placeholder="Judul catatan">
            <textarea id="noteContent" class="textarea" placeholder="Isi catatan..."></textarea>

            <div class="file-row">
              <label class="small">Lampiran:</label>
              <input type="file" id="noteAttachment" accept="image/*,video/*">
            </div>

            <div class="button-row">
              <button id="addNoteBtn" class="btn primary">Simpan</button>
              <button id="cancelNoteBtn" class="btn">Batal</button>
            </div>
          </div>
        </div>

        <!-- LIST CATATAN -->
        <div id="notesList" class="right-content">
          <div class="empty-placeholder">Belum ada catatan untuk ditampilkan.</div>
        </div>

      </div>

    </div> <!-- end app -->

  </div> <!-- end container -->

  <script src="script.js"></script>

</body>
</html>
