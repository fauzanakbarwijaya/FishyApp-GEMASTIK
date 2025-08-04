<?php
    session_start();
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>
<body>
    <!-- OpenStreetMap -->
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
    <div id="map" style="height: 100vh;"></div>


    <!-- Include the bottom navigation bar -->
    <?php require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>

    <script src="<?= BASE_URL ?>/View/Assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // --- Initialize the Map ---
    // Default to Jakarta
    const defaultLat = -6.183064;
    const defaultLon = 106.8403371;
    const map = L.map('map').setView([defaultLat, defaultLon], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // --- Geolocation ---
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                map.setView([lat, lon], 15);
                L.marker([lat, lon]).addTo(map)
                    .bindPopup('You are here!')
                    .openPopup();
                L.circle([lat, lon], {
                    color: 'blue',
                    fillColor: '#3085d6',
                    fillOpacity: 0.2,
                    radius: position.coords.accuracy
                }).addTo(map);
            },
            function(error) {
                // Fallback marker if location not allowed
                L.marker([defaultLat, defaultLon]).addTo(map)
                    .bindPopup('Default Location (Jakarta)')
                    .openPopup();
            }
        );
    } else {
        // Geolocation not supported
        L.marker([defaultLat, defaultLon]).addTo(map)
            .bindPopup('Default Location (Jakarta)')
            .openPopup();
    }
    </script>

</body>
</html>