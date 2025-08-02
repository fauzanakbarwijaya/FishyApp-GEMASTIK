<?php

$rootFolder = basename(dirname(__DIR__));
define('BASE_URL', '/' . $rootFolder);

$host = "localhost";
$user = "root";
$password = "";
$database = "fishyapp";

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

?>