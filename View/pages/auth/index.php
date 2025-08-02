<?php
require_once __DIR__ . '/../../../Controller/UserController.php';

$message = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $userController = new UserController($conn);
    $result = $userController->login($username, $password);
    $message = $result['message'];
    $success = $result['success'];
    // Jika login berhasil, bisa set session di sini jika diperlukan
    // $_SESSION['user_id'] = $result['user_id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User</title>
    <link rel="stylesheet" href="/FISHYAPP/View/Assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/FISHYAPP/View/Assets/css/style.css">
    <link rel="shortcut icon" href="/FISHYAPP/View/Assets/icons/logo-background.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-white">
    <?php if ($message): ?>
        <script>
            Swal.fire({
                icon: '<?= $success ? "success" : "error" ?>',
                title: '<?= $success ? "Success" : "Failed" ?>',
                text: '<?= htmlspecialchars($message, ENT_QUOTES) ?>',
                showConfirmButton: true,
                timer: <?= $success ? "2000" : "3000" ?>
            }).then(() => {
                <?php if ($success): ?>
                    window.location.href = "homepage.php";
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
    <div class="container" style="max-width: 400px;">
        <div class="text-center mb-4">
            <img src="/FISHYAPP/View/Assets/icons/logo-primary.png" alt="Logo" class="img-fluid"
                style="width: 60px;" />
        </div>

        <h4 class="text-center fw-bold">Welcome Back!</h4>
        <p class="text-center text-muted mb-4">Login To Your Account</p>

        <form method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-person-fill" style="color: #2B3788;"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="Username" name="username" required />
                </div>
            </div>

            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-lock-fill" style="color: #2B3788;"></i>
                    </span>
                    <input type="password" class="form-control border-start-0" placeholder="Password" name="password" required />
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn text-white" style="background-color: #2B3788;">Sign In</button>
            </div>
        </form>

        <div class="d-flex align-items-center my-3">
            <hr class="flex-grow-1" />
            <span class="mx-2 text-muted">Or Sign In With</span>
            <hr class="flex-grow-1" />
        </div>

        <div class="text-center mb-4">
            <a href="#" class="d-inline-block">
                <img src="/FISHYAPP/View/Assets/icons/google.png" width="36" alt="Google Sign In" />
            </a>
        </div>

        <p class="text-center text-muted">
            Don't have an account?
            <a href="/FISHYAPP/View/pages/auth/register.php" class="fw-semibold text-decoration-none" style="color: #2B3788;">Sign Up Here</a>
        </p>
    </div>



    <script src="/FISHYAPP/View/Assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
