 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: /karmiq/login.php");
    exit();
}

$eid = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$_GET['delete'], $eid]);
    header("Location: /karmiq/employer/manage_jobs.php?msg=deleted");
    exit();
}

// Handle toggle status
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$_GET['toggle'], $eid]);
    $job = $stmt->fetch();
    if ($job) {
        $new_status = $job['status'] == 'active' ? 'closed' : 'active';
        $stmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ? AND employer_id = ?");
        $stmt->execute([$new_status, $_GET['toggle'], $eid]);
    }
    header("Location: /karmiq/employer/manage_jobs.php?msg=updated");
    exit();
}

// Get all jobs
$stmt = $pdo->prepare("
    SELECT j.*, c.name as category_name,
    (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as app_count
    FROM jobs j
    JOIN categories c ON j.category_id = c.id
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$eid]);
$jobs = $stmt->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-list-ul me-2"></i>Manage Jobs
            </h2>
            <p class="mb-0 opacity-75">Edit, close or delete your job listings</p>
        </div>
        <a href="/karmiq/employer/post_job.php" class="btn btn-warning fw-bold px-4">
            <i class="bi bi-plus-circle me-2"></i>Post New Job
        </a>
    </div>
</div>

<div class="container my-4">

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo $_GET['msg'] == 'deleted' ? 'Job deleted successfully.' : 'Job status updated.'; ?>
        </div>
    <?php endif; ?>

    <?php if(empty($jobs)): ?>
        <div class="text-center py-5">
            <i class="bi bi-briefcase text-muted" style="font-size:4rem"></i>
            <h4 class="mt-3 text-muted">No jobs posted yet</h4>
            <a href="/karmiq/employer/post_job.php" class="btn btn-primary mt-2">
                Post Your First Job
            </a>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Job Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Salary</th>
                                <th>Deadline</th>
                                <th>Applications</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($jobs as $job): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo htmlspecialchars($job['title']); ?></div>
                                    <div class="text-muted small">
                                        Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                    </div>
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
                                <td class="fw-bold small" style="color:#059669;">
                                    <?php echo htmlspecialchars($job['salary'] ?: 'N/A'); ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo $job['deadline'] ? date('M d, Y', strtotime($job['deadline'])) : 'No deadline'; ?>
                                </td>
                                <td>
                                    <a href="/karmiq/employer/applications.php?job_id=<?php echo $job['id']; ?>"
                                       class="text-decoration-none">
                                        <span class="fw-bold text-primary"><?php echo $job['app_count']; ?></span>
                                        <span class="text-muted small"> applicants</span>
                                    </a>
                                </td>
                                <td>
                                    <?php if($job['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="/karmiq/employer/applications.php?job_id=<?php echo $job['id']; ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Applications">
                                            <i class="bi bi-people"></i>
                                        </a>
                                        <a href="/karmiq/employer/manage_jobs.php?toggle=<?php echo $job['id']; ?>"
                                           class="btn btn-sm btn-outline-warning"
                                           title="Toggle Status"
                                           onclick="return confirm('Toggle job status?')">
                                            <i class="bi bi-toggle-on"></i>
                                        </a>
                                        <a href="/karmiq/employer/manage_jobs.php?delete=<?php echo $job['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Delete Job"
                                           onclick="return confirm('Delete this job? This cannot be undone.')">
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
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>