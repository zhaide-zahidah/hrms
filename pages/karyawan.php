<?php require_once '../includes/header.php'; ?>

<?php
$msg = '';
$employees = get_employees();

// ADD EMPLOYEE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if ($_POST['action']==='add') {
        $new = [
            'id'         => next_id($_SESSION['employees']),
            'nik'        => strtoupper(trim($_POST['nik'])),
            'nama'       => trim($_POST['nama']),
            'jabatan'    => trim($_POST['jabatan']),
            'departemen' => trim($_POST['departemen']),
            'gaji_pokok' => (int)str_replace(['.','Rp',' '], '', $_POST['gaji_pokok']),
            'tunjangan'  => (int)str_replace(['.','Rp',' '], '', $_POST['tunjangan']),
            'status'     => $_POST['status'],
            'join_date'  => $_POST['join_date'],
            'foto'       => '',
        ];
        $_SESSION['employees'][] = $new;
        $msg = ['type'=>'success','text'=>"Karyawan {$new['nama']} berhasil ditambahkan."];
        $employees = get_employees();
    }
    if ($_POST['action']==='delete') {
        $del_id = (int)$_POST['del_id'];
        $_SESSION['employees'] = array_values(array_filter($_SESSION['employees'], fn($e)=>$e['id']!=$del_id));
        $msg = ['type'=>'success','text'=>'Karyawan berhasil dihapus.'];
        $employees = get_employees();
    }
    if ($_POST['action']==='edit') {
        foreach ($_SESSION['employees'] as &$e) {
            if ($e['id']==(int)$_POST['edit_id']) {
                $e['nama']       = trim($_POST['nama']);
                $e['jabatan']    = trim($_POST['jabatan']);
                $e['departemen'] = trim($_POST['departemen']);
                $e['gaji_pokok'] = (int)str_replace(['.','Rp',' '], '', $_POST['gaji_pokok']);
                $e['tunjangan']  = (int)str_replace(['.','Rp',' '], '', $_POST['tunjangan']);
                $e['status']     = $_POST['status'];
                break;
            }
        }
        $msg = ['type'=>'success','text'=>'Data karyawan berhasil diperbarui.'];
        $employees = get_employees();
    }
}

$depts = array_unique(array_column($employees, 'departemen'));
$filter_dept = $_GET['dept'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = strtolower($_GET['q'] ?? '');
$display = array_filter($employees, function($e) use ($filter_dept,$filter_status,$search) {
    if ($filter_dept && $e['departemen']!=$filter_dept) return false;
    if ($filter_status && $e['status']!=$filter_status) return false;
    if ($search && !str_contains(strtolower($e['nama']),$search) && !str_contains(strtolower($e['nik']),$search)) return false;
    return true;
});
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
<?php endif; ?>

<!-- Filter & Search -->
<div class="card" style="padding:1rem 1.5rem;margin-bottom:1rem">
  <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label class="form-label">Cari</label>
      <input type="text" name="q" class="form-control" style="width:200px" placeholder="Nama / NIK..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div>
      <label class="form-label">Departemen</label>
      <select name="dept" class="form-control" style="width:150px">
        <option value="">Semua</option>
        <?php foreach ($depts as $d): ?>
        <option value="<?= $d ?>" <?= $filter_dept==$d?'selected':'' ?>><?= $d ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">Status</label>
      <select name="status" class="form-control" style="width:120px">
        <option value="">Semua</option>
        <option value="Aktif" <?= $filter_status=='Aktif'?'selected':'' ?>>Aktif</option>
        <option value="Cuti" <?= $filter_status=='Cuti'?'selected':'' ?>>Cuti</option>
        <option value="Nonaktif" <?= $filter_status=='Nonaktif'?'selected':'' ?>>Nonaktif</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">🔍 Filter</button>
    <a href="karyawan.php" class="btn btn-outline">Reset</a>
  </form>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <div class="card-title">👥 Daftar Karyawan (<?= count($display) ?>)</div>
    <button class="btn btn-primary" onclick="document.getElementById('modalAdd').style.display='flex'">+ Tambah Karyawan</button>
  </div>
  <table>
    <thead>
      <tr><th>NIK</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>Gaji Pokok</th><th>Tgl Bergabung</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php foreach ($display as $e): ?>
    <tr>
      <td><code style="font-size:0.8rem;background:#f0ede8;padding:2px 6px;border-radius:4px"><?= $e['nik'] ?></code></td>
      <td>
        <div style="display:flex;align-items:center;gap:0.6rem">
          <div style="width:32px;height:32px;background:var(--accent2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:0.78rem;color:var(--ink);flex-shrink:0">
            <?= strtoupper(substr($e['nama'],0,2)) ?>
          </div>
          <strong><?= htmlspecialchars($e['nama']) ?></strong>
        </div>
      </td>
      <td><?= htmlspecialchars($e['jabatan']) ?></td>
      <td><span class="badge badge-info"><?= $e['departemen'] ?></span></td>
      <td><?= rupiah($e['gaji_pokok']) ?></td>
      <td><?= date('d M Y', strtotime($e['join_date'])) ?></td>
      <td>
        <span class="badge <?= $e['status']==='Aktif'?'badge-success':($e['status']==='Cuti'?'badge-warning':'badge-muted') ?>">
          <?= $e['status'] ?>
        </span>
      </td>
      <td>
        <button class="btn btn-outline btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($e)) ?>)">✏️</button>
        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus karyawan ini?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="del_id" value="<?= $e['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($display)): ?>
    <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:2rem">Tidak ada data karyawan</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL ADD -->
<div id="modalAdd" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:580px;max-height:90vh;overflow-y:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
      <h3 style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700">Tambah Karyawan Baru</h3>
      <button onclick="document.getElementById('modalAdd').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">NIK *</label>
          <input type="text" name="nik" class="form-control" placeholder="EMP007" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama" class="form-control" placeholder="Nama karyawan" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jabatan *</label>
          <input type="text" name="jabatan" class="form-control" placeholder="Staff / Manager..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Departemen *</label>
          <input type="text" name="departemen" class="form-control" placeholder="IT / Finance..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Gaji Pokok *</label>
          <input type="number" name="gaji_pokok" class="form-control" placeholder="5000000" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tunjangan</label>
          <input type="number" name="tunjangan" class="form-control" placeholder="500000" value="0">
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Bergabung</label>
          <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option>Aktif</option><option>Nonaktif</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:0.5rem">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAdd').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT -->
<div id="modalEdit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:580px;max-height:90vh;overflow-y:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
      <h3 style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700">Edit Karyawan</h3>
      <button onclick="document.getElementById('modalEdit').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama" id="edit_nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Jabatan *</label>
          <input type="text" name="jabatan" id="edit_jabatan" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Departemen *</label>
          <input type="text" name="departemen" id="edit_departemen" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Gaji Pokok</label>
          <input type="number" name="gaji_pokok" id="edit_gaji" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Tunjangan</label>
          <input type="number" name="tunjangan" id="edit_tunjangan" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" id="edit_status" class="form-control">
            <option>Aktif</option><option>Cuti</option><option>Nonaktif</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:0.5rem">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEdit(e) {
  document.getElementById('edit_id').value = e.id;
  document.getElementById('edit_nama').value = e.nama;
  document.getElementById('edit_jabatan').value = e.jabatan;
  document.getElementById('edit_departemen').value = e.departemen;
  document.getElementById('edit_gaji').value = e.gaji_pokok;
  document.getElementById('edit_tunjangan').value = e.tunjangan;
  document.getElementById('edit_status').value = e.status;
  document.getElementById('modalEdit').style.display = 'flex';
}
</script>

<?php require_once '../includes/footer.php'; ?>
