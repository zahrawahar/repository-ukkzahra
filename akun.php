<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'user';
$id_user = $_SESSION['id_user'];
$active_page = "akun";

$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query);

// 1. Inisialisasi variabel status untuk SweetAlert
$update_status = "";

if (isset($_POST['update_akun'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_baru = $_POST['password_baru'];

    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET username='$username', password='$password_hash' WHERE id_user='$id_user'");
    } else {
        $update = mysqli_query($conn, "UPDATE users SET username='$username' WHERE id_user='$id_user'");
    }

    if ($update) {
        $_SESSION['username'] = $username;
        // 2. Set status sukses
        $update_status = "success";
        $query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
        $user = mysqli_fetch_assoc($query);
    } else {
        // 3. Set status error
        $update_status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akun Saya | GudangBarang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

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

        /* CSS bawaan Anda tetap sama */
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

        .nav-link.active {
            background-color: white !important;
            color: #0d6efd !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logout-link {
            color: #ff4d4d !important;
            margin-top: 20px;
        }

        .content-wrapper {
            margin-left: 260px;
            padding: 40px;
        }

        .card-profile {
            background: white;
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            height: 150px;
            background: linear-gradient(310deg, #2152ff, #21d4fd);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: #fff;
            border-radius: 50%;
            margin-top: -60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-size: 50px;
            color: #2152ff;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d2d6da;
        }

        .btn-update {
            background: linear-gradient(310deg, #2152ff, #21d4fd);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px;
            border-radius: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
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
            <li class="nav-item">
                <a href="akun.php" class="nav-link <?php if ($active_page == 'akun') echo 'active'; ?>">
                    <i class="fas fa-user-circle"></i>
                    <p>Akun Saya</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
            </li>
        </ul>
    </aside>

    <main class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card-profile">
                        <div class="profile-header text-center p-4">
                            <h4 class="text-white fw-bold mb-0">Personal Profile</h4>
                            <p class="text-white opacity-8">Kelola informasi pribadi Anda di sini</p>
                        </div>
                        <div class="card-body text-center pt-0 px-5 pb-5">
                            <div class="d-flex justify-content-center">
                                <div class="profile-avatar">
                                    <i class="fas fa-user-astronaut"></i>
                                </div>
                            </div>
                            <h3 class="mt-3 fw-bold"><?= htmlspecialchars($user['username']); ?></h3>
                            <p class="text-muted text-uppercase small fw-bold mb-5"><?= $user['role']; ?></p>

                            <form method="POST" class="text-start">
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <label class="form-label fw-bold small text-uppercase">Username Pengguna</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-at text-primary"></i></span>
                                            <input type="text" name="username" class="form-control font-weight-bold" value="<?= htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label class="form-label fw-bold small text-uppercase">Ganti Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-key text-primary"></i></span>
                                            <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid mt-3">
                                    <button type="submit" name="update_akun" class="btn btn-update">
                                        Perbarui Profil Saya <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 6. Logika Pemicu SweetAlert
        <?php if ($update_status == "success") : ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil Anda telah diperbarui.',
                confirmButtonColor: '#0d6efd',
                timer: 2500,
                showConfirmButton: false
            });
        <?php elseif ($update_status == "error") : ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat memperbarui data.',
                confirmButtonColor: '#dc3545'
            });
        <?php endif; ?>
    </script>
</body>

</html>