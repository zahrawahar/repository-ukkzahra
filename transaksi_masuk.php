<?php
session_start();
include 'koneksi.php';

// PROTEKSI: Cek apakah session login ada
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// AMBIL ID USER DENGAN AMAN
if (!isset($_SESSION['id_user'])) {
    die("Error: Session ID User tidak ditemukan. Silakan <a href='logout.php'>Logout</a> dan Login kembali.");
}

$active_page = "transaksi_masuk";

// PROSES SIMPAN DATA (Logika tetap sama)
$show_success_sweetalert = false;
$count_success = 0;

if (isset($_POST['tambah_barang_masuk'])) {
    $id_user = $_SESSION['id_user'];
    $tanggal = date('Y-m-d');
    $status_transaksi = 'MASUK';

    $namas    = $_POST['nama'];
    $stoks    = $_POST['stok'];
    $hargas   = $_POST['harga'];

    foreach ($namas as $i => $val) {
        $nama   = mysqli_real_escape_string($conn, $namas[$i]);
        $stok   = (int)$stoks[$i];
        $harga  = (int)$hargas[$i];

        if ($stok <= 0 || $harga <= 0) continue;

        $cek_barang = $conn->query("SELECT id_barang, stok, stok_baik FROM barang WHERE nama = '$nama' AND harga = '$harga' LIMIT 1");

        if ($cek_barang->num_rows > 0) {
            $data_lama = $cek_barang->fetch_assoc();
            $id_barang_final = $data_lama['id_barang'];
            $stok_baru = $data_lama['stok'] + $stok;
            $stok_baik_baru = $data_lama['stok_baik'] + $stok;
            $conn->query("UPDATE barang SET stok = '$stok_baru', stok_baik = '$stok_baik_baru' WHERE id_barang = '$id_barang_final'");
        } else {
            $sql_barang = "INSERT INTO barang (nama, stok, harga, stok_baik, stok_rusak) VALUES ('$nama', '$stok', '$harga', '$stok', 0)";
            if ($conn->query($sql_barang)) { $id_barang_final = $conn->insert_id; }
        }

        if (isset($id_barang_final) && !empty($id_user)) {
            $sql_transaksi = "INSERT INTO transaksi (id_user, id_barang, tanggal_transaksi, stok, status) VALUES ('$id_user', '$id_barang_final', '$tanggal', '$stok', '$status_transaksi')";
            if ($conn->query($sql_transaksi)) { $count_success++; }
        }
    }
    if ($count_success > 0) { $show_success_sweetalert = true; }
}

$tanggal_transaksi_query = $conn->query("
    SELECT t.id_transaksi, t.tanggal_transaksi, b.nama, t.stok, b.harga, (t.stok * b.harga) AS total
    FROM transaksi t JOIN barang b ON t.id_barang = b.id_barang
    WHERE t.status = 'masuk' ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Masuk | Gudang Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d6efd, #0043a8);
            --glass-bg: rgba(255, 255, 255, 0.9);
        }

        body { 
            background-color: #f0f2f5; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        /* ASIDE TETAP SESUAI ASLI */
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

        /* CONTENT STYLING */
        .content-wrapper { 
            margin-left: 260px; 
            padding: 40px; 
            min-height: 100vh; 
            width: calc(100% - 260px);
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #edf2f7;
            padding: 1.5rem;
        }

        /* TABLE CUSTOM */
        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th {
            background-color: transparent;
            border: none;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px 20px;
        }

        .table tbody tr {
            background-color: #ffffff;
            transition: all 0.3s ease;
        }

        .table tbody tr td {
            padding: 20px;
            border: none;
            color: #2d3748;
        }

        .table tbody tr td:first-child { border-radius: 15px 0 0 15px; }
        .table tbody tr td:last-child { border-radius: 0 15px 15px 0; }

        .table tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* FORM INPUTS */
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }

        /* BUTTONS */
        .btn-add-row {
            background: #eef2ff;
            color: #0d6efd;
            border: none;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-add-row:hover { background: #0d6efd; color: white; }

        .btn-submit-custom {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            border-radius: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2);
        }

        .btn-remove {
            color: #feb2b2;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .btn-remove:hover { color: #f56565; }

        .badge-total {
            background: #ebf8ff;
            color: #3182ce;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 700;
        }

        @media (max-width: 992px) {
            .content-wrapper { margin-left: 0; width: 100%; padding: 20px; }
            .main-sidebar { display: none; }
        }
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
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">📦 Transaksi Masuk</h2>
                    <p class="text-muted m-0">Catat penerimaan stok barang ke dalam gudang</p>
                </div>
                <button type="button" id="addBtn" class="btn btn-add-row shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Baris Baru
                </button>
            </div>

            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-4">
                    <form method="post" id="formTransaksi">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Nama Barang</th>
                                        <th>Harga Satuan</th>
                                        <th style="width: 15%;">Jumlah Unit</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="formBody">
                                    <tr>
                                        <td>
                                            <input type="text" name="nama[]" class="form-control" required placeholder="Contoh: Laptop ASUS ROG">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 text-muted">Rp</span>
                                                <input type="number" name="harga[]" class="form-control border-start-0" required min="1" placeholder="0">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="stok[]" class="form-control text-center fw-bold" required min="1" placeholder="0">
                                        </td>
                                        <td class="text-center">
                                            <i class="fas fa-trash-alt btn-remove removeRow"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-4">
                            <button type="reset" class="btn btn-link text-decoration-none text-muted me-3">Bersihkan Form</button>
                            <button type="submit" name="tambah_barang_masuk" class="btn btn-primary btn-submit-custom px-5">
                                <i class="fas fa-check-double me-2"></i> Simpan Semua Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>5 Transaksi Terakhir</h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">Terbaru</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Nama Barang</th>
                                    <th>Kuantitas</th>
                                    <th>Harga Unit</th>
                                    <th class="pe-4">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($tanggal_transaksi_query->num_rows > 0): ?>
                                    <?php while ($row = $tanggal_transaksi_query->fetch_assoc()): ?>
                                        <tr class="transaksi-row" 
                                            data-tanggal="<?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?>"
                                            data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                            data-jumlah="<?= $row['stok'] ?>"
                                            data-harga="<?= number_format($row['harga'], 0, ',', '.') ?>"
                                            data-total="<?= number_format($row['total'], 0, ',', '.') ?>">
                                            <td class="ps-4"><span class="text-muted small"><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></span></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= $row['stok'] ?> Unit</span></td>
                                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                            <td class="pe-4"><span class="badge-total">Rp <?= number_format($row['total'], 0, ',', '.') ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-5">Belum ada aktivitas hari ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNota" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
                <div id="notaArea" class="bg-white p-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white d-inline-block p-3 rounded-circle mb-3">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                        <h4 class="fw-bold m-0">Bukti Barang Masuk</h4>
                        <p class="text-muted small" id="notaTanggal"></p>
                    </div>
                    
                    <div class="p-3 bg-light rounded-4 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Item</span>
                            <span class="fw-bold" id="notaNama"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Kuantitas</span>
                            <span id="notaJumlah"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Harga Satuan</span>
                            <span>Rp <span id="notaHarga"></span></span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center px-2">
                        <span class="h5 fw-bold m-0 text-dark">Total</span>
                        <span class="h4 fw-bold m-0 text-primary">Rp <span id="notaTotal"></span></span>
                    </div>

                    <div class="text-center mt-5">
                        <div class="border-top pt-3">
                            <small class="text-muted">Sistem Inventaris Gudang v2.0</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-4 flex-fill py-2" data-bs-dismiss="modal">Tutup</button>
                    <button onclick="cetakNota()" class="btn btn-primary rounded-4 flex-fill py-2 shadow-sm"><i class="fas fa-print me-2"></i>Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            <?php if ($show_success_sweetalert): ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Data inventaris telah diperbarui.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>

            $("#addBtn").click(function() {
                var row = `<tr>
                    <td><input type="text" name="nama[]" class="form-control" required placeholder="Nama Barang"></td>
                    <td><div class="input-group"><span class="input-group-text bg-transparent border-end-0 text-muted">Rp</span><input type="number" name="harga[]" class="form-control border-start-0" required min="1"></div></td>
                    <td><input type="number" name="stok[]" class="form-control text-center fw-bold" required min="1"></td>
                    <td class="text-center"><i class="fas fa-trash-alt btn-remove removeRow"></i></td>
                </tr>`;
                $("#formBody").append($(row).hide().fadeIn(400));
            });

            $(document).on('click', '.removeRow', function() {
                const row = $(this).closest('tr');
                if ($('#formBody tr').length > 1) {
                    row.fadeOut(300, function() { $(this).remove(); });
                }
            });

            $(document).on('click', '.transaksi-row', function() {
                $('#notaTanggal').text($(this).data('tanggal'));
                $('#notaNama').text($(this).data('nama'));
                $('#notaJumlah').text($(this).data('jumlah') + ' Unit');
                $('#notaHarga').text($(this).data('harga'));
                $('#notaTotal').text($(this).data('total'));
                new bootstrap.Modal(document.getElementById('modalNota')).show();
            });
        });

        function cetakNota() {
            var isi = document.getElementById('notaArea').innerHTML;
            var win = window.open('', '', 'width=600,height=700');
            win.document.write('<html><head><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body onload="window.print(); window.close();"><div class="container p-5">' + isi + '</div></body></html>');
            win.document.close();
        }
    </script>
</body>
</html>