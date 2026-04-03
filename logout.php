<?php
session_start(); // Memulai session agar bisa dihapus
session_unset(); // Mengosongkan semua variabel session
session_destroy(); // Menghancurkan session secara permanen

// Setelah session hancur, lempar ke halaman login
header("Location: login.php");
exit;
