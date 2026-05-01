<?php require_once '../includes/header.php'; ?>

<?php
$employees = get_employees();
$payroll = get_payroll();
$cuti_list = get_cuti();
$absensi = get_absensi();

// --- Payroll summary per bulan ---
$payroll_by_month = [];
foreach ($payroll as $p) {
    $m = $p['bulan'];
    if (!isset($payroll_by_month[$m])) $payroll_by_month[$m] = ['total'=>0,'count'=>0,'dibayar'=>0];
    $payroll_by_month[$m]['total'] += $p['total'];
    $payroll_by_month[$m]['count']++;
    if ($p['status']==='Dibayar') $payroll_by_month[$m]['dibayar'] += $p['total'];
}
krsort($payroll_by_month);

// --- Dept summary ---
$depts = [];
foreach ($employees as $e) {
    $d = $e['departemen'];
    if (!isset($depts[$d])) $depts[$d] = ['count'=>0,'gaji'=>0];
    $depts[$d]['count']++;
    $depts[$d]['gaji'] += $e['gaji_pokok'];
}

// --- Absensi summary ---
$abs_summary = [];
foreach ($absensi as $a) {
    $id = $a['emp_id'];
    if (!isset($abs_summary[$id])) $abs_summary[$id] = ['hadir'=>0,'terlambat'=>0,'absen'=>0];
    if ($a['status']==='Hadir') $abs_summary[$id]['hadir']++;
    elseif ($a['status']==='Terlambat') $abs_summary[$id]['terlambat']++;
    else $abs_summary[$id]['absen']++;
}

// Chart data
$chart_months = array_keys(array_slice($payroll_by_month, 0, 6, true));
$chart_months = array_reverse($chart_months);
$chart_totals = array_map(fn($m)=>$payroll_by_month[$m]['total']??0, $chart_months);
$chart_labels = array_map(fn($m)=>date('M Y',strtotime($m.'-01')), $chart_months);

$dept_names = array_keys($depts);
$dept_counts = array_map(fn($d)=>$depts[$d]['count'], $dept_names);
$dept_gaji = array_map(fn($d)=>$depts[$d]['gaji'], $dept_names);
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
  <!-- Payroll trend -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📈 Tren Penggajian Bulanan</div>
    </div>
    <canvas id="payrollChart" height="200"></canvas>
  </div>
  <!-- Dept distribution -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🏢 Distribusi Karyawan per Departemen</div>
    </div>
    <canvas id="deptChart" height="200"></canvas>
  </div>
</div>

<!-- Payroll Detail -->
<div class="card" style="margin-bottom:1.5rem">
  <div class="card-header">
    <div class="card-title">💰 Ringkasan Penggajian per Periode</div>
  </div>
  <table>
    <thead>
      <tr><th>Periode</th><th>Jml Karyawan</th><th>Total Gaji</th><th>Sudah Dibayar</th><th>Belum Dibayar</th><th>Progress</th></tr>
    </thead>
    <tbody>
    <?php foreach (array_slice($payroll_by_month, 0, 6, true) as $bulan => $data):
      $pending = $data['total'] - $data['dibayar'];
      $pct = $data['total'] > 0 ? round($data['dibayar']/$data['total']*100) : 0;
    ?>
    <tr>
      <td><strong><?= date('F Y', strtotime($bulan.'-01')) ?></strong></td>
      <td><?= $data['count'] ?> orang</td>
      <td><?= rupiah($data['total']) ?></td>
      <td style="color:var(--success)"><?= rupiah($data['dibayar']) ?></td>
      <td style="color:<?= $pending>0?'var(--warning)':'var(--muted)' ?>"><?= rupiah($pending) ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:0.5rem">
          <div style="flex:1;height:6px;background:#f0ede8;border-radius:3px;overflow:hidden">
            <div style="width:<?= $pct ?>%;height:100%;background:var(--success);border-radius:3px"></div>
          </div>
          <span style="font-size:0.78rem;font-weight:600;color:var(--success)"><?= $pct ?>%</span>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
  <!-- Dept Salary -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🏢 Anggaran Gaji per Departemen</div>
    </div>
    <table>
      <thead><tr><th>Departemen</th><th>Jml</th><th>Total Gaji Pokok</th><th>Rata-rata</th></tr></thead>
      <tbody>
      <?php foreach ($depts as $dept => $info): ?>
      <tr>
        <td><span class="badge badge-info"><?= $dept ?></span></td>
        <td><?= $info['count'] ?></td>
        <td><?= rupiah($info['gaji']) ?></td>
        <td style="color:var(--muted)"><?= rupiah(round($info['gaji']/$info['count'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Cuti Summary -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🌴 Ringkasan Cuti per Jenis</div>
    </div>
    <?php
    $jenis_cuti = ['Cuti Tahunan','Cuti Sakit','Cuti Melahirkan','Cuti Darurat'];
    foreach ($jenis_cuti as $jenis):
      $jml = array_filter($cuti_list, fn($c)=>$c['jenis']==$jenis);
      $total_hari = array_sum(array_column($jml,'lama'));
      $disetujui = count(array_filter($jml, fn($c)=>$c['status']==='Disetujui'));
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-size:0.88rem;font-weight:500"><?= $jenis ?></div>
        <div style="font-size:0.75rem;color:var(--muted)"><?= $disetujui ?> disetujui · <?= $total_hari ?> total hari</div>
      </div>
      <span class="badge badge-info"><?= count($jml) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Absensi Karyawan -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Rekap Kehadiran Karyawan</div>
  </div>
  <table>
    <thead>
      <tr><th>Karyawan</th><th>Departemen</th><th>Hadir</th><th>Terlambat</th><th>Tidak Hadir</th><th>Tingkat Kehadiran</th></tr>
    </thead>
    <tbody>
    <?php foreach ($employees as $e):
      $sum = $abs_summary[$e['id']] ?? ['hadir'=>0,'terlambat'=>0,'absen'=>0];
      $total = $sum['hadir'] + $sum['terlambat'] + $sum['absen'];
      $pct = $total > 0 ? round(($sum['hadir']+$sum['terlambat'])/$total*100) : 0;
      $color = $pct>=90?'var(--success)':($pct>=75?'var(--warning)':'var(--danger)');
    ?>
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:0.6rem">
          <div style="width:30px;height:30px;background:var(--accent2);border-radius:7px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:0.72rem;color:var(--ink)">
            <?= strtoupper(substr($e['nama'],0,2)) ?>
          </div>
          <strong><?= htmlspecialchars($e['nama']) ?></strong>
        </div>
      </td>
      <td><span class="badge badge-info"><?= $e['departemen'] ?></span></td>
      <td style="color:var(--success);font-weight:600"><?= $sum['hadir'] ?></td>
      <td style="color:var(--warning);font-weight:600"><?= $sum['terlambat'] ?></td>
      <td style="color:var(--danger);font-weight:600"><?= $sum['absen'] ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:0.6rem">
          <div style="flex:1;height:7px;background:#f0ede8;border-radius:4px;overflow:hidden;min-width:80px">
            <div style="width:<?= $pct ?>%;height:100%;background:<?= $color ?>;border-radius:4px"></div>
          </div>
          <span style="font-size:0.82rem;font-weight:700;color:<?= $color ?>"><?= $pct ?>%</span>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
// Payroll Trend
new Chart(document.getElementById('payrollChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [{
      label: 'Total Penggajian',
      data: <?= json_encode($chart_totals) ?>,
      borderColor: '#c8522a',
      backgroundColor: 'rgba(200,82,42,0.08)',
      borderWidth: 2.5,
      pointBackgroundColor: '#c8522a',
      pointRadius: 5,
      tension: 0.35,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false } },
      y: {
        grid: { color: '#f0ede8' },
        ticks: { callback: v => 'Rp' + (v/1000000).toFixed(1) + 'jt' }
      }
    }
  }
});

// Dept Chart
new Chart(document.getElementById('deptChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($dept_names) ?>,
    datasets: [{
      data: <?= json_encode($dept_counts) ?>,
      backgroundColor: ['#c8522a','#e8b84b','#2a7a4b','#1a5f8a','#8a4fa0','#4a6f8a'],
      borderWidth: 3,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'right' }
    }
  }
});
</script>

<?php require_once '../includes/footer.php'; ?>
