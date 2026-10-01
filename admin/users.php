 
<?php
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /karmiq/login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['delete']]);
    header("Location: /karmiq/admin/users.php?msg=deleted");
    exit();
}

// Filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$where  = ["role != 'admin'"];
$params = [];
if ($role_filter) {
    $where[]  = "role = ?";
    $params[] = $role_filter;
}
$whereStr = implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT * FROM users WHERE $whereStr ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="hero-section py-4">
    <div class="container">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-people me-2"></i>Manage Users
        </h2>
        <p class="mb-0 opacity-75">View and manage all registered users</p>
    </div>
</div>

<div class="container my-4">

    <?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>User deleted successfully.
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex gap-2 flex-wrap">
                <a href="/karmiq/admin/users.php"
                   class="btn btn-sm <?php echo !$role_filter ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    All Users
                </a>
                <a href="/karmiq/admin/users.php?role=seeker"
                   class="btn btn-sm <?php echo $role_filter == 'seeker' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Job Seekers
                </a>
                <a href="/karmiq/admin/users.php?role=employer"
                   class="btn btn-sm <?php echo $role_filter == 'employer' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                    Employers
                </a>
                <span class="text-muted small ms-2 align-self-center">
                    <?php echo count($users); ?> users found
                </span>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $i => $user): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?php echo $i+1; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:34px;height:34px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:700;flex-shrink:0;">
                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                    </div>
                                    <span class="fw-bold small">
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted small">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="text-muted small">
                                <?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?>
                            </td>
                            <td class="text-muted small">
                                <?php echo htmlspecialchars($user['location'] ?: 'N/A'); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] == 'seeker' ? 'primary' : 'success'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <a href="/karmiq/admin/users.php?delete=<?php echo $user['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this user? All their data will be removed.')">
                                    <i class="bi bi-trash"></i>
                                </a>
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



