<?php
    // Assuming BASE_URL is defined in your Connection.php
    // For example: define('BASE_URL', '/your-project-folder');
    require_once __DIR__ . '/../../../Connection/Connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Your Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/Assets/css/style.css">
    
    <!-- Google Fonts: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="shortcut icon" href="<?= BASE_URL ?>/View/Assets/icons/logo-background.png" type="image/x-icon">
</head>
<body>
    
    <!-- Main content container -->
    <div class="container main-content">
        <div class="profile-avatar">
            <!-- ICON TEMPLATE: Change the src to your profile icon path -->
            <img src="<?= BASE_URL ?>/View/Assets/icons/profile.png" alt="Profile Icon">
        </div>

        <!-- Form starts here -->
        <form class="form-container">
            <!-- Bootstrap Grid: Row for the two columns -->
            <div class="row g-3">

                <!-- Left Column -->
                <div class="col-6">
                    <!-- Username Input Group -->
                    <div class="input-group mb-3">
                        <span class="input-group-text">
                            <!-- ICON TEMPLATE: Change the src to your user icon path -->
                            <img src="<?= BASE_URL ?>/View/Assets/icons/user.png" alt="User Icon" class="input-icon">
                        </span>
                        <input type="text" class="form-control" placeholder="Username" value="Jhoe">
                    </div>

                    <!-- Email Input Group -->
                    <div class="input-group">
                        <span class="input-group-text">
                            <!-- ICON TEMPLATE: Change the src to your email icon path -->
                            <img src="<?= BASE_URL ?>/View/Assets/icons/email.png" alt="Email Icon" class="input-icon">
                        </span>
                        <input type="email" class="form-control" placeholder="Email">
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-6">
                    <!-- Old Password Input Group -->
                    <div class="input-group mb-3">
                        <span class="input-group-text">
                            <!-- ICON TEMPLATE: Change the src to your password icon path -->
                            <img src="<?= BASE_URL ?>/View/Assets/icons/password.png" alt="Password Icon" class="input-icon">
                        </span>
                        <input type="password" class="form-control" placeholder="Old Password">
                    </div>

                    <!-- New Password Input Group -->
                    <div class="input-group">
                        <span class="input-group-text">
                             <!-- ICON TEMPLATE: Change the src to your password icon path -->
                            <img src="<?= BASE_URL ?>/View/Assets/icons/password.png" alt="Password Icon" class="input-icon">
                        </span>
                        <input type="password" class="form-control" placeholder="New Password">
                    </div>
                </div>

            </div> <!-- End of Bootstrap Row -->

            <!-- Save Changes Button -->
            <button type="submit" class="btn btn-save-changes w-100 mt-4">Save Changes</button>
        </form>
    </div>

    <!-- Logout Button Container -->
    <div class="logout-container">
        <button type="submit" class="btn btn-logout">Logout</button>
    </div>
    
    <!-- Include the bottom navigation bar -->
    <?php 
        // To prevent errors
        if (file_exists(__DIR__ . '/../../../View/Components/bottom-nav.php')) {
            require_once __DIR__ . '/../../../View/Components/bottom-nav.php';
        }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


