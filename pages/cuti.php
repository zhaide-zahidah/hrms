<?php require_once '../includes/header.php'; ?>

<?php
$msg = '';
$employees = get_employees();
$cuti_list = get_cuti();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if ($_POST['action']==='add') {
        $tgl1 = strtotime($_POST['tgl_mulai']);
        $tgl2 = strtotime($_POST['tgl_selesai']);
        $lama = max(1, round(($tgl2-$tgl1)/86400)+1);
        $new = [
            'id'          => next_id($_SESSION['cuti']),
            'emp_id'      => (int)$_POST['emp_id'],
            'jenis'       => $_POST['jenis'],
            'tgl_mulai'   => $_POST['tgl_mulai'],
            'tgl_selesai' => $_POST['tgl_selesai'],
            'lama'        => $lama,
            'alasan'      => trim($_POST['alasan']),
            'status'      => 'Menunggu',
            'catatan'     => '',
        ];
        $_SESSION['cuti'][] = $new;
        $msg = ['type'=>'success','text'=>'Pengajuan cuti berhasil dikirim dan menunggu persetujuan.'];
    }
    if ($_POST['action']==='approve') {
        foreach ($_SESSION['cuti'] as &$c) {
            if ($c['id']==(int)$_POST['cuti_id']) {
                $c['status'] = 'Disetujui';
                $c['catatan'] = trim($_POST['catatan'] ?? '');
                // Update employee status
                foreach ($_SESSION['employees'] as &$e) {
                    if ($e['id']==$c['emp_id']) { $e['status']='Cuti'; break; }
                }
                break;
            }
        }
        $msg = ['type'=>'success','text'=>'Cuti disetujui.'];
    }
    if ($_POST['action']==='reject') {
        foreach ($_SESSION['cuti'] as &$c) {
            if ($c['id']==(int)$_POST['cuti_id']) {
                $c['status'] = 'Ditolak';
                $c['catatan'] = trim($_POST['catatan'] ?? '');
                break;
            }
        }
        $msg = ['type'=>'success','text'=>'Cuti ditolak.'];
    }
    $cuti_list = get_cuti();
}

$filter_status = $_GET['status'] ?? '';
$display = $filter_status ? array_filter($cuti_list, fn($c)=>$c['status']==$filter_status) : $cuti_list;
$display = array_reverse($display);

$stats = [
    'Menunggu' => count(array_filter($cuti_list, fn($c)=>$c['status']==='Menunggu')),
    'Disetujui' => count(array_filter($cuti_list, fn($c)=>$c['status']==='Disetujui')),
    'Ditolak' => count(array_filter($cuti_list, fn($c)=>$c['status']==='Ditolak')),
];
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
<?php endif; ?>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card yellow">
    <div class="stat-label">Menunggu Persetujuan</div>
    <div class="stat-value"><?= $stats['Menunggu'] ?></div>
    <div class="stat-sub">pengajuan pending</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Disetujui</div>
    <div class="stat-value"><?= $stats['Disetujui'] ?></div>
    <div class="stat-sub">total disetujui</div>
  </div>
  <div class="stat-card orange">
    <div class="stat-label">Ditolak</div>
    <div class="stat-value"><?= $stats['Ditolak'] ?></div>
    <div class="stat-sub">total ditolak</div>
  </div>
</div>

<!-- Filter & Actions -->
<div style="display:flex;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;align-items:center">
  <div style="display:flex;gap:0.5rem">
    <?php $statuses = [''=> 'Semua','Menunggu'=>'Menunggu','Disetujui'=>'Disetujui','Ditolak'=>'Ditolak']; ?>
    <?php foreach ($statuses as $key=>$label): ?>
    <a href="?status=<?= $key ?>" class="btn <?= $filter_status==$key?'btn-primary':'btn-outline' ?> btn-sm"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
  <button class="btn btn-primary" style="margin-left:auto" onclick="document.getElementById('modalAdd').style.display='flex'">+ Ajukan Cuti</button>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">🌴 Daftar Pengajuan Cuti</div>
  </div>
  <table>
    <thead>
      <tr><th>Karyawan</th><th>Jenis Cuti</th><th>Mulai</th><th>Selesai</th><th>Lama</th><th>Alasan</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php foreach ($display as $c):
      $badge = match($c['status']) {
        'Disetujui'=>'badge-success','Menunggu'=>'badge-warning',
        'Ditolak'=>'badge-danger',default=>'badge-muted'
      };
    ?>
    <tr>
      <td>
        <div>
          <strong><?= htmlspecialchars(get_emp_name($c['emp_id'])) ?></strong>
          <?php $emp = get_employee($c['emp_id']); ?>
          <?php if ($emp): ?>
          <div style="font-size:0.75rem;color:var(--muted)"><?= $emp['jabatan'] ?></div>
          <?php endif; ?>
        </div>
      </td>
      <td>
        <?php
        $jenis_icons = ['Cuti Tahunan'=>'🏖️','Cuti Sakit'=>'🏥','Cuti Melahirkan'=>'👶','Cuti Darurat'=>'🚨'];
        $icon = $jenis_icons[$c['jenis']] ?? '📋';
        ?>
        <?= $icon ?> <?= $c['jenis'] ?>
      </td>
      <td><?= date('d M Y', strtotime($c['tgl_mulai'])) ?></td>
      <td><?= date('d M Y', strtotime($c['tgl_selesai'])) ?></td>
      <td><strong><?= $c['lama'] ?></strong> hari</td>
      <td style="max-width:150px;font-size:0.83rem"><?= htmlspecialchars($c['alasan']) ?></td>
      <td>
        <span class="badge <?= $badge ?>"><?= $c['status'] ?></span>
        <?php if ($c['catatan']): ?>
        <div style="font-size:0.72rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($c['catatan']) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($c['status']==='Menunggu'): ?>
        <div style="display:flex;gap:0.4rem">
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="cuti_id" value="<?= $c['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm" title="Setujui">✓</button>
          </form>
          <button class="btn btn-danger btn-sm" onclick="openReject(<?= $c['id'] ?>)" title="Tolak">✗</button>
        </div>
        <?php else: ?>
        <span style="color:var(--muted);font-size:0.8rem">—</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($display)): ?>
    <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2rem">Tidak ada data cuti</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL Add -->
<div id="modalAdd" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:500px">
    <div style="display:flex;justify-content:space-between;margin-bottom:1.5rem">
      <h3 style="font-family:'Syne',sans-serif;font-weight:700">Ajukan Cuti</h3>
      <button onclick="document.getElementById('modalAdd').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label class="form-label">Karyawan</label>
        <select name="emp_id" class="form-control" required>
          <?php foreach ($employees as $e): ?>
          <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?> — <?= $e['jabatan'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Jenis Cuti</label>
        <select name="jenis" class="form-control" required>
          <option>Cuti Tahunan</option>
          <option>Cuti Sakit</option>
          <option>Cuti Melahirkan</option>
          <option>Cuti Darurat</option>
        </select>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" name="tgl_mulai" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Selesai</label>
          <input type="date" name="tgl_selesai" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Alasan Cuti</label>
        <textarea name="alasan" class="form-control" rows="3" placeholder="Jelaskan alasan cuti..." required style="resize:vertical"></textarea>
      </div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAdd').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL REJECT -->
<div id="modalReject" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:420px">
    <h3 style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:1.25rem">Tolak Pengajuan Cuti</h3>
    <form method="POST">
      <input type="hidden" name="action" value="reject">
      <input type="hidden" name="cuti_id" id="reject_id">
      <div class="form-group">
        <label class="form-label">Alasan Penolakan</label>
        <textarea name="catatan" class="form-control" rows="3" placeholder="Berikan alasan penolakan..." style="resize:vertical"></textarea>
      </div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalReject').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Cuti</button>
      </div>
    </form>
  </div>
</div>
<script>
function openReject(id) {
  document.getElementById('reject_id').value = id;
  document.getElementById('modalReject').style.display = 'flex';
}
</script>

<?php require_once '../includes/footer.php'; ?>
