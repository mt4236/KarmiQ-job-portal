 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: /karmiq/login.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title       = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $salary      = trim($_POST['salary']);
    $location    = trim($_POST['location']);
    $deadline    = $_POST['deadline'];

    if (empty($title) || empty($category_id) || empty($location)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO jobs
            (employer_id, title, category_id, description, salary, location, deadline, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $title, $category_id, $description,
            $salary, $location,
            $deadline ?: null
        ]);
        $success = "Job posted successfully!";
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Post a New Job
        </h2>
        <p class="mb-0 opacity-75">Fill in the details below to post your job listing</p>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                            <a href="/karmiq/employer/manage_jobs.php" class="fw-bold">View your jobs</a>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <!-- Job Title -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Job Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="e.g. Hotel Waiter, Shop Helper, Driver..."
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                   required>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Location <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-geo-alt"></i>
                                </span>
                                <input type="text" name="location" class="form-control"
                                       placeholder="e.g. Lakeside, Pokhara"
                                       value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Salary -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Salary</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-cash"></i>
                                </span>
                                <input type="text" name="salary" class="form-control"
                                       placeholder="e.g. Rs. 15,000/month"
                                       value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Application Deadline</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                </span>
                                <input type="date" name="deadline" class="form-control"
                                       value="<?php echo isset($_POST['deadline']) ? $_POST['deadline'] : ''; ?>">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Job Description</label>
                            <textarea name="description" class="form-control" rows="5"
                                      placeholder="Describe the job role, requirements, working hours, benefits..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary fw-bold px-5">
                                <i class="bi bi-send me-2"></i>Post Job
                            </button>
                            <a href="/karmiq/employer/dashboard.php"
                               class="btn btn-outline-secondary fw-bold px-4">
                                Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>