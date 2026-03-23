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
// Jika session id_user tidak ada, kita beri nilai default atau paksa logout
if (!isset($_SESSION['id_user'])) {
    die("Error: Session ID User tidak ditemukan. Silakan <a href='logout.php'>Logout</a> dan Login kembali.");
}

$active_page = "transaksi_masuk";

if (isset($_POST['tambah_barang_masuk'])) {
    $id_user = $_SESSION['id_user'];
    $tanggal = date('Y-m-d');
    $status_transaksi = 'MASUK'; // Sesuai ENUM di database (Huruf Kapital)

    $namas    = $_POST['nama'];
    $stoks    = $_POST['stok'];
    $hargas   = $_POST['harga'];

    $count = 0;
    foreach ($namas as $i => $val) {
        $nama   = mysqli_real_escape_string($conn, $namas[$i]);
        $stok   = (int)$stoks[$i];
        $harga  = (int)$hargas[$i];

        if ($stok <= 0 || $harga <= 0) continue;

        // CEK APAKAH BARANG SUDAH ADA
        $cek_barang = $conn->query("SELECT id_barang, stok, stok_baik FROM barang WHERE nama = '$nama' AND harga = '$harga' LIMIT 1");

        if ($cek_barang->num_rows > 0) {
            $data_lama = $cek_barang->fetch_assoc();
            $id_barang_final = $data_lama['id_barang'];
            $stok_baru = $data_lama['stok'] + $stok;
            $stok_baik_baru = $data_lama['stok_baik'] + $stok;

            $conn->query("UPDATE barang SET stok = '$stok_baru', stok_baik = '$stok_baik_baru' WHERE id_barang = '$id_barang_final'");
        } else {
            $sql_barang = "INSERT INTO barang (nama, stok, harga, stok_baik, stok_rusak) 
                           VALUES ('$nama', '$stok', '$harga', '$stok', 0)";
            if ($conn->query($sql_barang)) {
                $id_barang_final = $conn->insert_id;
            }
        }

        // CATAT KE TABEL TRANSAKSI (Gunakan variabel $id_user yang sudah divalidasi)
        if (isset($id_barang_final) && !empty($id_user)) {
            $sql_transaksi = "INSERT INTO transaksi (id_user, id_barang, tanggal_transaksi, stok, status) 
                              VALUES ('$id_user', '$id_barang_final', '$tanggal', '$stok', '$status_transaksi')";
            if ($conn->query($sql_transaksi)) {
                $count++;
            }
        }
    }

    if ($count > 0) {
        echo "<script>alert('Berhasil memproses $count data barang masuk!'); window.location='transaksi_masuk.php';</script>";
    }
}
$tanggal_transaksi = $conn->query("
    SELECT 
        t.tanggal_transaksi,
        b.nama,
        t.stok,
        b.harga,
        (t.stok * b.harga) AS total
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    WHERE t.status = 'masuk'
    ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Masuk | Gudang barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }

        h2 {
            color: #0d6efd;
        }

        /* CARD */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.15);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            font-weight: bold;
        }

        /* TABLE */
        table thead {
            background: #e7f0ff;
        }

        table thead th {
            color: #0d6efd;
            font-weight: 600;
        }

        table tbody tr {
            transition: 0.2s;
        }

        table tbody tr:hover {
            background: #f1f7ff;
            cursor: pointer;
        }

        /* BUTTON */
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #198754, #157347);
            border: none;
            box-shadow: 0 6px 15px rgba(25, 135, 84, 0.4);
        }

        .btn-light {
            border-radius: 30px;
        }

        /* INPUT */
        .form-control {
            border-radius: 10px;
            padding: 10px;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
        }

        /* ICON REMOVE */
        .btn-remove {
            color: #dc3545;
            font-size: 1.3rem;
            transition: 0.2s;
        }

        .btn-remove:hover {
            transform: scale(1.2);
        }

        /* MODAL */
        .modal-content {
            border-radius: 20px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .main-sidebar {
                margin-left: -250px;
            }

            .content-wrapper {
                margin-left: 0;
            }
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
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="fw-bold">Transaksi Barang Masuk</h2>
                    <p class="text-secondary">Input stok baru. Jika nama & harga sama, stok akan otomatis bertambah.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Daftar Item Masuk</h5>
                    <button type="button" id="addBtn" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th width="250">Harga Satuan (Rp)</th>
                                        <th width="150">Jumlah</th>
                                        <th width="50" class="text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="formBody">
                                    <tr>
                                        <td>
                                            <input type="text" name="nama[]" class="form-control" required placeholder="Nama Barang">
                                            <input type="hidden" name="status_barang[]" value="baik">
                                        </td>
                                        <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
                                        <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
                                        <td class="text-center">
                                            <i class="fas fa-times-circle btn-remove removeRow"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 border-top pt-3 text-end">
                            <button type="reset" class="btn btn-light px-4 me-2">Reset</button>
                            <button type="submit" name="tambah_barang_masuk" class="btn btn-success px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white py-3">
                            <h5 class="m-0 fw-bold text-primary">
                                <i class="fas fa-clock me-2"></i>Transaksi Terakhir
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($tanggal_transaksi->num_rows > 0): ?>
                                            <?php while ($row = $tanggal_transaksi->fetch_assoc()): ?>
                                                <tr class="transaksi-row"
                                                    data-tanggal="<?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                    data-jumlah="<?= $row['stok'] ?>"
                                                    data-harga="<?= number_format($row['harga'], 0, ',', '.') ?>"
                                                    data-total="<?= number_format($row['total'], 0, ',', '.') ?>"
                                                    style="cursor:pointer">

                                                    <td><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                                    <td><?= $row['stok'] ?></td>
                                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                                    <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Belum ada transaksi barang masuk
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal fade" id="modalNota" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow" id="notaArea" style="border-radius: 15px;">

                <!-- HEADER -->
                <div class="modal-header bg-dark text-white text-center d-block">
                    <h5 class="fw-bold mb-0">GudangBarang</h5>
                    <small class="text-light">Nota Barang Masuk</small>
                    <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body px-4">
                    <div class="text-center mb-3">
                        <small class="text-muted">Tanggal Transaksi</small>
                        <div class="fw-semibold" id="notaTanggal"></div>
                    </div>

                    <hr>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Nama Barang</span>
                        <span class="fw-semibold" id="notaNama"></span>
                    </div>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Jumlah asuk</span>
                        <span id="notaJumlah"></span>
                    </div>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Harga Satuan</span>
                        <span>Rp <span id="notaHarga"></span></span>
                    </div>

                    <hr class="border-dashed">

                    <div class="d-flex justify-content-between fw-bold fs-5 text-danger">
                        <span>TOTAL</span>
                        <span>Rp <span id="notaTotal"></span></span>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted fst-italic">
                            Terima kasih telah bertransaksi 🙏
                        </small>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer justify-content-center">
                    <button onclick="cetakNota()" class="btn btn-success w-100 rounded-pill">
                        <i class="fas fa-print me-2"></i> Cetak Nota
                    </button>
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
                var row = `<tr>
            <td>
                <input type="text" name="nama[]" class="form-control" required placeholder="Nama Barang">
                <input type="hidden" name="status_barang[]" value="baik">
            </td>
            <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
            <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
            <td class="text-center"><i class="fas fa-times-circle btn-remove removeRow"></i></td>
        </tr>`;
                $("#formBody").append(row);
            });

            // Hapus Baris
            $(document).on('click', '.removeRow', function() {
                if ($('#formBody tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert("Minimal sisa satu baris!");
                }
            });
        });

        $(document).on('click', '.transaksi-row', function() {
            $('#notaTanggal').text($(this).data('tanggal'));
            $('#notaNama').text($(this).data('nama'));
            $('#notaJumlah').text($(this).data('jumlah'));
            $('#notaHarga').text($(this).data('harga'));
            $('#notaTotal').text($(this).data('total'));

            new bootstrap.Modal(document.getElementById('modalNota')).show();
        });

        function cetakNota() {
            var isi = document.getElementById('notaArea').innerHTML;
            var win = window.open('', '', 'width=400,height=600');
            win.document.write(`
      <html>
        <head>
          <title>Cetak Nota</title>
          <style>
            body { font-family: Arial; padding: 20px; }
            table { width: 100%; }
            td { padding: 4px 0; }
          </style>
        </head>
        <body>${isi}</body>
      </html>
    `);
            win.document.close();
            win.print();
        }
    </script>
</body>

</html>