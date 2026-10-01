<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /karmiq/login.php");
    exit();
}
 
// Stats
$total_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$total_seekers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seeker'")->fetchColumn();
$total_employers= $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();
$total_jobs     = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$active_jobs    = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
$total_apps     = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Recent jobs
$recent_jobs = $pdo->query("
    SELECT j.*, u.full_name as employer_name, c.name as category_name,
    (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as app_count
    FROM jobs j
    JOIN users u ON j.employer_id = u.id
    JOIN categories c ON j.category_id = c.id
    ORDER BY j.created_at DESC
    LIMIT 5
")->fetchAll();

// Recent users
$recent_users = $pdo->query("
    SELECT * FROM users WHERE role != 'admin'
    ORDER BY created_at DESC LIMIT 5
")->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
        </h2>
        <p class="mb-0 opacity-75">Manage and monitor KarmiQ platform</p>
    </div>
</div>

<div class="container my-4">

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:55px;height:55px">
                    <i class="bi bi-people-fill text-primary fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-primary"><?php echo $total_users; ?></div>
                <div class="text-muted small mt-1">Total Users</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-success bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:55px;height:55px">
                    <i class="bi bi-person-check-fill text-success fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-success"><?php echo $total_seekers; ?></div>
                <div class="text-muted small mt-1">Job Seekers</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-warning bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:55px;height:55px">
                    <i class="bi bi-building-fill text-warning fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-warning"><?php echo $total_employers; ?></div>
                <div class="text-muted small mt-1">Employers</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-info bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:55px;height:55px">
                    <i class="bi bi-briefcase-fill text-info fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-info"><?php echo $total_jobs; ?></div>
                <div class="text-muted small mt-1">Total Jobs</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-success bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:55px;height:55px">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-success"><?php echo $active_jobs; ?></div>
                <div class="text-muted small mt-1">Active Jobs</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card text-center p-4">
                <div class="rounded-circle bg-danger bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:55px;height:55px">
                    <i class="bi bi-file-text-fill text-danger fs-4"></i>
                </div>
                <div class="display-6 fw-bold text-danger"><?php echo $total_apps; ?></div>
                <div class="text-muted small mt-1">Applications</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="/karmiq/admin/users.php" class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                <i class="bi bi-people me-2"></i>Manage Users
            </a>
        </div>
        <div class="col-md-6">
            <a href="/karmiq/admin/jobs.php" class="btn btn-outline-primary btn-lg w-100 py-3 fw-bold">
                <i class="bi bi-briefcase me-2"></i>Manage Jobs
            </a>
        </div>
    </div>

    <div class="row g-4">

        <!-- Recent Jobs -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-briefcase me-2"></i>Recent Jobs
                    </h6>
                    <a href="/karmiq/admin/jobs.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Title</th>
                                    <th>Employer</th>
                                    <th>Apps</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_jobs as $job): ?>
                                <tr>
                                    <td class="ps-4 fw-bold small">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo htmlspecialchars($job['employer_name']); ?>
                                    </td>
                                    <td class="text-primary fw-bold">
                                        <?php echo $job['app_count']; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-people me-2"></i>Recent Users
                    </h6>
                    <a href="/karmiq/admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-3">
                    <?php foreach($recent_users as $user): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:38px;height:38px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:700;flex-shrink:0;">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold small text-truncate">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </div>
                            <div class="text-muted" style="font-size:12px;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        <span class="badge bg-<?php echo $user['role'] == 'seeker' ? 'primary' : 'success'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>