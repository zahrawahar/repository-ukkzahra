<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// AMBIL ROLE DARI SESSION (Ini kuncinya!)
// Sesuaikan 'role' dengan nama kolom/key saat kamu set session di login.php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

include 'koneksi.php';

$active_page = 'dashboard';

/* ================= STATISTIK ================= */
$total_stokbarang = $conn->query("SELECT COUNT(*) AS jml FROM barang")->fetch_assoc()['jml'] ?? 0;

$total_barangmasuk = $conn->query("
    SELECT COUNT(*) AS jml FROM transaksi 
    WHERE status='masuk' AND DATE(tanggal_transaksi)=CURDATE()
")->fetch_assoc()['jml'] ?? 0;

$total_barangkeluar = $conn->query("
    SELECT COUNT(*) AS jml FROM transaksi 
    WHERE status='keluar' AND DATE(tanggal_transaksi)=CURDATE()
")->fetch_assoc()['jml'] ?? 0;

$total_stokfisik = $conn->query("SELECT IFNULL(SUM(stok),0) AS total FROM barang")->fetch_assoc()['total'] ?? 0;

/* ================= RIWAYAT (DENGAN LIMIT UNTUK TAMPILAN AWAL) ================= */
// Kita ambil lebih dari 5 agar fitur scroll terlihat fungsinya
$riwayat = $conn->query("
    SELECT t.tanggal_transaksi, b.nama AS nama_barang, t.status, t.stok, b.harga, (t.stok * b.harga) AS total
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC
    LIMIT 20 
");

/* ================= DATA GRAFIK ================= */
/* ================= DATA GRAFIK (MASUK & KELUAR) ================= */
$dataMasuk = [];
$dataKeluar = [];

for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($tgl));
    $dataMasuk[$tgl] = 0;
    $dataKeluar[$tgl] = 0;
}

// Ambil data Masuk & Keluar sekaligus
$queryGrafik = $conn->query("
    SELECT DATE(tanggal_transaksi) AS tanggal, status, COUNT(*) AS total 
    FROM transaksi 
    WHERE tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY DATE(tanggal_transaksi), status
");

while ($row = $queryGrafik->fetch_assoc()) {
    if ($row['status'] == 'masuk') {
        $dataMasuk[$row['tanggal']] = $row['total'];
    } else {
        $dataKeluar[$row['tanggal']] = $row['total'];
    }
}

$dataMasuk = array_values($dataMasuk);
$dataKeluar = array_values($dataKeluar);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | GudangBarang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        /* SIDEBAR CUSTOM SESUAI GAMBAR */
        .main-sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #0d6efd;
            /* Biru sesuai gambar */
            padding: 20px 15px;
            color: white;
            z-index: 1000;
        }

        .sidebar-brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .sidebar-brand i {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .sidebar-brand h2 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .nav-sidebar {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 12px;
            transition: 0.3s;
        }

        .nav-link i {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .nav-link p {
            margin: 0;
            font-weight: 500;
        }

        /* STYLE SAAT AKTIF (PUTIH SEPERTI GAMBAR) */
        .nav-link.active {
            background-color: white !important;
            color: #0d6efd !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* LOGOUT KHUSUS */
        .logout-link {
            color: #ff4d4d !important;
            /* Warna merah sesuai gambar */
            margin-top: 20px;
        }

        /* CONTENT */
        .content-wrapper {
            margin-left: 260px;
            padding: 30px;
        }

        /* WARNA STATISTIK CUSTOM */
        .bg-masuk {
            background-color: #0d6efd;
            color: white;
        }

        /* Biru */
        .bg-keluar {
            background-color: #dc3545;
            color: white;
        }

        /* Merah */
        .stat-box {
            padding: 20px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-box .icon {
            font-size: 35px;
            opacity: 0.3;
        }

        /* SCROLLABLE TABLE */
        .table-responsive-scroll {
            max-height: 350px;
            /* Tinggi sekitar 5-6 baris */
            overflow-y: auto;
        }

        .table thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        @media (max-width: 768px) {
            .main-sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar-brand h2,
            .nav-link p {
                display: none;
            }

            .content-wrapper {
                margin-left: 70px;
            }
        }
    </style>
</head>

<body>

    <aside class="main-sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-store"></i>
            <h2>GudangBarang</h2>
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
                <a href="javascript:void(0)" onclick="konfirmasiLogout()" class="nav-link logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
            </li>
        </ul>
    </aside>
    <div class="content-wrapper">
        <h3 class="fw-bold mb-4">Dashboard</h3>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-box bg-white shadow-sm text-dark">
                    <div>
                        <h3 class="fw-bold mb-0"><?= $total_stokbarang; ?></h3><small>Jenis Barang</small>
                    </div>
                    <div class="icon text-primary"><i class="fas fa-box"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box bg-masuk shadow">
                    <div>
                        <h3 class="fw-bold mb-0"><?= $total_barangmasuk; ?></h3><small>Masuk Hari Ini</small>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box bg-keluar shadow">
                    <div>
                        <h3 class="fw-bold mb-0"><?= $total_barangkeluar; ?></h3><small>Keluar Hari Ini</small>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-up"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box bg-warning text-dark shadow-sm">
                    <div>
                        <h3 class="fw-bold mb-0"><?= number_format($total_stokfisik, 0, ',', '.'); ?></h3><small>Total Stok</small>
                    </div>
                    <div class="icon"><i class="fas fa-warehouse"></i></div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Tren Transaksi Keluar (7 Hari)</div>
            <div class="card-body">
                <canvas id="chartTransaksi" height="100"></canvas>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>Riwayat Transaksi Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive-scroll">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Barang</th>
                                    <th>Status</th>
                                    <th>Jumlah</th>
                                    <th class="pe-4 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($r = $riwayat->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4"><?= date('d/m/Y', strtotime($r['tanggal_transaksi'])) ?></td>
                                        <td class="fw-bold"><?= $r['nama_barang'] ?></td>
                                        <td>
                                            <span class="badge <?= $r['status'] == 'masuk' ? 'bg-primary' : 'bg-danger' ?>">
                                                <?= strtoupper($r['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $r['stok'] ?></td>
                                        <td class="pe-4 text-end fw-bold">Rp <?= number_format($r['total'], 0, ',', '.') ?></td>
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
        const canvas = document.getElementById('chartTransaksi').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Barang Keluar',
                    data: <?= json_encode($dataGrafik) ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // 1. Inisialisasi Grafik Masuk & Keluar
        const ctx = document.getElementById('chartTransaksi').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                        label: 'Barang Masuk',
                        data: <?= json_encode($dataMasuk) ?>,
                        borderColor: '#0d6efd', // Biru
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Barang Keluar',
                        data: <?= json_encode($dataKeluar) ?>,
                        borderColor: '#dc3545', // Merah
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // 2. Fungsi SweetAlert untuk Logout
        function konfirmasiLogout() {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Sesi Anda akan berakhir dan harus login kembali.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }
    </script>
</body>

</html>