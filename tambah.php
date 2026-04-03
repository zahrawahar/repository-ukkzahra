<?php
session_start();
include "koneksi.php";

$active_page = 'data_barang';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$primary_key = 'id_barang';

// --- Bagian Logika PHP Tetap Sama (Tidak Berubah) ---
if (isset($_POST['ubah_barang'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = (int) $_POST['harga'];
    $cek = $conn->query("SELECT stok FROM barang WHERE id_barang = $id")->fetch_assoc();
    $stok_maksimal = (int)$cek['stok'];
    $s_baik  = max(0, (int)$_POST['stok_baik']);
    $s_rusak = max(0, (int)$_POST['stok_rusak']);

    if (($s_baik + $s_rusak) > $stok_maksimal) {
        echo "<script>alert('Gagal! Melebihi stok.'); window.location='tambah.php?edit=$id';</script>";
    } else {
        $sql = "UPDATE barang SET nama = '$nama', harga = '$harga', stok_baik = '$s_baik', stok_rusak = '$s_rusak' WHERE id_barang = $id";
        if ($conn->query($sql)) {
            echo "<script>alert('Berhasil!'); window.location='tambah.php';</script>";
        }
    }
    exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $res_edit = $conn->query("SELECT * FROM barang WHERE $primary_key=$id");
    if ($res_edit && $res_edit->num_rows > 0) { $edit_data = $res_edit->fetch_assoc(); }
}
$result = $conn->query("SELECT * FROM barang ORDER BY $primary_key DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang | Gudang Barang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }

        /* ASIDE TETAP (Sesuai Permintaan) */
        .main-sidebar {
            width: 260px; height: 100vh; position: fixed;
            top: 0; left: 0; background-color: #0d6efd;
            padding: 20px 15px; color: white; z-index: 1000;
        }
        .sidebar-brand { text-align: center; margin-bottom: 40px; }
        .sidebar-brand i { font-size: 50px; margin-bottom: 10px; }
        .sidebar-brand h2 { font-size: 24px; font-weight: bold; margin: 0; }
        .nav-sidebar { list-style: none; padding: 0; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { 
            display: flex; flex-direction: column; align-items: flex-start;
            padding: 12px 20px; color: rgba(255, 255, 255, 0.9);
            text-decoration: none; border-radius: 12px; transition: 0.3s;
        }
        .nav-link i { font-size: 20px; margin-bottom: 5px; }
        .nav-link.active { background-color: white !important; color: #0d6efd !important; }
        .logout-link { color: #ff4d4d !important; margin-top: 20px; }

        /* --- KUNCI LEBAR KE SAMPING --- */
        .content-wrapper {
            margin-left: 260px; /* Lebar sidebar */
            padding: 30px 50px; /* Padding atas-bawah 30px, Kanan-Kiri 50px agar luas */
            width: calc(100% - 260px); /* Memaksa konten mengisi sisa layar */
            min-height: 100vh;
        }

        .container-fluid-custom {
            width: 100%;
            max-width: 100%; /* Menghilangkan batasan lebar */
        }

        /* CARD STYLE */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            width: 100%;
        }

        /* TABLE Luas */
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }
        .table { width: 100%; margin-bottom: 0; white-space: nowrap; }
        .table thead th {
            background-color: #f1f5f9;
            padding: 20px;
            font-size: 0.85rem;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .table tbody td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Badge Kondisi */
        .badge-kondisi {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        @media (max-width: 992px) {
            .content-wrapper { margin-left: 0; width: 100%; padding: 20px; }
            .main-sidebar { display: none; }
        }
    </style>
</head>

<body>
    <div class="wrapper d-flex">

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
            <div class="container-fluid-custom">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark m-0"><i class="fas fa-layer-group text-primary me-2"></i> Inventaris Barang</h2>
                    <div class="text-muted small">Total Data: <b><?= $result->num_rows ?></b></div>
                </div>

                <?php if ($edit_data): ?>
                <div class="card mb-4 border-start border-warning border-5">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="fw-bold">Edit Detail: <?= $edit_data['nama'] ?></h5>
                            <a href="tambah.php" class="btn-close"></a>
                        </div>
                        <form method="POST" onsubmit="return validasiStok()">
                            <input type="hidden" name="id" value="<?= $edit_data['id_barang'] ?>">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="small fw-bold">Nama</label><input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?>" required></div>
                                <div class="col-md-2"><label class="small fw-bold text-success">Baik</label><input type="number" name="stok_baik" id="input_baik" class="form-control" value="<?= $edit_data['stok_baik'] ?>" required></div>
                                <div class="col-md-2"><label class="small fw-bold text-danger">Rusak</label><input type="number" name="stok_rusak" id="input_rusak" class="form-control" value="<?= $edit_data['stok_rusak'] ?>" required></div>
                                <div class="col-md-2"><label class="small fw-bold">Harga</label><input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?>" required></div>
                                <div class="col-md-2 d-flex align-items-end"><button type="submit" name="ubah_barang" class="btn btn-primary w-100 py-2">Update</button></div>
                            </div>
                            <p class="mt-2 mb-0 small text-muted">* Batas maksimal stok gabungan: <b id="maxStok"><?= $edit_data['stok'] ?></b></p>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>Nama Barang</th>
                                    <th>Stok Total</th>
                                    <th>Harga Satuan</th>
                                    <th>Rincian Kondisi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($d = $result->fetch_assoc()) : ?>
                                    <tr class="view-detail" 
                                        data-nama="<?= $d['nama'] ?>" data-stok="<?= $d['stok'] ?>" 
                                        data-harga="<?= $d['harga'] ?>" data-baik="<?= $d['stok_baik'] ?>" 
                                        data-rusak="<?= $d['stok_rusak'] ?>" style="cursor: pointer;">
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td><span class="fw-bold text-primary"><?= $d['nama'] ?></span></td>
                                        <td><span class="badge bg-primary-subtle text-primary px-3 py-2"><?= $d['stok'] ?> Unit</span></td>
                                        <td><span class="text-muted small">Rp</span> <b><?= number_format($d['harga'], 0, ',', '.') ?></b></td>
                                        <td>
                                            <span class="badge-kondisi bg-success-subtle text-success">B: <?= $d['stok_baik'] ?></span>
                                            <span class="badge-kondisi bg-danger-subtle text-danger">R: <?= $d['stok_rusak'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="?edit=<?= $d[$primary_key] ?>" class="btn btn-sm btn-outline-warning" onclick="event.stopPropagation();"><i class="fas fa-edit"></i></a>
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

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold"><i class="fas fa-info-circle me-2"></i> Detail Aset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-4 mb-3">
                        <label class="small text-muted d-block">Nama Barang</label>
                        <h4 class="fw-bold text-dark m-0" id="detail-nama"></h4>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="small text-muted">Stok Total</label>
                            <div class="fs-5 fw-bold" id="detail-stok"></div>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted">Harga Unit</label>
                            <div class="fs-5 fw-bold" id="detail-harga"></div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><div class="p-2 border rounded-3 text-center"><small class="text-success d-block">Baik</small><b id="detail-baik"></b></div></div>
                        <div class="col-6"><div class="p-2 border rounded-3 text-center"><small class="text-danger d-block">Rusak</small><b id="detail-rusak"></b></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.view-detail').click(function() {
                const d = $(this).data();
                $('#detail-nama').text(d.nama);
                $('#detail-stok').text(d.stok + ' Unit');
                $('#detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(d.harga));
                $('#detail-baik').text(d.baik);
                $('#detail-rusak').text(d.rusak);
                $('#modalDetail').modal('show');
            });
        });

        function validasiStok() {
            let max = parseInt($('#maxStok').text());
            let baik = parseInt($('#input_baik').val()) || 0;
            let rusak = parseInt($('#input_rusak').val()) || 0;
            if ((baik + rusak) > max) {
                alert("Total jumlah Baik dan Rusak tidak boleh melebihi stok yang ada (" + max + ")");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>