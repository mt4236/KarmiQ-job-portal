 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header("Location: /karmiq/login.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, j.title, j.location, j.salary, j.deadline,
           u.full_name as employer_name, c.name as category_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON j.employer_id = u.id
    JOIN categories c ON j.category_id = c.id
    WHERE a.seeker_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-file-text me-2"></i>My Applications
        </h2>
        <p class="mb-0 opacity-75">Track all your job applications</p>
    </div>
</div>

<div class="container my-4">

    <?php if(empty($applications)): ?>
        <div class="text-center py-5">
            <i class="bi bi-file-text text-muted" style="font-size:4rem"></i>
            <h4 class="mt-3 text-muted">No applications yet</h4>
            <p class="text-muted">Start applying for jobs to see them here</p>
            <a href="/karmiq/index.php" class="btn btn-primary mt-2">
                <i class="bi bi-search me-2"></i>Browse Jobs
            </a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach($applications as $app): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">

                        <!-- Job Title + Status -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <?php echo htmlspecialchars($app['title']); ?>
                                </h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:24px;height:24px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:5px;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;">
                                        <?php echo strtoupper(substr($app['employer_name'], 0, 1)); ?>
                                    </div>
                                    <span class="text-muted small">
                                        <?php echo htmlspecialchars($app['employer_name']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php
                            $badges = [
                                'pending'  => ['FEF3C7', '92400E', 'hourglass-split',    'Pending'],
                                'accepted' => ['D1FAE5', '065F46', 'check-circle-fill',  'Accepted'],
                                'rejected' => ['FEE2E2', '991B1B', 'x-circle-fill',      'Rejected'],
                            ];
                            $b = $badges[$app['status']] ?? $badges['pending'];
                            ?>
                            <span class="badge px-3 py-2"
                                  style="background:#<?php echo $b[0]; ?>;color:#<?php echo $b[1]; ?>;">
                                <i class="bi bi-<?php echo $b[2]; ?> me-1"></i>
                                <?php echo $b[3]; ?>
                            </span>
                        </div>

                        <!-- Details -->
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <span class="badge bg-primary">
                                <?php echo htmlspecialchars($app['category_name']); ?>
                            </span>
                            <span class="text-muted small">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?php echo htmlspecialchars($app['location']); ?>
                            </span>
                            <?php if($app['salary']): ?>
                            <span class="fw-bold small" style="color:#059669;">
                                <i class="bi bi-cash me-1"></i>
                                <?php echo htmlspecialchars($app['salary']); ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Applied date -->
                        <div class="text-muted small">
                            <i class="bi bi-clock me-1"></i>
                            Applied on: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                        </div>

                        <?php if($app['resume_path']): ?>
                        <a href="/karmiq/<?php echo htmlspecialchars($app['resume_path']); ?>"
                           target="_blank"
                           class="btn btn-outline-primary btn-sm mt-3">
                            <i class="bi bi-file-earmark-pdf me-1"></i>View Resume
                        </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?> 