 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: /karmiq/login.php");
    exit();
}

$eid = $_SESSION['user_id'];

// Handle accept/reject
if (isset($_GET['action']) && isset($_GET['app_id'])) {
    $action = in_array($_GET['action'], ['accepted','rejected','pending'])
              ? $_GET['action'] : 'pending';
    $stmt = $pdo->prepare("
        UPDATE applications SET status = ?
        WHERE id = ? AND job_id IN
        (SELECT id FROM jobs WHERE employer_id = ?)
    ");
    $stmt->execute([$action, $_GET['app_id'], $eid]);
    header("Location: /karmiq/employer/applications.php?job_id=" . ($_GET['job_id'] ?? '') . "&msg=updated");
    exit();
}

// Filter by job
$job_filter = isset($_GET['job_id']) && is_numeric($_GET['job_id']) ? $_GET['job_id'] : null;

// Get employer's jobs for filter dropdown
$jobs_list = $pdo->prepare("SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$jobs_list->execute([$eid]);
$jobs_list = $jobs_list->fetchAll();

// Get applications
$where  = ["j.employer_id = ?"];
$params = [$eid];
if ($job_filter) {
    $where[]  = "a.job_id = ?";
    $params[] = $job_filter;
}
$whereStr = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT a.*, j.title as job_title, j.location as job_location,
           u.full_name as seeker_name, u.email as seeker_email,
           u.phone as seeker_phone, u.location as seeker_location
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.seeker_id = u.id
    WHERE $whereStr
    ORDER BY a.applied_at DESC
");
$stmt->execute($params);
$applications = $stmt->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-people me-2"></i>Applications Received
        </h2>
        <p class="mb-0 opacity-75">Review and manage job applications</p>
    </div>
</div>

<div class="container my-4">

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>Application status updated!
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                <label class="fw-bold mb-0">Filter by Job:</label>
                <select name="job_id" class="form-control w-auto" onchange="this.form.submit()">
                    <option value="">All Jobs</option>
                    <?php foreach($jobs_list as $j): ?>
                    <option value="<?php echo $j['id']; ?>"
                        <?php echo $job_filter == $j['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($j['title']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <span class="text-muted small">
                    <?php echo count($applications); ?> application(s) found
                </span>
            </form>
        </div>
    </div>

    <!-- Applications -->
    <?php if(empty($applications)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size:4rem"></i>
            <h4 class="mt-3 text-muted">No applications yet</h4>
            <p class="text-muted">Applications will appear here when job seekers apply</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach($applications as $app): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">

                        <!-- Applicant Info -->
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:48px;height:48px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;font-weight:700;flex-shrink:0;">
                                <?php echo strtoupper(substr($app['seeker_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold fs-6">
                                    <?php echo htmlspecialchars($app['seeker_name']); ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-envelope me-1"></i>
                                    <?php echo htmlspecialchars($app['seeker_email']); ?>
                                </div>
                            </div>
                            <?php
                            $badges = [
                                'pending'  => ['warning', 'hourglass-split', 'Pending'],
                                'accepted' => ['success', 'check-circle-fill', 'Accepted'],
                                'rejected' => ['danger',  'x-circle-fill', 'Rejected'],
                            ];
                            $b = $badges[$app['status']] ?? $badges['pending'];
                            ?>
                            <span class="badge bg-<?php echo $b[0]; ?> ms-auto px-3 py-2">
                                <i class="bi bi-<?php echo $b[1]; ?> me-1"></i>
                                <?php echo $b[2]; ?>
                            </span>
                        </div>

                        <!-- Job Info -->
                        <div class="bg-light rounded-3 p-3 mb-3">
                            <div class="fw-bold small text-primary mb-1">
                                <i class="bi bi-briefcase me-1"></i>
                                Applied for: <?php echo htmlspecialchars($app['job_title']); ?>
                            </div>
                            <?php if($app['seeker_phone']): ?>
                            <div class="text-muted small">
                                <i class="bi bi-telephone me-1"></i>
                                <?php echo htmlspecialchars($app['seeker_phone']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if($app['seeker_location']): ?>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt me-1"></i>
                                <?php echo htmlspecialchars($app['seeker_location']); ?>
                            </div>
                            <?php endif; ?>
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                            </div>
                        </div>

                        <!-- Resume -->
                        <?php if($app['resume_path']): ?>
                        <a href="/karmiq/<?php echo htmlspecialchars($app['resume_path']); ?>"
                           target="_blank"
                           class="btn btn-outline-primary btn-sm w-100 mb-3">
                            <i class="bi bi-file-earmark-pdf me-1"></i>View Resume
                        </a>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <?php if($app['status'] !== 'accepted'): ?>
                            <a href="/karmiq/employer/applications.php?action=accepted&app_id=<?php echo $app['id']; ?>&job_id=<?php echo $job_filter; ?>"
                               class="btn btn-success btn-sm flex-fill fw-bold"
                               onclick="return confirm('Accept this applicant?')">
                                <i class="bi bi-check-circle me-1"></i>Accept
                            </a>
                            <?php endif; ?>
                            <?php if($app['status'] !== 'rejected'): ?>
                            <a href="/karmiq/employer/applications.php?action=rejected&app_id=<?php echo $app['id']; ?>&job_id=<?php echo $job_filter; ?>"
                               class="btn btn-danger btn-sm flex-fill fw-bold"
                               onclick="return confirm('Reject this applicant?')">
                                <i class="bi bi-x-circle me-1"></i>Reject
                            </a>
                            <?php endif; ?>
                            <?php if($app['status'] !== 'pending'): ?>
                            <a href="/karmiq/employer/applications.php?action=pending&app_id=<?php echo $app['id']; ?>&job_id=<?php echo $job_filter; ?>"
                               class="btn btn-outline-secondary btn-sm flex-fill"
                               onclick="return confirm('Reset to pending?')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>