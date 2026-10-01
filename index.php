<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-3">
                    Find Local Jobs.<br>Hire Local Talent.
                </h1>
                <p class="fs-5 mb-4 opacity-75">
                    Nepal's simplest job platform — connecting local employers
                    with local job seekers.
                </p>
                <form method="GET" action="/karmiq/index.php">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control form-control-lg"
                                   placeholder="Job title or keyword..."
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="location" class="form-control form-control-lg"
                                   placeholder="City or location..."
                                   value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
                <i class="bi bi-briefcase-fill" style="font-size:8rem; opacity:0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="stats-section">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="stat-num">
                    <?php echo $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='active'")->fetchColumn(); ?>+
                </div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num">
                    <?php echo $pdo->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetchColumn(); ?>+
                </div>
                <div class="stat-label">Employers</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num">
                    <?php echo $pdo->query("SELECT COUNT(*) FROM users WHERE role='seeker'")->fetchColumn(); ?>+
                </div>
                <div class="stat-label">Job Seekers</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num">11+</div>
                <div class="stat-label">Job Categories</div>
            </div>
        </div>
    </div>
</div>

<!-- Category Bar -->
<div class="category-bar">
    <div class="container">
        <div class="d-flex flex-wrap gap-2">
            <a href="/karmiq/index.php"
               class="btn btn-sm <?php echo !isset($_GET['category']) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                All Jobs
            </a>
            <?php
            $cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
            foreach($cats as $cat):
                $active = (isset($_GET['category']) && $_GET['category'] == $cat['id'])
                          ? 'btn-primary' : 'btn-outline-secondary';
            ?>
            <a href="/karmiq/index.php?category=<?php echo $cat['id']; ?>"
               class="btn btn-sm <?php echo $active; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Jobs Listing -->
<div class="container my-4">

    <?php
    $where  = ["j.status = 'active'"];
    $params = [];

    if (!empty($_GET['search'])) {
        $where[]  = "(j.title LIKE ? OR j.description LIKE ?)";
        $params[] = '%' . $_GET['search'] . '%';
        $params[] = '%' . $_GET['search'] . '%';
    }
    if (!empty($_GET['location'])) {
        $where[]  = "j.location LIKE ?";
        $params[] = '%' . $_GET['location'] . '%';
    }
    if (!empty($_GET['category'])) {
        $where[]  = "j.category_id = ?";
        $params[] = $_GET['category'];
    }

    $whereStr = implode(' AND ', $where);
    $stmt = $pdo->prepare("
        SELECT j.*, u.full_name as employer_name, c.name as category_name
        FROM jobs j
        JOIN users u ON j.employer_id = u.id
        JOIN categories c ON j.category_id = c.id
        WHERE $whereStr
        ORDER BY j.created_at DESC
    ");
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();
    ?>

    <!-- Results Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <?php echo count($jobs); ?> Jobs Found
            <?php if(!empty($_GET['search'])): ?>
                for "<?php echo htmlspecialchars($_GET['search']); ?>"
            <?php endif; ?>
        </h5>
        <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="/karmiq/register.php" class="btn btn-warning fw-bold">
            <i class="bi bi-person-plus me-1"></i>Register to Apply
        </a>
        <?php endif; ?>
    </div>

    <!-- Job Cards -->
    <?php if(empty($jobs)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search text-muted" style="font-size:4rem"></i>
            <h4 class="mt-3 text-muted">No jobs found</h4>
            <p class="text-muted">Try different keywords or browse all categories</p>
            <a href="/karmiq/index.php" class="btn btn-primary">View All Jobs</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach($jobs as $job): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card job-card h-100 p-3">

                    <!-- Badges -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary">
                            <?php echo htmlspecialchars($job['category_name']); ?>
                        </span>
                        <?php if($job['deadline'] && strtotime($job['deadline']) < time()): ?>
                            <span class="badge bg-danger">Expired</span>
                        <?php else: ?>
                            <span class="badge bg-success">Active</span>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h5 class="fw-bold mb-1">
                        <?php echo htmlspecialchars($job['title']); ?>
                    </h5>

                    <!-- Employer Avatar -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:28px;height:28px;background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:6px;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:700;flex-shrink:0;">
                            <?php echo strtoupper(substr($job['employer_name'], 0, 1)); ?>
                        </div>
                        <span style="color:#64748B;font-size:13px;font-weight:500;">
                            <?php echo htmlspecialchars($job['employer_name']); ?>
                        </span>
                    </div>

                    <!-- Meta Info -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
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
                            <?php echo date('M d, Y', strtotime($job['deadline'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <?php if($job['description']): ?>
                    <p class="text-muted small mb-3" style="line-height:1.6;">
                        <?php echo htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?>
                    </p>
                    <?php endif; ?>

                    <!-- Button -->
                    <div class="mt-auto">
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'seeker'): ?>
                            <a href="/karmiq/seeker/apply.php?job_id=<?php echo $job['id']; ?>"
                               class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-send me-1"></i>Apply Now
                            </a>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                            <a href="/karmiq/login.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login to Apply
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary w-100" disabled>
                                Job Listing
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>