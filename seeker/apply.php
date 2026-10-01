 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header("Location: /karmiq/login.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Get job details
$stmt = $pdo->prepare("
    SELECT j.*, c.name as category_name, u.full_name as employer_name
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    JOIN users u ON j.employer_id = u.id
    WHERE j.id = ? AND j.status = 'active'
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    header("Location: /karmiq/index.php");
    exit();
}

// Check already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
$stmt->execute([$job_id, $_SESSION['user_id']]);
$already_applied = $stmt->fetch();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$already_applied) {
    $resume_path = null;

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $ext     = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Only PDF, DOC, DOCX files allowed.";
        } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
            $error = "File size must be under 5MB.";
        } else {
            $filename    = 'resume_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $upload_path = '../assets/uploads/resumes/' . $filename;
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_path)) {
                $resume_path = 'assets/uploads/resumes/' . $filename;
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("
            INSERT INTO applications (job_id, seeker_id, resume_path, status)
            VALUES (?, ?, ?, 'pending')
        ");
        $stmt->execute([$job_id, $_SESSION['user_id'], $resume_path]);
        $success = "Application submitted successfully!";
        $already_applied = true;
    }
}
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-send me-2"></i>Apply for Job
        </h2>
        <p class="mb-0 opacity-75">Submit your application below</p>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <!-- Job Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:50px;height:50px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:20px;font-weight:700;flex-shrink:0;">
                            <?php echo strtoupper(substr($job['employer_name'], 0, 1)); ?>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-1">
                                <?php echo htmlspecialchars($job['title']); ?>
                            </h4>
                            <p class="text-muted mb-2">
                                <?php echo htmlspecialchars($job['employer_name']); ?>
                            </p>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($job['category_name']); ?>
                                </span>
                                <span class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </span>
                                <?php if($job['salary']): ?>
                                <span class="fw-bold small" style="color:#059669;">
                                    <i class="bi bi-cash me-1"></i>
                                    <?php echo htmlspecialchars($job['salary']); ?>
                                </span>
                                <?php endif; ?>
                                <?php if($job['deadline']): ?>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar me-1"></i>
                                    Deadline: <?php echo date('M d, Y', strtotime($job['deadline'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($job['description']): ?>
                    <hr>
                    <p class="text-muted mb-0" style="line-height:1.7;">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Application Form -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <?php if($success): ?>
                        <div class="text-center py-4">
                            <div style="width:70px;height:70px;background:#D1FAE5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="bi bi-check-circle-fill text-success" style="font-size:2rem;"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-2">Application Submitted!</h4>
                            <p class="text-muted mb-4">
                                Your application has been sent successfully.<br>
                                The employer will review it and get back to you.
                            </p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="/karmiq/seeker/my_applications.php"
                                   class="btn btn-primary fw-bold">
                                    <i class="bi bi-list-ul me-1"></i>My Applications
                                </a>
                                <a href="/karmiq/index.php"
                                   class="btn btn-outline-primary fw-bold">
                                    <i class="bi bi-search me-1"></i>Browse More Jobs
                                </a>
                            </div>
                        </div>

                    <?php elseif($already_applied): ?>
                        <div class="text-center py-4">
                            <div style="width:70px;height:70px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="bi bi-exclamation-circle-fill text-warning" style="font-size:2rem;"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Already Applied!</h4>
                            <p class="text-muted mb-4">
                                You have already applied for this job.<br>
                                Check your application status below.
                            </p>
                            <a href="/karmiq/seeker/my_applications.php"
                               class="btn btn-primary fw-bold">
                                <i class="bi bi-list-ul me-1"></i>View My Applications
                            </a>
                        </div>

                    <?php else: ?>

                        <?php if($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-file-earmark-person me-2"></i>Your Application
                        </h5>

                        <form method="POST" enctype="multipart/form-data">

                            <!-- Auto-filled info -->
                            <div class="bg-light rounded-3 p-3 mb-4">
                                <p class="fw-bold small text-muted mb-2">
                                    APPLICATION FROM:
                                </p>
                                <p class="fw-bold mb-1">
                                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    Your profile info will be shared with the employer
                                </p>
                            </div>

                            <!-- Resume Upload -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    Upload Resume / CV
                                    <span class="text-muted fw-normal">(Optional)</span>
                                </label>
                                <input type="file" name="resume"
                                       class="form-control" accept=".pdf,.doc,.docx">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    PDF, DOC or DOCX — Max 5MB
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary fw-bold px-5">
                                    <i class="bi bi-send me-2"></i>Submit Application
                                </button>
                                <a href="/karmiq/index.php"
                                   class="btn btn-outline-secondary fw-bold">
                                    Cancel
                                </a>
                            </div>

                        </form>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>