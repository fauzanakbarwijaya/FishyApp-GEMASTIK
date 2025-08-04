<?php
    session_start();
    require_once __DIR__ . '/../../../Connection/Connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyze</title>
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
    <h1>Analyze Page</h1>
    <p>Welcome to the Analyze page!</p>

    <!-- Include the bottom navigation bar -->
    <?php require_once __DIR__ . '/../../../View/Components/bottom-nav.php'; ?>

    <script src="<?= BASE_URL ?>/View/Assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>