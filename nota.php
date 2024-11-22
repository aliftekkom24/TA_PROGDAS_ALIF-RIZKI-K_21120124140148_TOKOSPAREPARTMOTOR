<?php 
// Koneksi ke database
include 'koneksi.php';

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Mulai sesi jika diperlukan
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembelian</title>
    <link rel="stylesheet" href="admin/assets/CSS/bootstrap.css">
</head>
<body>

<?php include'menu.php';    ?>

<section class="konten">
    <div class="container">
        <h2>Detail Pembelian</h2>

        <?php 
        // Validasi input ID
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            die("ID pembelian tidak ditemukan.");
        }

        $id_pembelian = intval($_GET['id']);

        // Ambil detail pembelian
        $ambil = $koneksi->query("SELECT * FROM pembelian 
                                  JOIN pelanggan 
                                  ON pembelian.id_pelanggan = pelanggan.id_pelanggan 
                                  WHERE pembelian.id_pembelian = $id_pembelian");
        $detail = $ambil->fetch_assoc();

        // Jika data pembelian tidak ditemukan
        if (!$detail) {
            die("Data pembelian tidak ditemukan.");
        }
        ?>





        

        <div class="row">
            <div class="col-md-4">
                <h3>Pembelian</h3>
                <strong>No. Pembelian: <?php echo $detail['id_pembelian']?></strong><br>
                Tanggal : <?php echo $detail['tanggal_pembelian'];?><br>
                Total : Rp. <?php echo number_format($detail['total_pembelian'])?>
            </div>
            <div class="col-md-4">
                <h3>Pelanggan</h3>
               <strong><?php echo htmlspecialchars($detail['nama_pelanggan']); ?></strong><br>
               <p>
                    <?php echo htmlspecialchars($detail['telepon_pelanggan']); ?><br>
                    <?php echo htmlspecialchars($detail['email_pelanggan']); ?>
             </p>
            
        </div>
        <div class="col-md-4">
            <h3>Pengiriman</h3>
            <strong><?php echo $detail['nama_kota']; ?></strong><br>
            Ongkos Kirim: Rp. <?php echo number_format($detail['tarif']); ?><br>
            Alamat: <?php echo htmlspecialchars($detail['alamat_pengiriman']); ?>

        </div>
     </div>

        <p>
            Tanggal: <?php echo htmlspecialchars($detail['tanggal_pembelian']); ?><br>
            Total: Rp <?php echo number_format($detail['total_pembelian'], 0, ',', '.'); ?>
        </p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subberat</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $nomor = 1;
                $ambil = $koneksi->query("SELECT * FROM pembelian_produk 
                                          JOIN produk 
                                          ON pembelian_produk.id_produk = produk.id_produk 
                                          WHERE pembelian_produk.id_pembelian = $id_pembelian");

                while ($pecah = $ambil->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $nomor++; ?></td>
                        <td><?php echo htmlspecialchars($pecah['nama_produk']); ?></td>
                        <td>Rp <?php echo number_format($pecah['harga_produk'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($pecah['jumlah']); ?></td>
                        <td><?php echo htmlspecialchars($pecah['subberat']); ?> Gr</td>
                        <td>Rp <?php echo number_format($pecah['harga_produk'] * $pecah['jumlah'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="row">
            <div class="col-md-7">
                <div class="alert alert-info">
                    <p>
                        Silakan melakukan pembayaran sebesar <strong>Rp <?php echo number_format($detail['total_pembelian'], 0, ',', '.'); ?></strong> ke <br>
                        <strong>Bank Mandiri 012-345678-2335 AN. ALPI</strong>
                    </p>
                </div>
            </div>
        </div>

    </div>
</section>

</body>
</html>
