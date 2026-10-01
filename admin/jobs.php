 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /karmiq/login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: /karmiq/admin/jobs.php?msg=deleted");
    exit();
}

// Handle toggle
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    $job = $stmt->fetch();
    if ($job) {
        $new = $job['status'] == 'active' ? 'closed' : 'active';
        $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ?")->execute([$new, $_GET['toggle']]);
    }
    header("Location: /karmiq/admin/jobs.php?msg=updated");
    exit();
}

$jobs = $pdo->query("
    SELECT j.*, u.full_name as employer_name, c.name as category_name,
    (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as app_count
    FROM jobs j
    JOIN users u ON j.employer_id = u.id
    JOIN categories c ON j.category_id = c.id
    ORDER BY j.created_at DESC
")->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-briefcase me-2"></i>Manage Jobs
        </h2>
        <p class="mb-0 opacity-75">Monitor and moderate all job listings</p>
    </div>
</div>

<div class="container my-4">

    <?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_GET['msg'] == 'deleted' ? 'Job deleted.' : 'Job status updated.'; ?>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">All Job Listings (<?php echo count($jobs); ?>)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Job Title</th>
                            <th>Employer</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jobs as $job): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold small">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </div>
                                <?php if($job['salary']): ?>
                                <div class="small" style="color:#059669;">
                                    <?php echo htmlspecialchars($job['salary']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?php echo htmlspecialchars($job['employer_name']); ?>
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
                            <td class="text-primary fw-bold">
                                <?php echo $job['app_count']; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($job['status']); ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="/karmiq/admin/jobs.php?toggle=<?php echo $job['id']; ?>"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Toggle Status"
                                       onclick="return confirm('Toggle this job status?')">
                                        <i class="bi bi-toggle-on"></i>
                                    </a>
                                    <a href="/karmiq/admin/jobs.php?delete=<?php echo $job['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       title="Delete Job"
                                       onclick="return confirm('Delete this job permanently?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>