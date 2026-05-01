<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Demo credentials
    $users = [
        'admin' => ['password' => 'admin123', 'name' => 'Admin HRD', 'role' => 'admin'],
        'hrd'   => ['password' => 'hrd123',   'name' => 'Budi Santoso', 'role' => 'hrd'],
    ];
    
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['user'] = [
            'username' => $username,
            'name'     => $users[$username]['name'],
            'role'     => $users[$username]['role'],
        ];
        header('Location: pages/dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HRMS — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --ink: #0f1117;
    --paper: #f5f3ee;
    --accent: #c8522a;
    --accent2: #e8b84b;
    --muted: #8a8680;
    --card: #ffffff;
    --border: #e0ddd8;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--paper);
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
  .left-panel {
    background: var(--ink);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 3rem;
    position: relative;
    overflow: hidden;
  }
  .left-panel::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,82,42,0.25) 0%, transparent 70%);
  }
  .left-panel::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -80px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(232,184,75,0.2) 0%, transparent 70%);
  }
  .brand {
    position: relative; z-index: 1;
  }
  .brand-logo {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 3rem;
  }
  .logo-icon {
    width: 42px; height: 42px;
    background: var(--accent);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    color: white;
    font-size: 1.1rem;
    letter-spacing: -1px;
  }
  .logo-text {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    color: white;
    font-size: 1.2rem;
    letter-spacing: 0.05em;
  }
  .hero-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2.5rem, 4vw, 3.5rem);
    font-weight: 800;
    color: white;
    line-height: 1.1;
    margin-bottom: 1.5rem;
  }
  .hero-title span { color: var(--accent2); }
  .hero-desc {
    color: rgba(255,255,255,0.55);
    font-size: 1rem;
    line-height: 1.7;
    max-width: 380px;
  }
  .features-list {
    position: relative; z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .feat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
  }
  .feat-dot {
    width: 8px; height: 8px;
    background: var(--accent);
    border-radius: 50%;
    flex-shrink: 0;
  }
  .right-panel {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
  }
  .login-card {
    width: 100%;
    max-width: 420px;
  }
  .login-header {
    margin-bottom: 2.5rem;
  }
  .login-header h2 {
    font-family: 'Syne', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 0.4rem;
  }
  .login-header p {
    color: var(--muted);
    font-size: 0.95rem;
  }
  .form-group {
    margin-bottom: 1.25rem;
  }
  label {
    display: block;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--ink);
    margin-bottom: 0.5rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  input[type=text], input[type=password] {
    width: 100%;
    padding: 0.85rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    color: var(--ink);
    background: var(--card);
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
  }
  input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(200,82,42,0.1);
  }
  .btn-login {
    width: 100%;
    padding: 0.9rem;
    background: var(--ink);
    color: white;
    border: none;
    border-radius: 10px;
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    margin-top: 0.5rem;
  }
  .btn-login:hover { background: var(--accent); }
  .btn-login:active { transform: scale(0.98); }
  .error-msg {
    background: #fef2f0;
    border: 1px solid #f5c6bc;
    color: var(--accent);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 1.25rem;
  }
  .demo-hint {
    margin-top: 2rem;
    padding: 1rem;
    background: #f9f7f3;
    border-radius: 10px;
    border: 1px solid var(--border);
  }
  .demo-hint p {
    font-size: 0.8rem;
    color: var(--muted);
    margin-bottom: 0.3rem;
  }
  .demo-hint code {
    font-size: 0.8rem;
    color: var(--ink);
    background: white;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid var(--border);
  }
  @media (max-width: 768px) {
    body { grid-template-columns: 1fr; }
    .left-panel { display: none; }
    .right-panel { padding: 2rem 1.5rem; }
  }
</style>
</head>
<body>
<div class="left-panel">
  <div class="brand">
    <div class="brand-logo">
      <div class="logo-icon">HR</div>
      <span class="logo-text">HRMS Pro</span>
    </div>
    <h1 class="hero-title">Kelola SDM<br>dengan <span>Cerdas</span><br>& Efisien</h1>
    <p class="hero-desc">Platform manajemen sumber daya manusia terpadu untuk mengelola penggajian, absensi, dan cuti karyawan secara digital.</p>
  </div>
  <div class="features-list">
    <div class="feat-item"><span class="feat-dot"></span>Manajemen Penggajian & Slip Gaji</div>
    <div class="feat-item"><span class="feat-dot"></span>Sistem Absensi Digital</div>
    <div class="feat-item"><span class="feat-dot"></span>Pengajuan & Persetujuan Cuti</div>
    <div class="feat-item"><span class="feat-dot"></span>Laporan & Analitik HR</div>
  </div>
</div>
<div class="right-panel">
  <div class="login-card">
    <div class="login-header">
      <h2>Selamat Datang</h2>
      <p>Masuk ke dashboard HR Anda</p>
    </div>
    <?php if ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn-login">MASUK →</button>
    </form>
    <div class="demo-hint">
      <p><strong>Demo Login:</strong></p>
      <p>Admin: <code>admin</code> / <code>admin123</code></p>
      <p>HRD: <code>hrd</code> / <code>hrd123</code></p>
    </div>
  </div>
</div>
</body>
</html>
