<?php
    require_once __DIR__ . '/../../../Connection/Connection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/style.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/View/Assets/icons/logo-background.png" type="image/x-icon">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />


</head>
<body>
    <!-- OpenStreetMap -->
    <div id="map" style="height: 100vh;"></div>


    <!-- Include the bottom navigation bar -->
    <?php require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>

    <script src="<?= BASE_URL ?>/View/Assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const map = L.map('map').setView([-6.183064, 106.8403371], 13); // Jakarta sebagai contoh

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([-6.183064, 106.8403371]).addTo(map)
        .bindPopup('UBSI KRAMAT!')
        .openPopup();
</script>

</body>
</html>