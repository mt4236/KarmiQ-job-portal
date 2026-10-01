 
<?php
require_once '../includes/header.php';

// Check if logged in and is seeker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header("Location: /karmiq/login.php");
    exit();
}

// Get seeker's applications count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE seeker_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE seeker_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$pending = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE seeker_id = ? AND status = 'accepted'");
$stmt->execute([$_SESSION['user_id']]);
$accepted = $stmt->fetch()['total'];

// Get recent applications
$stmt = $pdo->prepare("
    SELECT a.*, j.title, j.location, j.salary, j.status as job_status
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.seeker_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_apps = $stmt->fetchAll();
?>

<!-- Hero -->
<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold">
            <i class="bi bi-person-circle me-2"></i>
            Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!
        </h2>
        <p class="mb-0">Find your next opportunity on KarmiQ</p>
    </div>
</div>

<div class="container my-4">

    <!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-center p-4">
            <div class="rounded-circle bg-primary bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:60px;height:60px">
                <i class="bi bi-file-text-fill text-primary fs-4"></i>
            </div>
            <div class="display-6 fw-bold text-primary"><?php echo $total; ?></div>
            <div class="text-muted fw-500 mt-1">Total Applications</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center p-4">
            <div class="rounded-circle bg-warning bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:60px;height:60px">
                <i class="bi bi-hourglass-split text-warning fs-4"></i>
            </div>
            <div class="display-6 fw-bold text-warning"><?php echo $pending; ?></div>
            <div class="text-muted fw-500 mt-1">Pending</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center p-4">
            <div class="rounded-circle bg-success bg-opacity-10 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:60px;height:60px">
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
            </div>
            <div class="display-6 fw-bold text-success"><?php echo $accepted; ?></div>
            <div class="text-muted fw-500 mt-1">Accepted</div>
        </div>
    </div>
</div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="/karmiq/index.php" class="btn btn-primary btn-lg w-100 py-3">
                <i class="bi bi-search me-2"></i>Browse Jobs
            </a>
        </div>
        <div class="col-md-6">
            <a href="/karmiq/seeker/my_applications.php" class="btn btn-outline-primary btn-lg w-100 py-3">
                <i class="bi bi-file-text me-2"></i>My Applications
            </a>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-2"></i>Recent Applications
            </h5>
        </div>
        <div class="card-body px-4">
            <?php if (empty($recent_apps)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-briefcase text-muted" style="font-size:3rem"></i>
                    <p class="text-muted mt-2">No applications yet.</p>
                    <a href="/karmiq/index.php" class="btn btn-primary">
                        Browse Jobs Now
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Job Title</th>
                                <th>Location</th>
                                <th>Salary</th>
                                <th>Applied</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_apps as $app): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($app['title']); ?></td>
                                <td>
                                    <i class="bi bi-geo-alt text-muted me-1"></i>
                                    <?php echo htmlspecialchars($app['location']); ?>
                                </td>
                                <td>
                                    <i class="bi bi-cash text-muted me-1"></i>
                                    <?php echo htmlspecialchars($app['salary']); ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                </td>
                                <td>
                                    <?php
                                    $badge = [
                                        'pending'  => 'warning',
                                        'accepted' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $b = $badge[$app['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $b; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
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