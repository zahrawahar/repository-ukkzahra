<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$active_page = 'dashboard';

// Ambil input pencarian dan filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_tgl = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

// Cek apakah sedang dalam mode pencarian (untuk memunculkan tombol kembali)
$is_searching = ($search != '' || $filter_tgl != '');

$query_str = "SELECT t.tanggal_transaksi, b.nama AS nama_barang, t.status, t.stok, b.harga, (t.stok * b.harga) AS total 
              FROM transaksi t 
              JOIN barang b ON t.id_barang = b.id_barang 
              WHERE 1=1";

if ($search != '') {
    $query_str .= " AND b.nama LIKE '%$search%'";
}
if ($filter_tgl != '') {
    $query_str .= " AND DATE(t.tanggal_transaksi) = '$filter_tgl'";
}

$query_str .= " ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC";
$semua_riwayat = $conn->query($query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi | GudangBarang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; font-family: 'Inter', sans-serif; color: #333; }
        .content-wrapper { padding: 40px 20px; max-width: 1200px; margin: auto; }
        .glass-card { border: none; border-radius: 15px; background: #fff; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); overflow: hidden; }
        .table thead th { background: #f8f9fc; color: #4e73df; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; border: none; padding: 15px; }
        .table td { padding: 15px; border-color: #f1f1f1; }
        .btn-back { color: #6c757d; text-decoration: none; font-weight: 600; transition: 0.3s; display: inline-flex; align-items: center; margin-bottom: 20px; }
        .btn-back:hover { color: #0d6efd; transform: translateX(-5px); }
        .badge-status { font-size: 11px; font-weight: 700; padding: 6px 12px; }
        .search-box { background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
    </style>
</head>
<body>

<div class="content-wrapper">
    <a href="dashboard.php" class="btn-back">
        <i class="fas fa-chevron-left me-2"></i> Dashboard Utama
    </a>

    <div class="row mb-2">
        <div class="col-md-7">
            <h3 class="fw-bold m-0 text-dark">Data Riwayat Transaksi</h3>
            <p class="text-muted small">Kelola dan pantau semua aliran barang keluar masuk.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <?php if ($is_searching): ?>
                <a href="riwayat.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm mb-3">
                    <i class="fas fa-sync-alt me-2"></i> Tampilkan Semua Data
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="search-box">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-1">Cari Nama Barang</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Contoh: Semen Gresik..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-1">Filter Tanggal</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                    <input type="date" name="tanggal" class="form-control border-0 bg-light" value="<?= $filter_tgl ?>">
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <div class="card glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" width="80">No</th>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Status</th>
                            <th>Jumlah</th>
                            <th class="pe-4 text-end">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($semua_riwayat->num_rows > 0):
                            while ($r = $semua_riwayat->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="ps-4 text-muted small"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= date('d M Y', strtotime($r['tanggal_transaksi'])) ?></td>
                                <td class="fw-bold text-dark"><?= $r['nama_barang'] ?></td>
                                <td>
                                    <?php if($r['status'] == 'masuk'): ?>
                                        <span class="badge rounded-pill bg-light text-primary border border-primary-subtle badge-status">
                                            <i class="fas fa-arrow-down me-1"></i> MASUK
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-light text-danger border border-danger-subtle badge-status">
                                            <i class="fas fa-arrow-up me-1"></i> KELUAR
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-bold"><?= $r['stok'] ?></span> <small class="text-muted">Unit</small></td>
                                <td class="pe-4 text-end fw-bold text-primary">
                                    Rp <?= number_format($r['total'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" style="width: 80px; opacity: 0.3;" alt="Kosong">
                                    <p class="mt-3 text-muted">Barang yang kamu cari tidak ditemukan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>