<?php
session_start();
include 'koneksi.php';

/* ================== PROTEKSI ADMIN ================== */
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';


$active_page = "admin";

/* ================== TAMBAH PETUGAS ================== */
if (isset($_POST['tambah_petugas'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = 'petugas';

    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
    header("Location: admin.php");
    exit;
}

/* ================== EDIT USER ================== */
if (isset($_POST['edit_user'])) {
    $id_user  = $_POST['id_user'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
    $u = mysqli_fetch_assoc($cek_user);

    if ($u) {
        if ($_SESSION['id_user'] == $id_user || $u['role'] == 'petugas') {
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET username='$username', password='$password_hash' WHERE id_user='$id_user'");
            } else {
                mysqli_query($conn, "UPDATE users SET username='$username' WHERE id_user='$id_user'");
            }
            header("Location: admin.php");
            exit;
        }
    }
}

/* ================== HAPUS USER ================== */
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_hapus'");
    $u = mysqli_fetch_assoc($cek_user);
    if ($u && $u['role'] == 'petugas') {
        mysqli_query($conn, "DELETE FROM users WHERE id_user='$id_hapus'");
        header("Location: admin.php");
        exit;
    }
}

// Ambil data terbaru setelah proses logika di atas
$dataUser = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, username ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Admin | GudangBarang</title>
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
                    <a href="admin.php" class="nav-link <?php if ($active_page == 'admin') echo 'active'; ?>">
                        <i class="fas fa-user-cog"></i>
                        <p>Admin</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role != 'admin') : ?>
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
    </aside>


    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col">
                    <h3 class="fw-bold text-gray-800">Manajemen Pengguna</h3>
                    <p class="text-muted">Kelola hak akses admin dan petugas gudang</p>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#tambahPetugas">
                        <i class="fas fa-user-plus me-2"></i>Tambah Petugas
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                while ($u = mysqli_fetch_assoc($dataUser)): ?>
                                    <tr>
                                        <td class="ps-4"><?= $no++; ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($u['username']); ?></td>
                                        <td>
                                            <span class="badge badge-role <?= $u['role'] == 'admin' ? 'bg-primary' : 'bg-info text-dark' ?>">
                                                <?= $u['role']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($_SESSION['id_user'] == $u['id_user'] || $u['role'] == 'petugas'): ?>
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id_user']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($u['role'] == 'petugas'): ?>
                                                <a href="admin.php?hapus=<?= $u['id_user']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus petugas ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editModal<?= $u['id_user']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form method="POST">
                                                    <input type="hidden" name="id_user" value="<?= $u['id_user']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Pengguna: <?= htmlspecialchars($u['username']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($u['username']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Password Baru</label>
                                                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit_user" class="btn btn-warning px-4">Update Data</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahPetugas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Petugas Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_petugas" class="btn btn-primary px-4">Simpan Petugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>