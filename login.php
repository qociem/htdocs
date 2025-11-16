<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // ========================
    // PILIH SATU Opsi di bawah
    // ========================

    /* ----- Opsi A: Hardcoded user (quick, LAN only) -----
    $HC_USER = 'admin';
    // hash for password "secret123" created with password_hash('secret123', PASSWORD_DEFAULT)
    $HC_HASH = 'admin'; // <-- GANTI DENGAN HASH AKTUAL
    if ($username === $HC_USER && password_verify($password, $HC_HASH)) {
        $_SESSION['user'] = $username;
        header('Location: index.php');
        exit;
    }*/

    // ----- Opsi B: DB-based user -----
    // Uncomment bagian berikut jika kamu pakai DB users (dan punya db.php)
    
    require_once 'db.php';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
        $_SESSION['user'] = $u['username'];
        header('Location: index.php');
        exit;
    }
    

    $err = 'Username atau password salah';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <style>
    body{font-family:Arial, sans-serif; background:#f5f7fa; display:flex; align-items:center; justify-content:center; height:100vh;}
    .box{background:#fff;padding:20px;border-radius:8px; box-shadow:0 6px 24px rgba(0,0,0,0.08); width:320px;}
    input{width:100%;padding:8px;margin:8px 0;border-radius:6px;border:1px solid #ddd;}
    .btn{background:#3498db;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;width:100%;}
    .err{color:#b00020;font-size:0.9em;}
  </style>
</head>
<body>
  <div class="box">
    <h3>Masuk</h3>
    <?php if($err): ?><div class="err"><?=htmlspecialchars($err)?></div><?php endif; ?>
    <form method="post" action="">
      <input name="username" placeholder="Username" required>
      <input name="password" type="password" placeholder="Password" required>
      <button class="btn" type="submit">Masuk</button>
    </form>
  </div>
</body>
</html>
