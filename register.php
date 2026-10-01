<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $confirm   = trim($_POST['confirm_password']);
    $phone     = trim($_POST['phone']);
    $role      = $_POST['role'];
    $location  = trim($_POST['location']);

    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email already registered. Please login.";
        } else {
            // Insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users 
                (full_name, email, password, phone, role, location) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $hashed, $phone, $role, $location]);

            $success = "Registration successful! You can now login.";
        }
    }
}
?>

<div class="hero-section py-4">
    <div class="container text-center">
        <h2 class="fw-bold">
            <i class="bi bi-person-plus me-2"></i>Create Your Account
        </h2>
        <p class="mb-0">Join KarmiQ — Find Work or Hire Talent</p>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                            <a href="login.php" class="fw-bold">Login here</a>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">I want to: <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" 
                                           id="seeker" value="seeker" required
                                           <?php echo (isset($_POST['role']) && $_POST['role']=='seeker') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 py-3" for="seeker">
                                        <i class="bi bi-search d-block fs-3 mb-1"></i>
                                        <strong>Find a Job</strong><br>
                                        <small class="text-muted">I am a Job Seeker</small>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" 
                                           id="employer" value="employer" required
                                           <?php echo (isset($_POST['role']) && $_POST['role']=='employer') ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-success w-100 py-3" for="employer">
                                        <i class="bi bi-building d-block fs-3 mb-1"></i>
                                        <strong>Hire Someone</strong><br>
                                        <small class="text-muted">I am an Employer</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" name="full_name" class="form-control"
                                       placeholder="Your full name"
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                       required>
                            </div>
                        </div>

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

                        <!-- Phone -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="text" name="phone" class="form-control"
                                       placeholder="98XXXXXXXX"
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your City / Location</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-geo-alt"></i>
                                </span>
                                <input type="text" name="location" class="form-control"
                                       placeholder="e.g. Pokhara, Kathmandu, Butwal"
                                       value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" name="password" 
                                       class="form-control" placeholder="Min 6 characters" required>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" name="confirm_password" 
                                       class="form-control" placeholder="Repeat password" required>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                <i class="bi bi-person-check me-2"></i>Create Account
                            </button>
                        </div>

                        <p class="text-center mt-3 mb-0">
                            Already have an account? 
                            <a href="login.php" class="text-primary fw-bold">Login here</a>
                        </p>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>