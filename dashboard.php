<?php
session_start();
include 'koneksi.php';

// Periksa login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$active_page = 'dashboard';

/* ================= STATISTIK ================= */
$total_stokbarang = $conn->query("SELECT COUNT(*) AS jml FROM barang")->fetch_assoc()['jml'] ?? 0;
$total_barangmasuk = $conn->query("SELECT COUNT(*) AS jml FROM transaksi WHERE status='masuk' AND DATE(tanggal_transaksi)=CURDATE()")->fetch_assoc()['jml'] ?? 0;
$total_barangkeluar = $conn->query("SELECT COUNT(*) AS jml FROM transaksi WHERE status='keluar' AND DATE(tanggal_transaksi)=CURDATE()")->fetch_assoc()['jml'] ?? 0;
$total_stokfisik = $conn->query("SELECT IFNULL(SUM(stok),0) AS total FROM barang")->fetch_assoc()['total'] ?? 0;

/* ================= RIWAYAT (SAMPING) ================= */
$riwayat = $conn->query("
    SELECT t.tanggal_transaksi, b.nama AS nama_barang, t.status, t.stok 
    FROM transaksi t 
    JOIN barang b ON t.id_barang = b.id_barang 
    ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC 
    LIMIT 10
");

/* ================= DATA GRAFIK ================= */
$labels = []; $dataMasuk = []; $dataKeluar = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($tgl));
    $dataMasuk[$tgl] = 0; $dataKeluar[$tgl] = 0;
}
$queryGrafik = $conn->query("SELECT DATE(tanggal_transaksi) AS tgl, status, COUNT(*) AS total FROM transaksi WHERE tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(tanggal_transaksi), status");
while ($row = $queryGrafik->fetch_assoc()) {
    if ($row['status'] == 'masuk') $dataMasuk[$row['tgl']] = $row['total'];
    else $dataKeluar[$row['tgl']] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | GudangBarang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7fe; font-family: 'Plus Jakarta Sans', sans-serif; color: #2d3436; }
        
        /* --- ASIDE TETAP (TIDAK BERUBAH) --- */
        .main-sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #0d6efd; padding: 20px 15px; color: white; z-index: 1000; }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand i { font-size: 50px; margin-bottom: 10px; }
        .sidebar-brand h2 { font-size: 24px; font-weight: bold; margin: 0; }
        .nav-sidebar { list-style: none; padding: 0; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { display: flex; flex-direction: column; align-items: flex-start; padding: 12px 20px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: 12px; transition: 0.3s; }
        .nav-link i { font-size: 20px; margin-bottom: 5px; }
        .nav-link p { margin: 0; font-weight: 500; }
        .nav-link.active { background-color: white !important; color: #0d6efd !important; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .logout-link { color: #ff4d4d !important; margin-top: 20px; }

        /* --- CONTENT AREA --- */
        .content-wrapper { margin-left: 260px; padding: 40px; }
        .welcome-card { background: linear-gradient(135deg, #0d6efd, #00c6ff); color: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; border: none; box-shadow: 0 10px 20px rgba(13, 110, 253, 0.1); }
        .stat-card { background: white; border-radius: 20px; padding: 20px; display: flex; align-items: center; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-right: 15px; }
        .glass-panel { background: white; border-radius: 20px; padding: 25px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        
        .bg-soft-blue { background: #eef4ff; color: #0d6efd; }
        .bg-soft-green { background: #e9fbf0; color: #198754; }
        .bg-soft-red { background: #fff1f2; color: #dc3545; }
        .bg-soft-orange { background: #fff8ed; color: #f59e0b; }
        
        .table-scroll { max-height: 350px; overflow-y: auto; }
        @media (max-width: 992px) { .main-sidebar { width: 80px; } .sidebar-brand h2, .nav-link p { display: none; } .content-wrapper { margin-left: 80px; } }
    </style>
</head>
<body>

    <aside class="main-sidebar">
            <div class="sidebar-brand">
                <h2 class="text-white text-center mb-4">
                    <i class="fas fa-store fa-2x mb-2"></i><br>
                    GudangBarang
                </h2>
            </div>

            <ul class="nav-sidebar">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php if ($active_page == 'dashboard') echo 'active'; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tambah.php" class="nav-link <?php if ($active_page == 'data_barang') echo 'active'; ?>">
                        <i class="fas fa-boxes-stacked"></i>
                        <p>Data Barang</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="transaksi_masuk.php" class="nav-link <?php if ($active_page == 'transaksi_masuk') echo 'active'; ?>">
                        <i class="fas fa-arrow-down"></i>
                        <p>Transaksi Masuk</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="transaksi_keluar.php" class="nav-link <?php if ($active_page == 'transaksi_keluar') echo 'active'; ?>">
                        <i class="fas fa-arrow-up"></i>
                        <p>Transaksi Keluar</p>
                    </a>
                </li>
                <?php if ($role == 'admin') : ?>
                    <li class="nav-item">
                        <a href="admin.php" class="nav-link">
                            <i class="fas fa-users-cog"></i>
                            <p>Admin</p>
                        </a>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a href="akun.php" class="nav-link <?php if ($active_page == 'akun') echo 'active'; ?>">
                            <i class="fas fa-user-circle"></i>
                            <p>Akun Saya</p>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link logout-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </aside>


    <div class="content-wrapper">
        <div class="welcome-card">
            <h2 class="fw-bold m-0">Halo, <?= $_SESSION['username']; ?>! 👋</h2>
            <p class="m-0 opacity-75">Sistem manajemen gudang dalam kendali penuh Anda.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-soft-blue"><i class="fas fa-box"></i></div>
                    <div><h5 class="fw-bold m-0"><?= $total_stokbarang; ?></h5><small class="text-muted">Jenis Barang</small></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-soft-green"><i class="fas fa-arrow-down"></i></div>
                    <div><h5 class="fw-bold m-0"><?= $total_barangmasuk; ?></h5><small class="text-muted">Masuk Hari Ini</small></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-soft-red"><i class="fas fa-arrow-up"></i></div>
                    <div><h5 class="fw-bold m-0"><?= $total_barangkeluar; ?></h5><small class="text-muted">Keluar Hari Ini</small></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-soft-orange"><i class="fas fa-layer-group"></i></div>
                    <div><h5 class="fw-bold m-0"><?= number_format($total_stokfisik, 0, ',', '.'); ?></h5><small class="text-muted">Total Unit</small></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-panel h-100">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-chart-line me-2 text-primary"></i>Tren Transaksi Mingguan</h5>
                    <div style="height: 350px;">
                        <canvas id="chartTransaksi"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-panel h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0"><i class="fas fa-history me-2 text-primary"></i>Riwayat</h5>
                        <a href="riwayat.php" class="btn btn-sm btn-light text-primary fw-bold" style="font-size: 11px;">LIHAT SEMUA</a>
                    </div>
                    <div class="table-scroll">
                        <table class="table table-borderless align-middle">
                            <tbody>
                                <?php while ($r = $riwayat->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-0">
                                        <div class="fw-bold text-dark small"><?= $r['nama_barang'] ?></div>
                                        <div class="text-muted" style="font-size: 10px;"><?= date('d M Y', strtotime($r['tanggal_transaksi'])) ?></div>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="badge rounded-pill <?= $r['status'] == 'masuk' ? 'bg-soft-blue text-primary' : 'bg-soft-red text-danger' ?>" style="font-size: 10px;">
                                            <?= $r['status'] == 'masuk' ? '+' : '-' ?><?= $r['stok'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chartTransaksi').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    { label: 'Masuk', data: <?= json_encode(array_values($dataMasuk)) ?>, borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', fill: true, tension: 0.4 },
                    { label: 'Keluar', data: <?= json_encode(array_values($dataKeluar)) ?>, borderColor: '#dc3545', backgroundColor: 'rgba(220, 53, 69, 0.1)', fill: true, tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>