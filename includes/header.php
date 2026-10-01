 
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KarmiQ — Find Work. Hire Talent.</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/karmiq/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-4" href="/karmiq/index.php">
            <i class="bi bi-briefcase-fill me-2"></i>KarmiQ
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="/karmiq/index.php">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Logged in links -->
                    <?php if($_SESSION['role'] == 'seeker'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/karmiq/seeker/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/karmiq/seeker/my_applications.php">My Applications</a>
                        </li>
                    <?php elseif($_SESSION['role'] == 'employer'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/karmiq/employer/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/karmiq/employer/post_job.php">Post a Job</a>
                        </li>
                    <?php elseif($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/karmiq/admin/dashboard.php">Admin Panel</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item ms-2">
                        <span class="nav-link text-warning fw-bold">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo $_SESSION['full_name']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-2" href="/karmiq/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>

                <?php else: ?>
                    <!-- Not logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="/karmiq/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-2 fw-bold" href="/karmiq/register.php">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>