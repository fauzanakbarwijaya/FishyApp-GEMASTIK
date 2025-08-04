<?php
    session_start();
    require_once __DIR__ . '/../../../Connection/Connection.php';

    $daftar_ikan = [
        "Lele",
        "Bandeng",
        "Pari",
        "Gurame",
        "Bawal",
        "Kakap",
        "Arwana",
        "Ikan Kakatua",
        "Ikan Mas",
    ];

    $query = $_GET['query'] ?? '';
    $filtered_ikan = [];

    foreach ($daftar_ikan as $ikan) {
        if ($query === '' || stripos($ikan, $query) !== false) {
            $filtered_ikan[] = $ikan;
        }
    }

    $gallery = [];

    foreach ($filtered_ikan as $nama_ikan) {
        $slug = str_replace(' ', '_', strtolower($nama_ikan));
        $url = "https://id.wikipedia.org/api/rest_v1/page/summary/" . urlencode($slug);
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            $gambar = $data['thumbnail']['source'] ?? null;
            if ($gambar) {
                $gallery[] = [
                    'nama' => $data['title'],
                    'gambar' => $gambar
                ];
            }
        }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fishpedia</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/style.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/View/Assets/icons/logo-background.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php
    
        if (!isset($_SESSION['user_id'])) {
            echo '<script>
                Swal.fire({
                    icon: "warning",
                    title: "Not Logged In",
                    text: "Silakan login untuk mengakses fitur ini.",
                    showConfirmButton: true
                }).then(() => {
                    window.location.href = "../auth/index.php";
                });
            </script>';
            exit;
        }

    ?>
    <div class="container mt-4">
        <h1 class="mb-4">Fishpedia 🐟</h1>

        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Cari ikan..." value="<?= htmlspecialchars($query) ?>">
                <button class="btn text-light" style="background-color:#2B3788" type="submit">Cari</button>
            </div>
        </form>

        <div class="row mb-5">
            <?php foreach ($gallery as $item): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $item['gambar'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['nama']) ?>" style="height: 130px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h6 class="card-title mb-0"><?= htmlspecialchars($item['nama']) ?></h6>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Include the bottom navigation bar -->
    <?php require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>

    <script src="<?= BASE_URL ?>/View/Assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>