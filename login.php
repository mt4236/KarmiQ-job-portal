 
<?php
require_once 'includes/header.php';

$error = '';

// If already logged in redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'seeker')   header("Location: /karmiq/seeker/dashboard.php");
    if ($_SESSION['role'] == 'employer') header("Location: /karmiq/employer/dashboard.php");
    if ($_SESSION['role'] == 'admin')    header("Location: /karmiq/admin/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['location']  = $user['location'];

            // Redirect based on role
            if ($user['role'] == 'seeker')   header("Location: /karmiq/seeker/dashboard.php");
            if ($user['role'] == 'employer') header("Location: /karmiq/employer/dashboard.php");
            if ($user['role'] == 'admin')    header("Location: /karmiq/admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="hero-section py-4">
    <div class="container text-center">
        <h2 class="fw-bold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Welcome Back!
        </h2>
        <p class="mb-0">Login to your KarmiQ account</p>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control"
                                       placeholder="your@email.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" name="password"
                                       class="form-control"
                                       placeholder="Your password" required>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>

                        <p class="text-center mt-3 mb-0">
                            Don't have an account?
                            <a href="/karmiq/register.php" class="text-primary fw-bold">
                                Register here
                            </a>
                        </p>

                    </form>

                    <!-- Quick login hint for testing -->
                    <hr>
                    <p class="text-center text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Admin login: admin@karmiq.com.np / admin123
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>