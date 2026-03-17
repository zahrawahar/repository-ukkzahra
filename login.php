<?php
session_start();
include 'koneksi.php';

$login_status = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $db_password = $row['password'];
        $is_valid = false;

        if (password_verify($password, $db_password)) {
            $is_valid = true;
        } else if (strlen($db_password) === 32 && md5($password) === $db_password) {
            $is_valid = true;
        }

        if ($is_valid) {
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = strtolower($row['role']);
            $_SESSION['login_success'] = true;

            $login_status = "success";
        } else {
            $login_status = "wrong_password";
        }
    } else {
        $login_status = "user_not_found";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gudang Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-blue: #2669f8;
            --secondary-blue: #6fb1fc;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            overflow: hidden;
        }

        /* Dekorasi Lingkaran Latar Belakang */
        .bg-circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -50px;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -20px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
            box-shadow: 0 8px 15px rgba(38, 105, 248, 0.3);
        }

        .login-title {
            font-weight: 800;
            color: #333;
            letter-spacing: -0.5px;
        }

        .form-label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: var(--primary-blue);
        }

        .form-control {
            border-left: none;
            padding: 12px;
            border-radius: 8px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(38, 105, 248, 0.15);
            border-radius: 8px;
        }

        .btn-login {
            background: linear-gradient(to right, var(--primary-blue), #4a84ff);
            border: none;
            padding: 12px;
            color: #fff;
            border-radius: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: linear-gradient(to right, #1d56d1, var(--primary-blue));
            box-shadow: 0 5px 15px rgba(38, 105, 248, 0.4);
            transform: scale(1.02);
            color: white;
        }

        .footer-text {
            font-size: 0.85rem;
            margin-top: 25px;
        }
    </style>
</head>

<body>
    <div class="bg-circles">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
    </div>

    <div class="login-card">
        <div class="brand-logo">
            <i class="fas fa-boxes-stacked"></i>
        </div>

        <h3 class="text-center login-title mb-1">Gudang Barang</h3>
        <p class="text-center text-muted mb-4">Silakan login ke akun Anda</p>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" name="login" class="btn btn-login">
                    Masuk <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </div>
        </form>

        <div class="text-center footer-text text-muted">
            &copy; <?= date('Y'); ?> Inventaris Sistem
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($login_status == "success") : ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Masuk!',
                text: 'Selamat datang kembali, <?= $_SESSION['username']; ?>',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        <?php elseif ($login_status == "wrong_password") : ?>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: 'Password yang Anda masukkan salah!',
                confirmButtonColor: '#2669f8'
            });
        <?php elseif ($login_status == "user_not_found") : ?>
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ditemukan',
                text: 'Username tidak terdaftar di sistem!',
                confirmButtonColor: '#2669f8'
            });
        <?php endif; ?>
    </script>
</body>

</html>