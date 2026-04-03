<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "fzone_time";

/** @var mysqli $conn */
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
