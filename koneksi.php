<?php
class Database {
    private $dbname = "websitetokoonline"; // Nama database Anda
    private $host = "localhost"; // Host database Anda
    private $user = "root";      // Username database Anda
    private $pass = "";          // Password database Anda
    public $koneksi; // Properti untuk koneksi database

    public function __construct() {
        // Membuat koneksi menggunakan mysqli
        $this->koneksi = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        // Periksa koneksi
        if ($this->koneksi->connect_error) {
            die("Koneksi gagal: " . $this->koneksi->connect_error);
        }
    }
}

// Inisialisasi objek dan ekspor ke $koneksi
$database = new Database();
$koneksi = $database->koneksi; // Variabel global
?>
