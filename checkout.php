<?php 
session_start();
include 'koneksi.php';

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION["pelanggan"])) {
    echo "<script>alert('Silakan login terlebih dahulu');</script>";
    echo "<script>location='login.php';</script>";
    exit;
}

// Pastikan keranjang sudah diinisialisasi
if (!isset($_SESSION["keranjang"])) {
    $_SESSION["keranjang"] = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="admin/assets/CSS/bootstrap.css">
</head>
<body>
<?php include 'menu.php'; ?>

<section class="konten">
    <div class="container">
        <h1>Keranjang Belanja</h1>
        <hr>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $nomor = 1; 
                $totalbelanja = 0;

                if (isset($_SESSION["keranjang"]) && !empty($_SESSION["keranjang"])): 
                    foreach ($_SESSION["keranjang"] as $id_produk => $jumlah): 
                        $ambil = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                        $pecah = $ambil->fetch_assoc();

                        if (!$pecah) continue; // Lewati jika produk tidak ditemukan

                        $subharga = $pecah["harga_produk"] * $jumlah;
                ?>
                <tr>
                    <td><?php echo $nomor; ?></td>
                    <td><?php echo htmlspecialchars($pecah["nama_produk"]); ?></td>
                    <td>Rp. <?php echo number_format($pecah["harga_produk"]); ?></td>
                    <td><?php echo $jumlah; ?></td>
                    <td>Rp. <?php echo number_format($subharga); ?></td>
                </tr>
                <?php 
                    $nomor++;
                    $totalbelanja += $subharga;
                    endforeach;
                else: 
                ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Keranjang kosong</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Total Belanja</th>
                    <th>Rp. <?php echo number_format($totalbelanja); ?></th>
                </tr>
            </tfoot>
        </table>

        <form method="POST">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" readonly value="<?php echo htmlspecialchars($_SESSION['pelanggan']['nama_pelanggan']); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <input type="text" readonly value="<?php echo htmlspecialchars($_SESSION['pelanggan']['telepon_pelanggan']); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <select class="form-control" name="id_ongkir" required>
                        <option value="">Pilih Ongkos Kirim</option>
                        <?php 
                        $ambil = $koneksi->query("SELECT * FROM ongkir");
                        while ($perongkir = $ambil->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $perongkir["id_ongkir"]; ?>">
                            <?php echo htmlspecialchars($perongkir['nama_kota']); ?> - Rp. <?php echo number_format($perongkir['tarif']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
            	<label>Alamat Lengkap Pengiriman</label>
            	<textarea class="form-control" name="alamat_pengiriman" placeholder="masukan alamat lengkap pengiriman(termasuk kode pos)"></textarea>
            </div>
            <button class="btn btn-primary" name="checkout">Checkout</button>
        </form>

        <?php 
        if (isset($_POST['checkout'])) {
            $id_pelanggan = $_SESSION["pelanggan"]["id_pelanggan"];
            $id_ongkir = $_POST["id_ongkir"];
            $tanggal_pembelian = date("Y-m-d");
            $alamat_pengiriman = $_POST['alamat_pengiriman'];

            $ambil = $koneksi->query("SELECT * FROM ongkir WHERE id_ongkir='$id_ongkir'");
            $arrayongkir = $ambil->fetch_assoc();
            $nama_kota = $arrayongkir['nama_kota'];
            $arrayongkir = $ambil->fetch_assoc();
            $tarif = $arrayongkir['tarif'];
            $total_pembelian = $totalbelanja + $tarif;

            // Simpan data pembelian
            $koneksi->query("INSERT INTO pembelian (id_pelanggan, id_ongkir, tanggal_pembelian, total_pembelian,nama_kota,tarif,alamat_pengiriman) 
                             VALUES ('$id_pelanggan', '$id_ongkir', '$tanggal_pembelian', '$total_pembelian','$nama_kota','$tarif','$alamat_pengiriman')");

            $id_pembelian_barusan = $koneksi->insert_id;

            foreach ($_SESSION["keranjang"] as $id_produk => $jumlah) {
                $ambil = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                $perproduk = $ambil->fetch_assoc();

                $nama = $perproduk['nama_produk'];
                $harga = $perproduk['harga_produk'];
                $berat = $perproduk['berat_produk'];
                $subberat = $perproduk['berat_produk'] * $jumlah;
                $subharga = $perproduk['harga_produk'] * $jumlah;

                $koneksi->query("INSERT INTO pembelian_produk (id_pembelian, id_produk, nama, harga, berat, subberat, subharga, jumlah) 
                                 VALUES ('$id_pembelian_barusan', '$id_produk', '$nama', '$harga', '$berat', '$subberat', '$subharga', '$jumlah')");
            }

            // Kosongkan keranjang
            unset($_SESSION["keranjang"]);

            // Redirect ke halaman nota
            echo "<script>alert('Pembelian berhasil!');</script>";
            echo "<script>location='nota.php?id=$id_pembelian_barusan';</script>";
        }
        ?>
    </div>
</section>



</body>
</html>
	
