<?php require_once '../includes/header.php'; ?>

<?php
$msg = '';
$employees = get_employees();
$payroll = get_payroll();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if ($_POST['action']==='generate') {
        $bulan = $_POST['bulan'];
        $generated = 0;
        foreach ($employees as $e) {
            if ($e['status']!=='Aktif') continue;
            $exists = array_filter($payroll, fn($p)=>$p['emp_id']==$e['id'] && $p['bulan']==$bulan);
            if ($exists) continue;
            $potongan = round($e['gaji_pokok'] * 0.05);
            $total = $e['gaji_pokok'] + $e['tunjangan'] - $potongan;
            $_SESSION['payroll'][] = [
                'id'=>next_id($_SESSION['payroll']),
                'emp_id'=>$e['id'],'bulan'=>$bulan,
                'gaji_pokok'=>$e['gaji_pokok'],'tunjangan'=>$e['tunjangan'],
                'potongan'=>$potongan,'lembur'=>0,'total'=>$total,
                'status'=>'Pending','tgl_bayar'=>''
            ];
            $generated++;
        }
        $msg = ['type'=>'success','text'=>"Berhasil generate $generated slip gaji untuk periode $bulan."];
        $payroll = get_payroll();
    }
    if ($_POST['action']==='bayar') {
        $pid = (int)$_POST['pay_id'];
        foreach ($_SESSION['payroll'] as &$p) {
            if ($p['id']==$pid) {
                $p['status'] = 'Dibayar';
                $p['tgl_bayar'] = date('Y-m-d');
                break;
            }
        }
        $msg = ['type'=>'success','text'=>'Gaji berhasil dibayarkan.'];
        $payroll = get_payroll();
    }
    if ($_POST['action']==='bayar_all') {
        $bulan = $_POST['bulan_all'];
        foreach ($_SESSION['payroll'] as &$p) {
            if ($p['bulan']==$bulan && $p['status']==='Pending') {
                $p['status'] = 'Dibayar';
                $p['tgl_bayar'] = date('Y-m-d');
            }
        }
        $msg = ['type'=>'success','text'=>"Semua gaji periode $bulan berhasil dibayarkan."];
        $payroll = get_payroll();
    }
    if ($_POST['action']==='update') {
        $pid = (int)$_POST['pay_id'];
        foreach ($_SESSION['payroll'] as &$p) {
            if ($p['id']==$pid) {
                $p['lembur'] = (int)$_POST['lembur'];
                $p['potongan'] = (int)$_POST['potongan'];
                $p['total'] = $p['gaji_pokok'] + $p['tunjangan'] + $p['lembur'] - $p['potongan'];
                break;
            }
        }
        $msg = ['type'=>'success','text'=>'Data gaji diperbarui.'];
        $payroll = get_payroll();
    }
}

$filter_bulan = $_GET['bulan'] ?? date('Y-m');
$display = array_filter($payroll, fn($p)=>$p['bulan']==$filter_bulan);

$total_dibayar = array_sum(array_map(fn($p)=>$p['status']==='Dibayar'?$p['total']:0, $display));
$total_pending = array_sum(array_map(fn($p)=>$p['status']==='Pending'?$p['total']:0, $display));
$count_pending = count(array_filter($display, fn($p)=>$p['status']==='Pending'));
$count_dibayar = count(array_filter($display, fn($p)=>$p['status']==='Dibayar'));

// Get unique months for filter
$months_available = array_unique(array_column($payroll,'bulan'));
rsort($months_available);
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card blue">
    <div class="stat-label">Total Slip</div>
    <div class="stat-value"><?= count($display) ?></div>
    <div class="stat-sub">periode <?= $filter_bulan ?></div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Sudah Dibayar</div>
    <div class="stat-value"><?= $count_dibayar ?></div>
    <div class="stat-sub"><?= rupiah($total_dibayar) ?></div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-label">Belum Dibayar</div>
    <div class="stat-value"><?= $count_pending ?></div>
    <div class="stat-sub"><?= rupiah($total_pending) ?></div>
  </div>
  <div class="stat-card orange">
    <div class="stat-label">Total Penggajian</div>
    <div class="stat-value" style="font-size:1rem"><?= rupiah($total_dibayar+$total_pending) ?></div>
    <div class="stat-sub">bulan ini</div>
  </div>
</div>

<!-- Controls -->
<div class="card" style="padding:1rem 1.5rem;margin-bottom:1rem">
  <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;justify-content:space-between">
    <div style="display:flex;gap:0.75rem;align-items:flex-end">
      <form method="GET" style="display:flex;gap:0.75rem;align-items:flex-end">
        <div>
          <label class="form-label">Filter Periode</label>
          <input type="month" name="bulan" class="form-control" value="<?= $filter_bulan ?>">
        </div>
        <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
      </form>
    </div>
    <div style="display:flex;gap:0.75rem">
      <form method="POST" style="display:flex;gap:0.75rem;align-items:flex-end">
        <input type="hidden" name="action" value="generate">
        <div>
          <label class="form-label">Generate Gaji Periode</label>
          <input type="month" name="bulan" class="form-control" value="<?= date('Y-m') ?>">
        </div>
        <button type="submit" class="btn btn-outline" onclick="return confirm('Generate slip gaji untuk periode ini?')">⚡ Generate</button>
      </form>
      <?php if ($count_pending > 0): ?>
      <form method="POST">
        <input type="hidden" name="action" value="bayar_all">
        <input type="hidden" name="bulan_all" value="<?= $filter_bulan ?>">
        <button type="submit" class="btn btn-success" onclick="return confirm('Bayar semua gaji pending periode ini?')" style="align-self:flex-end;height:40px">✓ Bayar Semua (<?= $count_pending ?>)</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <div class="card-title">💰 Slip Gaji — <?= date('F Y', strtotime($filter_bulan.'-01')) ?></div>
  </div>
  <table>
    <thead>
      <tr><th>Karyawan</th><th>Jabatan</th><th>Gaji Pokok</th><th>Tunjangan</th><th>Lembur</th><th>Potongan</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php foreach ($display as $p):
      $emp = get_employee($p['emp_id']);
      if (!$emp) continue;
    ?>
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:0.6rem">
          <div style="width:32px;height:32px;background:var(--accent2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:0.78rem;color:var(--ink);flex-shrink:0">
            <?= strtoupper(substr($emp['nama'],0,2)) ?>
          </div>
          <div>
            <strong><?= htmlspecialchars($emp['nama']) ?></strong>
            <div style="font-size:0.73rem;color:var(--muted)"><?= $emp['nik'] ?></div>
          </div>
        </div>
      </td>
      <td style="font-size:0.83rem"><?= htmlspecialchars($emp['jabatan']) ?></td>
      <td><?= rupiah($p['gaji_pokok']) ?></td>
      <td style="color:var(--success)"><?= rupiah($p['tunjangan']) ?></td>
      <td style="color:var(--info)">
        <?= $p['lembur'] > 0 ? '<span style="color:var(--info)">'.rupiah($p['lembur']).'</span>' : '<span style="color:var(--muted)">—</span>' ?>
      </td>
      <td style="color:var(--danger)">(<?= rupiah($p['potongan']) ?>)</td>
      <td><strong style="font-family:'Syne',sans-serif"><?= rupiah($p['total']) ?></strong></td>
      <td>
        <span class="badge <?= $p['status']==='Dibayar'?'badge-success':'badge-warning' ?>">
          <?= $p['status'] ?>
        </span>
        <?php if ($p['tgl_bayar']): ?>
        <div style="font-size:0.7rem;color:var(--muted)"><?= date('d M', strtotime($p['tgl_bayar'])) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <div style="display:flex;gap:0.3rem">
          <?php if ($p['status']==='Pending'): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="bayar">
            <input type="hidden" name="pay_id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm" title="Bayar">💳</button>
          </form>
          <?php endif; ?>
          <button class="btn btn-outline btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($p)) ?>)" title="Edit">✏️</button>
          <button class="btn btn-outline btn-sm" onclick="printSlip(<?= htmlspecialchars(json_encode(['pay'=>$p,'emp'=>$emp])) ?>)" title="Cetak Slip">🖨️</button>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($display)): ?>
    <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:2.5rem">
      Belum ada data payroll untuk periode <?= $filter_bulan ?>.<br>
      <small>Gunakan tombol "Generate" untuk membuat slip gaji otomatis.</small>
    </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL EDIT -->
<div id="modalEdit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:420px">
    <div style="display:flex;justify-content:space-between;margin-bottom:1.5rem">
      <h3 style="font-family:'Syne',sans-serif;font-weight:700">Edit Komponen Gaji</h3>
      <button onclick="document.getElementById('modalEdit').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="pay_id" id="edit_pay_id">
      <div class="form-group">
        <label class="form-label">Tunjangan Lembur</label>
        <input type="number" name="lembur" id="edit_lembur" class="form-control" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">Total Potongan</label>
        <input type="number" name="potongan" id="edit_potongan" class="form-control" min="0">
      </div>
      <div style="display:flex;gap:0.75rem;justify-content:flex-end">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL SLIP CETAK -->
<div id="modalSlip" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center">
  <div style="background:white;border-radius:16px;padding:2rem;width:480px;position:relative">
    <button onclick="document.getElementById('modalSlip').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.3rem;cursor:pointer">×</button>
    <div id="slip-content"></div>
    <div style="text-align:center;margin-top:1.5rem">
      <button onclick="window.print()" class="btn btn-primary">🖨️ Print Slip</button>
    </div>
  </div>
</div>

<script>
function openEdit(p) {
  document.getElementById('edit_pay_id').value = p.id;
  document.getElementById('edit_lembur').value = p.lembur;
  document.getElementById('edit_potongan').value = p.potongan;
  document.getElementById('modalEdit').style.display = 'flex';
}

function rupiah(n) {
  return 'Rp ' + parseInt(n).toLocaleString('id-ID');
}

function printSlip(data) {
  const p = data.pay, e = data.emp;
  const html = `
    <div style="border:2px solid #0f1117;border-radius:12px;padding:1.5rem;font-family:'DM Sans',sans-serif">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #eee">
        <div>
          <div style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:#0f1117">HRMS Pro</div>
          <div style="font-size:0.75rem;color:#8a8680">Slip Gaji Karyawan</div>
        </div>
        <div style="text-align:right;font-size:0.82rem;color:#8a8680">Periode: <strong>${p.bulan}</strong></div>
      </div>
      <div style="margin-bottom:1rem">
        <div style="font-weight:700;font-size:1rem">${e.nama}</div>
        <div style="font-size:0.82rem;color:#8a8680">${e.jabatan} · ${e.departemen}</div>
        <div style="font-size:0.78rem;color:#8a8680">NIK: ${e.nik}</div>
      </div>
      <table style="width:100%;font-size:0.85rem;border-collapse:collapse">
        <tr style="background:#f5f3ee"><td style="padding:0.5rem">Gaji Pokok</td><td style="text-align:right;padding:0.5rem">${rupiah(p.gaji_pokok)}</td></tr>
        <tr><td style="padding:0.5rem">Tunjangan</td><td style="text-align:right;padding:0.5rem;color:green">+ ${rupiah(p.tunjangan)}</td></tr>
        <tr style="background:#f5f3ee"><td style="padding:0.5rem">Lembur</td><td style="text-align:right;padding:0.5rem;color:blue">+ ${rupiah(p.lembur)}</td></tr>
        <tr><td style="padding:0.5rem">Potongan</td><td style="text-align:right;padding:0.5rem;color:red">- ${rupiah(p.potongan)}</td></tr>
        <tr style="border-top:2px solid #0f1117;font-weight:700;font-family:'Syne',sans-serif">
          <td style="padding:0.7rem">TOTAL GAJI</td>
          <td style="text-align:right;padding:0.7rem;font-size:1.1rem">${rupiah(p.total)}</td>
        </tr>
      </table>
      <div style="margin-top:1rem;font-size:0.75rem;color:#8a8680;text-align:center">
        Status: <strong style="color:${p.status==='Dibayar'?'green':'orange'}">${p.status}</strong>
        ${p.tgl_bayar ? ' · Dibayar: ' + p.tgl_bayar : ''}
      </div>
    </div>
  `;
  document.getElementById('slip-content').innerHTML = html;
  document.getElementById('modalSlip').style.display = 'flex';
}
</script>

<?php require_once '../includes/footer.php'; ?>
