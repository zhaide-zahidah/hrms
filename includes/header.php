<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../index.php'); exit; }
require_once __DIR__ . '/data.php';
init_data();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HRMS Pro — <?= ucfirst($current_page) ?></title>
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
    --sidebar-w: 250px;
    --success: #2a7a4b;
    --warning: #b87a00;
    --danger: #c0392b;
    --info: #1a5f8a;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--paper);
    color: var(--ink);
    display: flex;
    min-height: 100vh;
  }
  /* SIDEBAR */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--ink);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
    overflow-y: auto;
  }
  .sidebar-brand {
    padding: 1.5rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .sidebar-logo {
    width: 36px; height: 36px;
    background: var(--accent);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    color: white;
    font-size: 0.85rem;
    flex-shrink: 0;
  }
  .sidebar-brand-text {
    font-family: 'Syne', sans-serif;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    line-height: 1;
  }
  .sidebar-brand-text small {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-weight: 400;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.4);
    margin-top: 2px;
  }
  .sidebar-nav {
    flex: 1;
    padding: 1rem 0;
  }
  .nav-section {
    padding: 0.5rem 1.25rem 0.25rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    margin-top: 0.5rem;
  }
  .nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 1.25rem;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 0.88rem;
    transition: all 0.15s;
    border-left: 3px solid transparent;
    position: relative;
  }
  .nav-item:hover {
    color: white;
    background: rgba(255,255,255,0.05);
  }
  .nav-item.active {
    color: white;
    background: rgba(200,82,42,0.15);
    border-left-color: var(--accent);
  }
  .nav-icon { font-size: 1.1rem; width: 20px; text-align: center; }
  .sidebar-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.08);
  }
  .user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
  }
  .user-avatar {
    width: 34px; height: 34px;
    background: var(--accent2);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    color: var(--ink);
    font-size: 0.8rem;
    flex-shrink: 0;
  }
  .user-name {
    font-size: 0.82rem;
    color: white;
    font-weight: 500;
  }
  .user-role {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.4);
  }
  .logout-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: rgba(200,82,42,0.15);
    color: rgba(255,255,255,0.7);
    border: none;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    cursor: pointer;
    width: 100%;
    transition: background 0.15s;
    text-decoration: none;
  }
  .logout-btn:hover { background: rgba(200,82,42,0.3); color: white; }

  /* MAIN CONTENT */
  .main-content {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  .topbar {
    background: var(--card);
    border-bottom: 1px solid var(--border);
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
  }
  .page-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.25rem;
    font-weight: 700;
  }
  .topbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.85rem;
    color: var(--muted);
  }
  .date-badge {
    background: var(--paper);
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--ink);
    border: 1px solid var(--border);
  }
  .page-body {
    padding: 2rem;
    flex: 1;
  }
  /* CARDS */
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
  }
  .card-title {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
  }
  /* STATS */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  .stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    position: relative;
    overflow: hidden;
  }
  .stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 0 14px 0 100%;
    opacity: 0.08;
  }
  .stat-card.orange::before { background: var(--accent); }
  .stat-card.yellow::before { background: var(--accent2); }
  .stat-card.green::before { background: #2a7a4b; }
  .stat-card.blue::before { background: #1a5f8a; }
  .stat-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.5rem;
  }
  .stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 1.8rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 0.25rem;
  }
  .stat-sub {
    font-size: 0.78rem;
    color: var(--muted);
  }
  /* TABLE */
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
  }
  th {
    text-align: left;
    padding: 0.65rem 1rem;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
    background: #fafaf8;
  }
  td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f0ede8;
    vertical-align: middle;
  }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #fafaf8; }
  /* BADGE */
  .badge {
    display: inline-block;
    padding: 0.25em 0.75em;
    border-radius: 20px;
    font-size: 0.73rem;
    font-weight: 600;
  }
  .badge-success { background: #e6f4ec; color: var(--success); }
  .badge-warning { background: #fff8e6; color: var(--warning); }
  .badge-danger  { background: #fdecea; color: var(--danger); }
  .badge-info    { background: #e6f0f8; color: var(--info); }
  .badge-muted   { background: #f0ede8; color: var(--muted); }
  /* BUTTON */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.15s;
  }
  .btn-primary { background: var(--ink); color: white; }
  .btn-primary:hover { background: var(--accent); }
  .btn-outline {
    background: transparent;
    color: var(--ink);
    border: 1.5px solid var(--border);
  }
  .btn-outline:hover { border-color: var(--ink); }
  .btn-success { background: var(--success); color: white; }
  .btn-danger  { background: var(--danger); color: white; }
  .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.78rem; }
  /* FORM */
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .form-group { margin-bottom: 1rem; }
  .form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
    margin-bottom: 0.4rem;
  }
  .form-control {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    color: var(--ink);
    background: var(--card);
    outline: none;
    transition: border-color 0.15s;
  }
  .form-control:focus { border-color: var(--accent); }
  select.form-control { cursor: pointer; }
  /* ALERT */
  .alert {
    padding: 0.85rem 1.1rem;
    border-radius: 10px;
    font-size: 0.88rem;
    margin-bottom: 1.25rem;
    border-left: 4px solid;
  }
  .alert-success { background: #e6f4ec; color: var(--success); border-color: var(--success); }
  .alert-danger  { background: #fdecea; color: var(--danger); border-color: var(--danger); }
  /* RESPONSIVE */
  @media (max-width: 900px) {
    .sidebar { transform: translateX(-100%); }
    .main-content { margin-left: 0; }
    .form-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo">HR</div>
    <div class="sidebar-brand-text">
      HRMS Pro
      <small>Sistem SDM Terpadu</small>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Utama</div>
    <a href="dashboard.php" class="nav-item <?= $current_page=='dashboard'?'active':'' ?>">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="karyawan.php" class="nav-item <?= $current_page=='karyawan'?'active':'' ?>">
      <span class="nav-icon">👥</span> Data Karyawan
    </a>
    <div class="nav-section">Operasional</div>
    <a href="absensi.php" class="nav-item <?= $current_page=='absensi'?'active':'' ?>">
      <span class="nav-icon">📋</span> Absensi
    </a>
    <a href="cuti.php" class="nav-item <?= $current_page=='cuti'?'active':'' ?>">
      <span class="nav-icon">🌴</span> Cuti
    </a>
    <a href="payroll.php" class="nav-item <?= $current_page=='payroll'?'active':'' ?>">
      <span class="nav-icon">💰</span> Penggajian
    </a>
    <div class="nav-section">Analitik</div>
    <a href="laporan.php" class="nav-item <?= $current_page=='laporan'?'active':'' ?>">
      <span class="nav-icon">📈</span> Laporan
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($user['name'],0,2)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="user-role"><?= strtoupper($user['role']) ?></div>
      </div>
    </div>
    <a href="../logout.php" class="logout-btn">🚪 Keluar</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main-content">
<div class="topbar">
  <div class="page-title">
    <?php
    $titles = [
      'dashboard'=>'Dashboard', 'karyawan'=>'Data Karyawan',
      'absensi'=>'Manajemen Absensi', 'cuti'=>'Manajemen Cuti',
      'payroll'=>'Penggajian (Payroll)', 'laporan'=>'Laporan & Analitik'
    ];
    echo $titles[$current_page] ?? ucfirst($current_page);
    ?>
  </div>
  <div class="topbar-right">
    <span class="date-badge">📅 <?= date('d M Y') ?></span>
    <span>Halo, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
  </div>
</div>
<div class="page-body">
