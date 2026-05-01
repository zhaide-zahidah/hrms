<?php require_once '../includes/header.php'; ?>

<?php
$msg = '';
$employees = get_employees();
$absensi = get_absensi();

// Add absensi
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if ($_POST['action']==='add') {
        $new = [
            'id'         => next_id($_SESSION['absensi']),
            'emp_id'     => (int)$_POST['emp_id'],
            'tanggal'    => $_POST['tanggal'],
            'jam_masuk'  => $_POST['jam_masuk'],
            'jam_keluar' => $_POST['jam_keluar'],
            'status'     => $_POST['status'],
            'keterangan' => trim($_POST['keterangan']),
        ];
        $_SESSION['absensi'][] = $new;
        $msg = ['type'=>'success','text'=>'Absensi berhasil ditambahkan.'];
    }
    if ($_POST['action']==='checkin') {
        $emp_id = (int)$_POST['emp_id'];
        $today = date('Y-m-d');
        // check existing
        $exists = false;
        foreach ($_SESSION['absensi'] as &$a) {
            if ($a['emp_id']==$emp_id && $a['tanggal']==$today) {
                $a['jam_masuk'] = date('H:i');
                $a['status'] = (date('H:i') > '08:30') ? 'Terlambat' : 'Hadir';
                $exists = true; break;
            }
        }
        if (!$exists) {
            $_SESSION['absensi'][] = [
                'id'=>next_id($_SESSION['absensi']),
                'emp_id'=>$emp_id, 'tanggal'=>$today,
                'jam_masuk'=>date('H:i'), 'jam_keluar'=>'',
                'status'=>(date('H:i')>'08:30')?'Terlambat':'Hadir','keterangan'=>''
            ];
        }
        $msg = ['type'=>'success','text'=>'Check-in berhasil pada '.date('H:i')];
    }
    if ($_POST['action']==='checkout') {
        $emp_id = (int)$_POST['emp_id'];
        $today = date('Y-m-d');
        foreach ($_SESSION['absensi'] as &$a) {
            if ($a['emp_id']==$emp_id && $a['tanggal']==$today) {
                $a['jam_keluar'] = date('H:i');
                break;
            }
        }
        $msg = ['type'=>'success','text'=>'Check-out berhasil pada '.date('H:i')];
    }
    $absensi = get_absensi();
}

// Filter
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_emp = $_GET['emp'] ?? '';
$display = array_filter($absensi, function($a) use ($filter_date,$filter_emp) {
    if ($filter_date && $a['tanggal']!=$filter_date) return false;
    if ($filter_emp && $a['emp_id']!=$filter_emp) return false;
    return true;
});

// Stats for selected date
$hadir = count(array_filter($display, fn($a)=>$a['status']==='Hadir'));
$terlambat = count(array_filter($display, fn($a)=>$a['status']==='Terlambat'));
$absen = count(array_filter($display, fn($a)=>$a['status']==='Tidak Hadir'));
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
<?php endif; ?>

<!-- Quick Check-in / Check-out -->
<div class="card" style="background:linear-gradient(135deg,#0f1117,#1e2333);color:white;margin-bottom:1.5rem">
  <div class="card-header" style="border-color:rgba(255,255,255,0.1)">
    <div class="card-title" style="color:white">⚡ Check-in / Check-out Cepat</div>
    <span style="color:rgba(255,255,255,0.5);font-size:0.85rem"><?= date('l, d F Y — H:i') ?></span>
  </div>
  <div style="display:flex;gap:1rem;flex-wrap:wrap">
    <form method="POST" style="display:flex;gap:0.75rem;align-items:flex-end">
      <input type="hidden" name="action" value="checkin">
      <div>
        <label class="form-label" style="color:rgba(255,255,255,0.6)">Karyawan</label>
        <select name="emp_id" class="form-control" style="width:200px">
          <?php foreach ($employees as $e): ?>
          <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-success">🟢 Check-in</button>
    </form>
    <form method="POST" style="display:flex;gap:0.75rem;align-items:flex-end">
      <input type="hidden" name="action" value="checkout">
      <div>
        <label class="form-label" style="color:rgba(255,255,255,0.6)">Karyawan</label>
        <select name="emp_id" class="form-control" style="width:200px">
          <?php foreach ($employees as $e): ?>
          <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-danger">🔴 Check-out</button>
    </form>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1rem">
  <div class="stat-card green">
    <div class="stat-label">Hadir</div>
    <div class="stat-value"><?= $hadir ?></div>
    <div class="stat-sub">tanggal <?= date('d M', strtotime($filter_date)) ?></div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-label">Terlambat</div>
    <div class="stat-value"><?= $terlambat ?></div>
    <div class="stat-sub">melebihi jam 08:30</div>
  </div>
  <div class="stat-card orange">
    <div class="stat-label">Tidak Hadir</div>
    <div class="stat-value"><?= $absen ?></div>
    <div class="stat-sub">tanpa keterangan</div>
  </div>
</div>

<!-- Filter -->
<div class="card" style="padding:1rem 1.5rem;margin-bottom:1rem">
  <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label class="form-label">Tanggal</label>
      <input type="date" name="date" class="form-control" value="<?= $filter_date ?>">
    </div>
    <div>
      <label class="form-label">Karyawan</label>
      <select name="emp" class="form-control" style="width:180px">
        <option value="">Semua</option>
        <?php foreach ($employees as $e): ?>
        <option value="<?= $e['id'] ?>" <?= $filter_emp==$e['id']?'selected':'' ?>><?= htmlspecialchars($e['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">🔍 Filter</button>
    <a href="absensi.php" class="btn btn-outline">Reset</a>
    <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAdd').style.display='flex'" style="margin-left:auto">+ Input Manual</button>
  </form>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Data Absensi</div>
    <span style="font-size:0.82rem;color:var(--muted)"><?= count($display) ?> record</span>
  </div>
  <table>
    <thead>
      <tr><th>#</th><th>Karyawan</th><th>Tanggal</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Durasi</th><th>Status</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
    <?php $i=1; foreach ($display as $a):
      $dur = '';
      if ($a['jam_masuk'] && $a['jam_keluar']) {
        $t1 = strtotime($a['jam_masuk']); $t2 = strtotime($a['jam_keluar']);
        $diff = $t2 - $t1;
        $dur = floor($diff/3600).'j '.floor(($diff%3600)/60).'m';
      }
      $badge = match($a['status']) { 'Hadir'=>'badge-success','Terlambat'=>'badge-warning',default=>'badge-danger' };
    ?>
    <tr>
      <td style="color:var(--muted)"><?= $i++ ?></td>
      <td><strong><?= htmlspecialchars(get_emp_name($a['emp_id'])) ?></strong></td>
      <td><?= date('d M Y', strtotime($a['tanggal'])) ?></td>
      <td><?= $a['jam_masuk'] ? '<span style="color:var(--success);font-weight:600">'.$a['jam_masuk'].'</span>' : '<span style="color:var(--muted)">—</span>' ?></td>
      <td><?= $a['jam_keluar'] ? $a['jam_keluar'] : '<span style="color:var(--muted)">—</span>' ?></td>
      <td><?= $dur ?: '—' ?></td>
      <td><span class="badge <?= $badge ?>"><?= $a['status'] ?></span></td>
      <td style="color:var(--muted);font-size:0.82rem"><?= $a['keterangan'] ?: '—' ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($display)): ?>
    <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2rem">Tidak ada data absensi untuk filter ini</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL ADD -->
<div id="modalAdd" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:480px">
    <div style="display:flex;justify-content:space-between;margin-bottom:1.5rem">
      <h3 style="font-family:'Syne',sans-serif;font-weight:700">Input Absensi Manual</h3>
      <button onclick="document.getElementById('modalAdd').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label class="form-label">Karyawan</label>
        <select name="emp_id" class="form-control" required>
          <?php foreach ($employees as $e): ?>
          <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option>Hadir</option><option>Terlambat</option><option>Tidak Hadir</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Jam Masuk</label>
          <input type="time" name="jam_masuk" class="form-control" value="08:00">
        </div>
        <div class="form-group">
          <label class="form-label">Jam Keluar</label>
          <input type="time" name="jam_keluar" class="form-control" value="17:00">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Keterangan</label>
        <input type="text" name="keterangan" class="form-control" placeholder="(opsional)">
      </div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAdd').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
