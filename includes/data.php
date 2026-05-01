<?php
// =============================================
// DATA STORE — Simulasi Database (Session-based)
// =============================================

function init_data() {
    if (!isset($_SESSION['employees'])) {
        $_SESSION['employees'] = [
            ['id'=>1,'nik'=>'EMP001','nama'=>'Andi Prasetyo','jabatan'=>'Software Engineer','departemen'=>'IT','gaji_pokok'=>8500000,'tunjangan'=>1500000,'status'=>'Aktif','join_date'=>'2022-03-15','foto'=>''],
            ['id'=>2,'nik'=>'EMP002','nama'=>'Siti Rahayu','jabatan'=>'HR Specialist','departemen'=>'HRD','gaji_pokok'=>7000000,'tunjangan'=>1000000,'status'=>'Aktif','join_date'=>'2021-07-01','foto'=>''],
            ['id'=>3,'nik'=>'EMP003','nama'=>'Budi Hartono','jabatan'=>'Marketing Manager','departemen'=>'Marketing','gaji_pokok'=>9500000,'tunjangan'=>2000000,'status'=>'Aktif','join_date'=>'2020-01-10','foto'=>''],
            ['id'=>4,'nik'=>'EMP004','nama'=>'Dewi Lestari','jabatan'=>'Finance Analyst','departemen'=>'Finance','gaji_pokok'=>7500000,'tunjangan'=>1200000,'status'=>'Aktif','join_date'=>'2023-02-20','foto'=>''],
            ['id'=>5,'nik'=>'EMP005','nama'=>'Rizky Firmansyah','jabatan'=>'UI/UX Designer','departemen'=>'IT','gaji_pokok'=>7800000,'tunjangan'=>1000000,'status'=>'Aktif','join_date'=>'2022-09-05','foto'=>''],
            ['id'=>6,'nik'=>'EMP006','nama'=>'Mega Wulandari','jabatan'=>'Accountant','departemen'=>'Finance','gaji_pokok'=>6500000,'tunjangan'=>800000,'status'=>'Cuti','join_date'=>'2021-04-12','foto'=>''],
        ];
    }
    if (!isset($_SESSION['payroll'])) {
        $_SESSION['payroll'] = [
            ['id'=>1,'emp_id'=>1,'bulan'=>'2025-04','gaji_pokok'=>8500000,'tunjangan'=>1500000,'potongan'=>500000,'lembur'=>600000,'total'=>10100000,'status'=>'Dibayar','tgl_bayar'=>'2025-04-28'],
            ['id'=>2,'emp_id'=>2,'bulan'=>'2025-04','gaji_pokok'=>7000000,'tunjangan'=>1000000,'potongan'=>350000,'lembur'=>0,'total'=>7650000,'status'=>'Dibayar','tgl_bayar'=>'2025-04-28'],
            ['id'=>3,'emp_id'=>3,'bulan'=>'2025-04','gaji_pokok'=>9500000,'tunjangan'=>2000000,'potongan'=>700000,'lembur'=>1000000,'total'=>11800000,'status'=>'Dibayar','tgl_bayar'=>'2025-04-28'],
            ['id'=>4,'emp_id'=>4,'bulan'=>'2025-04','gaji_pokok'=>7500000,'tunjangan'=>1200000,'potongan'=>400000,'lembur'=>0,'total'=>8300000,'status'=>'Dibayar','tgl_bayar'=>'2025-04-28'],
            ['id'=>5,'emp_id'=>5,'bulan'=>'2025-04','gaji_pokok'=>7800000,'tunjangan'=>1000000,'potongan'=>390000,'lembur'=>300000,'total'=>8710000,'status'=>'Pending','tgl_bayar'=>''],
            ['id'=>6,'emp_id'=>1,'bulan'=>'2025-05','gaji_pokok'=>8500000,'tunjangan'=>1500000,'potongan'=>500000,'lembur'=>0,'total'=>9500000,'status'=>'Pending','tgl_bayar'=>''],
        ];
    }
    if (!isset($_SESSION['cuti'])) {
        $_SESSION['cuti'] = [
            ['id'=>1,'emp_id'=>2,'jenis'=>'Cuti Tahunan','tgl_mulai'=>'2025-05-05','tgl_selesai'=>'2025-05-08','lama'=>4,'alasan'=>'Liburan keluarga','status'=>'Disetujui','catatan'=>''],
            ['id'=>2,'emp_id'=>5,'jenis'=>'Cuti Sakit','tgl_mulai'=>'2025-04-28','tgl_selesai'=>'2025-04-29','lama'=>2,'alasan'=>'Sakit demam','status'=>'Disetujui','catatan'=>''],
            ['id'=>3,'emp_id'=>6,'jenis'=>'Cuti Melahirkan','tgl_mulai'=>'2025-04-01','tgl_selesai'=>'2025-06-30','lama'=>90,'alasan'=>'Melahirkan','status'=>'Disetujui','catatan'=>''],
            ['id'=>4,'emp_id'=>4,'jenis'=>'Cuti Tahunan','tgl_mulai'=>'2025-05-12','tgl_selesai'=>'2025-05-14','lama'=>3,'alasan'=>'Acara keluarga','status'=>'Menunggu','catatan'=>''],
            ['id'=>5,'emp_id'=>3,'jenis'=>'Cuti Darurat','tgl_mulai'=>'2025-05-02','tgl_selesai'=>'2025-05-02','lama'=>1,'alasan'=>'Urusan mendadak','status'=>'Menunggu','catatan'=>''],
        ];
    }
    if (!isset($_SESSION['absensi'])) {
        $absensi = [];
        $id = 1;
        $employees = [1,2,3,4,5];
        $days = ['2025-04-28','2025-04-29','2025-04-30','2025-05-01','2025-05-02'];
        foreach ($days as $day) {
            foreach ($employees as $emp_id) {
                $rand = rand(1,10);
                $masuk = $rand > 2 ? sprintf('0%d:%02d', rand(7,8), rand(0,59)) : '';
                $keluar = $masuk ? sprintf('1%d:%02d', rand(6,7), rand(0,59)) : '';
                $status = $masuk == '' ? 'Tidak Hadir' : ($rand == 3 ? 'Terlambat' : 'Hadir');
                $absensi[] = ['id'=>$id++,'emp_id'=>$emp_id,'tanggal'=>$day,'jam_masuk'=>$masuk,'jam_keluar'=>$keluar,'status'=>$status,'keterangan'=>''];
            }
        }
        $_SESSION['absensi'] = $absensi;
    }
}

function get_employees() { return $_SESSION['employees'] ?? []; }
function get_employee($id) {
    foreach ($_SESSION['employees'] as $e) { if ($e['id'] == $id) return $e; }
    return null;
}
function get_payroll() { return $_SESSION['payroll'] ?? []; }
function get_cuti() { return $_SESSION['cuti'] ?? []; }
function get_absensi() { return $_SESSION['absensi'] ?? []; }
function next_id($arr) { return count($arr) > 0 ? max(array_column($arr,'id'))+1 : 1; }
function rupiah($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
function get_emp_name($id) {
    $e = get_employee($id);
    return $e ? $e['nama'] : 'Unknown';
}
?>
