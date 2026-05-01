<?php require_once '../includes/header.php'; ?>

<?php
$employees = get_employees();
$payroll = get_payroll();
$cuti = get_cuti();
$absensi = get_absensi();

$total_karyawan = count($employees);
$karyawan_aktif = count(array_filter($employees, fn($e)=>$e['status']==='Aktif'));

$today = date('Y-m-d');
$hadir_hari_ini = count(array_filter($absensi, fn($a)=>$a['tanggal']==$today && $a['status']==='Hadir'));

$total_gaji_bulan = array_sum(array_column(
    array_filter($payroll, fn($p)=>substr($p['bulan'],0,7)==date('Y-m')),
    'total'
));

$cuti_pending = count(array_filter($cuti, fn($c)=>$c['status']==='Menunggu'));
$cuti_aktif = count(array_filter($cuti, fn($c)=>
    $c['status']==='Disetujui' && $c['tgl_mulai']<=$today && $c['tgl_selesai']>=$today
));

// Chart data absensi 5 hari terakhir
$dates = [];
$hadir_data = [];
$absen_data = [];
for ($i=4; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d/m', strtotime($d));
    $hadir_data[] = count(array_filter($absensi, fn($a)=>$a['tanggal']==$d && $a['status']==='Hadir'));
    $absen_data[] = count(array_filter($absensi, fn($a)=>$a['tanggal']==$d && $a['status']==='Tidak Hadir'));
}
?>

<div class="stats-grid">
  <div class="stat-card orange">
    <div class="stat-label">Total Karyawan</div>
    <div class="stat-value"><?= $total_karyawan ?></div>
    <div class="stat-sub"><?= $karyawan_aktif ?> aktif saat ini</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Hadir Hari Ini</div>
    <div class="stat-value"><?= $hadir_hari_ini ?></div>
    <div class="stat-sub">dari <?= $karyawan_aktif ?> karyawan aktif</div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-label">Penggajian Bulan Ini</div>
    <div class="stat-value" style="font-size:1.3rem"><?= rupiah($total_gaji_bulan) ?></div>
    <div class="stat-sub"><?= count(array_filter($payroll, fn($p)=>substr($p['bulan'],0,7)==date('Y-m'))) ?> karyawan diproses</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-label">Cuti Pending</div>
    <div class="stat-value"><?= $cuti_pending ?></div>
    <div class="stat-sub"><?= $cuti_aktif ?> sedang cuti aktif</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
  <!-- Grafik Absensi -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 Tren Absensi 5 Hari Terakhir</div>
    </div>
    <canvas id="absensiChart" height="180"></canvas>
  </div>

  <!-- Ringkasan Cuti -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🌴 Status Cuti</div>
      <a href="cuti.php" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <?php
    $jenis_cuti = ['Cuti Tahunan','Cuti Sakit','Cuti Melahirkan','Cuti Darurat'];
    foreach ($jenis_cuti as $j):
      $count = count(array_filter($cuti, fn($c)=>$c['jenis']==$j));
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--border)">
      <span style="font-size:0.85rem"><?= $j ?></span>
      <span class="badge badge-info"><?= $count ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Daftar Karyawan Terbaru Cuti Pending -->
<?php if ($cuti_pending > 0): ?>
<div class="card">
  <div class="card-header">
    <div class="card-title">⏳ Pengajuan Cuti Menunggu Persetujuan</div>
    <a href="cuti.php" class="btn btn-primary btn-sm">Proses Sekarang</a>
  </div>
  <table>
    <thead>
      <tr><th>Karyawan</th><th>Jenis Cuti</th><th>Tanggal</th><th>Lama</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php foreach (array_filter($cuti, fn($c)=>$c['status']==='Menunggu') as $c): ?>
    <tr>
      <td><strong><?= htmlspecialchars(get_emp_name($c['emp_id'])) ?></strong></td>
      <td><?= $c['jenis'] ?></td>
      <td><?= date('d M Y', strtotime($c['tgl_mulai'])) ?> – <?= date('d M Y', strtotime($c['tgl_selesai'])) ?></td>
      <td><?= $c['lama'] ?> hari</td>
      <td><span class="badge badge-warning"><?= $c['status'] ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Aktivitas Absensi Terbaru -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Absensi Terbaru</div>
    <a href="absensi.php" class="btn btn-outline btn-sm">Lihat Semua</a>
  </div>
  <table>
    <thead>
      <tr><th>Karyawan</th><th>Tanggal</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php
    $recent_abs = array_slice(array_reverse($absensi), 0, 6);
    foreach ($recent_abs as $a):
      $badge = match($a['status']) {
        'Hadir' => 'badge-success',
        'Terlambat' => 'badge-warning',
        default => 'badge-danger'
      };
    ?>
    <tr>
      <td><strong><?= htmlspecialchars(get_emp_name($a['emp_id'])) ?></strong></td>
      <td><?= date('d M Y', strtotime($a['tanggal'])) ?></td>
      <td><?= $a['jam_masuk'] ?: '—' ?></td>
      <td><?= $a['jam_keluar'] ?: '—' ?></td>
      <td><span class="badge <?= $badge ?>"><?= $a['status'] ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('absensiChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($dates) ?>,
    datasets: [
      {
        label: 'Hadir',
        data: <?= json_encode($hadir_data) ?>,
        backgroundColor: 'rgba(42, 122, 75, 0.8)',
        borderRadius: 6,
      },
      {
        label: 'Tidak Hadir',
        data: <?= json_encode($absen_data) ?>,
        backgroundColor: 'rgba(200, 82, 42, 0.7)',
        borderRadius: 6,
      }
    ]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true, grid: { color: '#f0ede8' }, ticks: { stepSize: 1 } }
    }
  }
});
</script>

<?php require_once '../includes/footer.php'; ?>
