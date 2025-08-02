<?php
require_once __DIR__ . '/../Connection/Connection.php';

class UserController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Fungsi register user baru
    public function register($username, $email, $password, $createdAt)
    {
        // Hash password sebelum disimpan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Cek apakah username atau email sudah ada
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return ['success' => false, 'message' => 'Username atau email sudah terdaftar'];
        }

        $stmt->close();

        // Insert user baru
        $stmt = $this->conn->prepare("INSERT INTO user (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registrasi berhasil'];
        } else {
            return ['success' => false, 'message' => 'Registrasi gagal'];
        }
    }

    // Fungsi login user
    public function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                return ['success' => true, 'message' => 'Login berhasil', 'user_id' => $id];
            } else {
                return ['success' => false, 'message' => 'Password salah'];
            }
        } else {
            return ['success' => false, 'message' => 'Username tidak ditemukan'];
        }
    }
}

?>