 
<?php
require_once '../includes/header.php';

// Only employers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: /karmiq/login.php");
    exit();
}

$eid = $_SESSION['user_id'];

// Stats
$total_jobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$total_jobs->execute([$eid]);
$total_jobs = $total_jobs->fetchColumn();

$active_jobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'active'");
$active_jobs->execute([$eid]);
$active_jobs = $active_jobs->fetchColumn();

$total_apps = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = ?");
$total_apps->execute([$eid]);
$total_apps = $total_apps->fetchColumn();

$pending_apps = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = ? AND a.status = 'pending'");
$pending_apps->execute([$eid]);
$pending_apps = $pending_apps->fetchColumn();

// Recent jobs
$stmt = $pdo->prepare("
    SELECT j.*, c.name as category_name,
    (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as app_count
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
    LIMIT 5
");
$stmt->execute([$eid]);
$recent_jobs = $stmt->fetchAll();
?>

<!-- Hero -->
<div class="hero-section py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-building me-2"></i>
                    Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!
                </h2>
                <p class="mb-0 opacity-75">Manage your job listings and applications</p>
            </div>
            <a href="/karmiq/employer/post_job.php" class="btn btn-warning fw-bold px-4">
                <i class="bi bi-plus-circle me-2"></i>Post a New Job
            </a>
        </div>
    </div>
</div>

<div class="container my-4">

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:55px;height:55px">
                    <i class="bi bi-briefcase-fill text-primary fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-primary"><?php echo $total_jobs; ?></div>
                <div class="text-muted small mt-1">Total Jobs Posted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-success bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:55px;height:55px">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-success"><?php echo $active_jobs; ?></div>
                <div class="text-muted small mt-1">Active Jobs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-warning bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:55px;height:55px">
                    <i class="bi bi-people-fill text-warning fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-warning"><?php echo $total_apps; ?></div>
                <div class="text-muted small mt-1">Total Applications</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-danger bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:55px;height:55px">
                    <i class="bi bi-hourglass-split text-danger fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-danger"><?php echo $pending_apps; ?></div>
                <div class="text-muted small mt-1">Pending Review</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="/karmiq/employer/post_job.php"
               class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                <i class="bi bi-plus-circle me-2"></i>Post a Job
            </a>
        </div>
        <div class="col-md-4">
            <a href="/karmiq/employer/manage_jobs.php"
               class="btn btn-outline-primary btn-lg w-100 py-3 fw-bold">
                <i class="bi bi-list-ul me-2"></i>Manage Jobs
            </a>
        </div>
        <div class="col-md-4">
            <a href="/karmiq/employer/applications.php"
               class="btn btn-outline-primary btn-lg w-100 py-3 fw-bold">
                <i class="bi bi-people me-2"></i>View Applications
            </a>
        </div>
    </div>

    <!-- Recent Jobs -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-2"></i>Recent Job Listings
            </h5>
            <a href="/karmiq/employer/manage_jobs.php"
               class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if(empty($recent_jobs)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-briefcase text-muted" style="font-size:3rem"></i>
                    <p class="text-muted mt-2">No jobs posted yet.</p>
                    <a href="/karmiq/employer/post_job.php" class="btn btn-primary">
                        Post Your First Job
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Job Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Applications</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_jobs as $job): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($job['category_name']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">
                                        <?php echo $job['app_count']; ?>
                                    </span>
                                    <span class="text-muted small"> applicants</span>
                                </td>
                                <td>
                                    <?php if($job['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/karmiq/employer/applications.php?job_id=<?php echo $job['id']; ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-people me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>