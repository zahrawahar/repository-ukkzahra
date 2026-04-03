<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$active_page = "transaksi_keluar";

// Inisialisasi variabel untuk SweetAlert
$alert = '';

if (isset($_POST['tambah_barang_keluar'])) {
    $id_user = $_SESSION['id_user'];
    $tanggal = date('Y-m-d');
    
    $namas    = $_POST['nama'];
    $stoks    = $_POST['stok'];
    $hargas   = $_POST['harga'];

    $count = 0;
    foreach ($namas as $i => $val) {
        $nama  = mysqli_real_escape_string($conn, $namas[$i]);
        $stok  = (int)$stoks[$i];
        $harga = (int)$hargas[$i];

        if ($stok <= 0) continue;

        $cek_barang = $conn->query("SELECT id_barang, stok, stok_baik FROM barang WHERE nama = '$nama' AND harga = '$harga' LIMIT 1");

        if ($cek_barang->num_rows == 0) continue;

        $data = $cek_barang->fetch_assoc();
        $id_barang_final = $data['id_barang'];
        $stok_sekarang   = $data['stok'];
        $stok_baik       = $data['stok_baik'];

        if ($stok_sekarang < $stok) continue;

        $stok_baru = $stok_sekarang - $stok;
        $stok_baik_baru = max(0, $stok_baik - $stok);

        $conn->query("UPDATE barang SET stok = '$stok_baru', stok_baik = '$stok_baik_baru' WHERE id_barang = '$id_barang_final'");

        $sql_transaksi = "INSERT INTO transaksi (id_user, id_barang, stok, tanggal_transaksi, status) VALUES ('$id_user', '$id_barang_final', '$stok', '$tanggal', 'keluar')";

        if ($conn->query($sql_transaksi)) {
            $count++;
        }
    }

    if ($count > 0) {
        $alert = "
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Berhasil memproses $count data barang keluar!',
                icon: 'success',
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                window.location='transaksi_keluar.php';
            });
        </script>";
    }
}

$tanggal_transaksi = $conn->query("
    SELECT t.tanggal_transaksi, b.nama, t.stok, b.harga, (t.stok * b.harga) AS total
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    WHERE t.status = 'keluar'
    ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Keluar | GudangBarang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8fafc;
            --accent-color: #f1f5f9;
        }

        body {
            background-color: var(--secondary-bg);
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        /* --- SIDEBAR AREA --- */
        .main-sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #0d6efd;
            padding: 20px 15px;
            color: white;
            z-index: 1000;
        }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand i { font-size: 50px; margin-bottom: 10px; }
        .sidebar-brand h2 { font-size: 24px; font-weight: bold; margin: 0; }
        .nav-sidebar { list-style: none; padding: 0; }
        .nav-item { margin-bottom: 10px; }
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
        .nav-link i { font-size: 20px; margin-bottom: 5px; }
        .nav-link p { margin: 0; font-weight: 500; }
        .nav-link.active {
            background-color: white !important;
            color: #0d6efd !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .logout-link { color: #ff4d4d !important; margin-top: 20px; }

        /* --- CONTENT AREA --- */
        .content-wrapper {
            margin-left: 260px;
            padding: 40px;
            transition: all 0.3s;
        }

        .page-header { margin-bottom: 30px; }
        .page-header h2 { font-weight: 800; color: #1e293b; letter-spacing: -0.5px; }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            background: white;
            margin-bottom: 30px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 25px;
            border-radius: 20px 20px 0 0 !important;
        }

        .table thead th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            padding: 15px 25px;
        }

        .table tbody td { padding: 18px 25px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        .form-control { border-radius: 10px; padding: 10px 15px; }

        .btn-remove {
            width: 35px; height: 35px;
            display: flex; align-items: center; justify-content: center;
            background: #fff1f2; color: #e11d48;
            border-radius: 8px; cursor: pointer; transition: 0.2s;
        }
        .btn-remove:hover { background: #e11d48; color: white; }

        .badge-keluar {
            background-color: #fff1f2; color: #e11d48;
            padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;
        }

        .nota-container { font-family: 'Courier New', Courier, monospace; }

        @media (max-width: 992px) {
            .main-sidebar { transform: translateX(-100%); }
            .content-wrapper { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

    <?= $alert ?>

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
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h2>Transaksi Barang Keluar</h2>
                    <p class="text-muted">Kelola pengeluaran stok barang inventaris Anda</p>
                </div>
                <button type="button" id="addBtn" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus me-2"></i> Tambah Item
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <form method="post" id="formTransaksi">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Informasi Barang</th>
                                        <th width="200">Harga Satuan (Rp)</th>
                                        <th width="150">Jumlah</th>
                                        <th width="80" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="formBody">
                                    <tr>
                                        <td>
                                            <input type="text" name="nama[]" class="form-control" required placeholder="Ketik nama barang...">
                                            <input type="hidden" name="status_barang[]" value="baik">
                                        </td>
                                        <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
                                        <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
                                        <td class="text-center">
                                            <div class="btn-remove removeRow mx-auto"><i class="fas fa-trash-alt"></i></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-top bg-light d-flex justify-content-end gap-2" style="border-radius: 0 0 20px 20px;">
                            <button type="reset" class="btn btn-outline-secondary px-4">Reset</button>
                            <button type="submit" name="tambah_barang_keluar" class="btn btn-success px-5">
                                <i class="fas fa-check-circle me-2"></i> Proses Pengeluaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="page-header mt-5">
                <h3 class="fw-bold"><i class="fas fa-history me-2 text-primary"></i>Riwayat Pengeluaran</h3>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Barang</th>
                                    <th>Status</th>
                                    <th>Jumlah</th>
                                    <th>Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($tanggal_transaksi->num_rows > 0): ?>
                                    <?php while ($row = $tanggal_transaksi->fetch_assoc()): ?>
                                        <tr class="transaksi-row" 
                                            style="cursor: pointer;"
                                            data-tanggal="<?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?>"
                                            data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                            data-jumlah="<?= $row['stok'] ?>"
                                            data-harga="<?= number_format($row['harga'], 0, ',', '.') ?>"
                                            data-total="<?= number_format($row['total'], 0, ',', '.') ?>">
                                            <td class="fw-medium"><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></span></td>
                                            <td><span class="badge-keluar">KELUAR</span></td>
                                            <td><?= $row['stok'] ?> Unit</td>
                                            <td class="fw-bold text-primary">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Belum ada data.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNota" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
                <div id="notaArea" class="p-4 nota-container">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold mb-0">GudangBarang</h4>
                        <p class="text-muted small">Nota Transaksi Keluar</p>
                        <hr>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Tanggal:</span> <span class="fw-bold" id="notaTanggal"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Barang:</span> <span class="fw-bold" id="notaNama"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Jumlah:</span> <span class="fw-bold" id="notaJumlah"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 small">
                        <span>Harga Satuan:</span> <span class="fw-bold">Rp <span id="notaHarga"></span></span>
                    </div>
                    <div class="bg-light p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">TOTAL</span>
                        <span class="h5 fw-bold text-primary mb-0">Rp <span id="notaTotal"></span></span>
                    </div>
                    <p class="text-center small text-muted mb-0">*** Terima Kasih ***</p>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button onclick="cetakNota()" class="btn btn-primary w-100 py-2"><i class="fas fa-print me-2"></i> Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Tambah Baris
            $("#addBtn").click(function() {
                var row = `<tr style="display:none;">
                    <td>
                        <input type="text" name="nama[]" class="form-control" required placeholder="Nama barang...">
                        <input type="hidden" name="status_barang[]" value="baik">
                    </td>
                    <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
                    <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
                    <td class="text-center"><div class="btn-remove removeRow mx-auto"><i class="fas fa-trash-alt"></i></div></td>
                </tr>`;
                var $row = $(row);
                $("#formBody").append($row);
                $row.fadeIn(300);
            });

            // Hapus Baris dengan SweetAlert2
            $(document).on('click', '.removeRow', function() {
                var $row = $(this).closest('tr');
                if ($('#formBody tr').length > 1) {
                    Swal.fire({
                        title: 'Hapus baris?',
                        text: "Data yang sedang diinput akan hilang.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $row.fadeOut(300, function() { $(this).remove(); });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Info',
                        text: 'Minimal sisa satu baris!',
                        icon: 'info'
                    });
                }
            });

            // Klik Row untuk Lihat Nota
            $(document).on('click', '.transaksi-row', function() {
                $('#notaTanggal').text($(this).data('tanggal'));
                $('#notaNama').text($(this).data('nama'));
                $('#notaJumlah').text($(this).data('jumlah') + " Unit");
                $('#notaHarga').text($(this).data('harga'));
                $('#notaTotal').text($(this).data('total'));
                new bootstrap.Modal(document.getElementById('modalNota')).show();
            });
        });

        function cetakNota() {
            var isi = document.getElementById('notaArea').innerHTML;
            var win = window.open('', '', 'width=400,height=600');
            win.document.write('<html><head><title>Nota</title><style>body{font-family:monospace;padding:20px;}.d-flex{display:flex;justify-content:space-between;}.text-center{text-align:center;}.fw-bold{font-weight:bold;}.bg-light{background:#eee;padding:10px;}</style></head><body>' + isi + '</body></html>');
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); win.close(); }, 500);
        }
    </script>
</body>
</html>