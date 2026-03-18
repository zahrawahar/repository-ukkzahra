<?php
session_start();
include "koneksi.php";

$active_page = 'data_barang';
// if (!isset($_SESSION['login'])) {
//     header("Location: login.php");
//     exit;
// }
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
// DEFINISI PRIMARY KEY TABEL BARANG (Sesuai barang.sql)
$primary_key = 'id_barang';

// Proses Edit Barang
// Proses Edit Barang
if (isset($_POST['ubah_barang'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = (int) $_POST['harga'];

    // Ambil data stok saat ini dari database untuk validasi
    $cek = $conn->query("SELECT stok FROM barang WHERE id_barang = $id")->fetch_assoc();
    $stok_maksimal = (int)$cek['stok'];

    $s_baik  = max(0, (int)$_POST['stok_baik']);
    $s_rusak = max(0, (int)$_POST['stok_rusak']);

    // VALIDASI: Total baik + rusak tidak boleh melebihi stok yang ada
    if (($s_baik + $s_rusak) > $stok_maksimal) {
        echo "<script>
                alert('Gagal! Total Baik ($s_baik) + Rusak ($s_rusak) melebihi stok yang ada ($stok_maksimal).');
                window.location='tambah.php?edit=$id';
              </script>";
    } else {
        // Update rincian kondisi (stok utama tetap sesuai data awal)
        $sql = "UPDATE barang SET 
                nama = '$nama', 
                harga = '$harga', 
                stok_baik = '$s_baik', 
                stok_rusak = '$s_rusak' 
                WHERE id_barang = $id";

        if ($conn->query($sql)) {
            echo "<script>alert('Kondisi Berhasil Diperbarui!'); window.location='tambah.php';</script>";
        }
    }
    exit();
}
// Ambil data untuk form edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $res_edit = $conn->query("SELECT * FROM barang WHERE $primary_key=$id");
    if ($res_edit && $res_edit->num_rows > 0) {
        $edit_data = $res_edit->fetch_assoc();
    }
}


// Query Utama
$result = $conn->query("SELECT * FROM barang ORDER BY $primary_key DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Barang | Gudang Barang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Fungsi saat baris tabel diklik
        $('.view-detail').click(function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const stok = $(this).data('stok');
            const harga = $(this).data('harga');
            const baik = $(this).data('baik');
            const rusak = $(this).data('rusak');
            const totalHarga = stok * harga;

            // Isi data ke dalam Modal
            $('#detail-nama').text(nama);
            $('#detail-stok').text(stok);
            $('#detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(harga));
            $('#detail-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga));
            $('#detail-baik').text(baik);
            $('#detail-rusak').text(rusak);
            // Tanggal default (karena di SQL tidak ada kolom tanggal, bisa diisi '-' atau tambahkan kolom tgl_masuk)
            $('#detail-tanggal').text('Pembaruan Terakhir');

            // Tampilkan Modal
            $('#modalDetail').modal('show');
        });
    });
</script>

<body class="hold-transition sidebar-mini">
    <div class="wrapper d-flex">


        <!-- Sidebar langsung di sini -->
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

        <div class="content-wrapper w-100">
            <section class="content-header mb-3">
                <h2><i class="fas fa-boxes"></i> Manajemen Stok Barang</h2>
            </section>

            <section class="content">
                <?php if ($edit_data): ?>
                    <div class="card card-warning mb-4 shadow">
                        <div class="card-header">
                            <h3 class="card-title text-dark">
                                <?= $edit_data['nama'] ?>
                                (Stok: <span id="maxStok"><?= $edit_data['stok'] ?></span>)
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="return validasiStok()">
                                <input type="hidden" name="id" value="<?= $edit_data['id_barang'] ?>">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold">Nama Barang</label>
                                        <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold text-success">Jumlah Baik</label>
                                        <input type="number" name="stok_baik" id="input_baik" class="form-control"
                                            value="<?= $edit_data['stok_baik'] ?>" min="0" max="<?= $edit_data['stok'] ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold text-danger">Jumlah Rusak</label>
                                        <input type="number" name="stok_rusak" id="input_rusak" class="form-control"
                                            value="<?= $edit_data['stok_rusak'] ?>" min="0" max="<?= $edit_data['stok'] ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="fw-bold">Harga (Rp)</label>
                                        <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?>" required>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" name="ubah_barang" class="btn btn-primary w-100 shadow-sm">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                        function validasiStok() {
                            let max = parseInt(document.getElementById('maxStok').innerText);
                            let baik = parseInt(document.getElementById('input_baik').value) || 0;
                            let rusak = parseInt(document.getElementById('input_rusak').value) || 0;

                            if ((baik + rusak) > max) {
                                alert("Total jumlah Baik dan Rusak tidak boleh lebih dari " + max);
                                return false; // Batalkan submit form
                            }
                            return true;
                        }
                    </script>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover m-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Stok</th>
                                    <th>Harga</th>
                                    <th>Kondisi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    $no = 1;
                                    while ($d = $result->fetch_assoc()) {
                                        // Tambahkan class 'view-detail' dan data-attributes
                                        echo "<tr class='view-detail' style='cursor:pointer' 
                 
                  data-nama='{$d['nama']}' 
                  data-stok='{$d['stok']}' 
                  data-harga='{$d['harga']}' 
                  data-baik='{$d['stok_baik']}' 
                  data-rusak='{$d['stok_rusak']}'>
                  <td class = 'text-center'>{$no}</td>
            
            <td><span class='text-primary fw-bold'>{$d['nama']}</span></td>
            <td><b>{$d['stok']}</b></td>
            <td>Rp " . number_format($d['harga'], 0, ',', '.') . "</td>
            <td>
                <span class='badge bg-success'>B: {$d['stok_baik']}</span>
                <span class='badge bg-danger'>R: {$d['stok_rusak']}</span>
            </td>
            <td class='text-center'>
                <a href='?edit={$d[$primary_key]}' class='btn btn-warning btn-sm' onclick='event.stopPropagation();'><i class='fas fa-edit'></i></a>
            </td>
        </tr>";
                                        $no++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Barang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-striped m-0">
                        <tr>
                            <th class="ps-3 w-50">Nama Barang</th>
                            <td id="detail-nama"></td>
                        </tr>

                        <tr>
                            <th class="ps-3">Stok Keseluruhan</th>
                            <td id="detail-stok" class="fw-bold"></td>
                        </tr>
                        <tr>
                            <th class="ps-3">Harga Satuan</th>
                            <td id="detail-harga"></td>
                        </tr>
                        <tr class="table-primary">
                            <th class="ps-3">Total Harga</th>
                            <td id="detail-total" class="fw-bold text-primary"></td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-success">Kondisi Baik</th>
                            <td><span class="badge bg-success" id="detail-baik"></span> Unit</td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-danger">Kondisi Rusak</th>
                            <td><span class="badge bg-danger" id="detail-rusak"></span> Unit</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>