<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Admin Dashboard</h1>
            <p class="text-muted">Welcome back! Here's what's happening with Sandawatha.lk</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Last updated: <?= date('M d, Y H:i') ?></small>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon primary me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3 class="stat-number"><?= number_format($user_stats['total_users']) ?></h3>
                        <p class="stat-label">Total Users</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon success me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="stat-number"><?= number_format($user_stats['active_users']) ?></h3>
                        <p class="stat-label">Active Users</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon warning me-3">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <h3 class="stat-number"><?= number_format($user_stats['pending_users']) ?></h3>
                        <p class="stat-label">Pending Approval</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="admin-stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon info me-3">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <h3 class="stat-number"><?= number_format($premium_stats['active_premium_users']) ?></h3>
                        <p class="stat-label">Premium Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card admin-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> System Health</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="health-indicator <?= $system_health['database_status'] === 'OK' ? 'ok' : 'error' ?>">
                                <i class="bi bi-database"></i>
                                Database: <?= $system_health['database_status'] ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="health-indicator <?= $system_health['file_permissions'] === 'OK' ? 'ok' : 'error' ?>">
                                <i class="bi bi-folder"></i>
                                File Permissions: <?= $system_health['file_permissions'] ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="health-indicator ok">
                                <i class="bi bi-hdd"></i>
                                Disk Usage: <?= $system_health['disk_usage'] ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="health-indicator <?= $system_health['pending_verifications'] > 10 ? 'warning' : 'ok' ?>">
                                <i class="bi bi-person-check"></i>
                                Pending Verifications: <?= $system_health['pending_verifications'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card admin-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Revenue Overview (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Stats -->
        <div class="col-lg-4">
            <div class="card admin-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-hearts"></i> Profile Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-primary"><?= number_format($profile_stats['male_profiles']) ?></h4>
                            <small class="text-muted">Male Profiles</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-danger"><?= number_format($profile_stats['female_profiles']) ?></h4>
                            <small class="text-muted">Female Profiles</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success"><?= number_format($profile_stats['with_photos']) ?></h4>
                            <small class="text-muted">With Photos</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-info"><?= number_format($profile_stats['with_videos']) ?></h4>
                            <small class="text-muted">With Videos</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5 class="text-warning"><?= round($profile_stats['avg_age']) ?> years</h5>
                        <small class="text-muted">Average Age</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Recent Users -->
        <div class="col-lg-6">
            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Recent Users</h5>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($recent_users as $user): ?>
                        <div class="activity-item">
                            <div class="d-flex align-items-center">
                                <div class="activity-avatar bg-primary text-white d-flex align-items-center justify-content-center me-3">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($user['email']) ?></h6>
                                    <small class="activity-time">
                                        Joined <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                    </small>
                                </div>
                                <span class="status-badge <?= $user['status'] ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="col-lg-6">
            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat-square-text"></i> Recent Feedback</h5>
                    <a href="<?= BASE_URL ?>/admin/feedback" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_feedback)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat-square-text display-4 text-muted"></i>
                            <p class="text-muted mt-2">No feedback yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_feedback as $feedback): ?>
                            <div class="activity-item">
                                <div class="d-flex align-items-start">
                                    <div class="activity-avatar bg-warning text-white d-flex align-items-center justify-content-center me-3">
                                        <i class="bi bi-star"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="mb-0 me-2"><?= htmlspecialchars($feedback['name'] ?? 'Anonymous') ?></h6>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= $feedback['rating'] ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-1"><?= htmlspecialchars(substr($feedback['message'], 0, 100)) ?>...</p>
                                        <small class="activity-time">
                                            <?= date('M d, Y', strtotime($feedback['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Memberships Alert -->
    <?php if (!empty($expiring_memberships)): ?>
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="alert alert-warning alert-admin">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle alert-icon"></i>
                    <div>
                        <h6 class="mb-1">Premium Memberships Expiring Soon</h6>
                        <p class="mb-0"><?= count($expiring_memberships) ?> premium memberships are expiring within the next 7 days.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/users?status=premium" class="btn btn-warning ms-auto">View Details</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Revenue Chart
const revenueData = <?= json_encode($revenue_data) ?>;
const ctx = document.getElementById('revenueChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => item.month),
        datasets: [{
            label: 'Revenue (LKR)',
            data: revenueData.map(item => item.revenue),
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Subscriptions',
            data: revenueData.map(item => item.subscriptions),
            borderColor: 'rgb(245, 87, 108)',
            backgroundColor: 'rgba(245, 87, 108, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (LKR)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Subscriptions'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return 'Revenue: LKR ' + context.parsed.y.toLocaleString();
                        } else {
                            return 'Subscriptions: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    }
});

// Auto-refresh data every 5 minutes
setInterval(function() {
    fetch('<?= BASE_URL ?>/admin/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            // Update stats dynamically
            document.querySelectorAll('.stat-number').forEach((el, index) => {
                if (data.stats && data.stats[index]) {
                    el.textContent = data.stats[index].toLocaleString();
                }
            });
        })
        .catch(error => console.log('Stats refresh failed:', error));
}, 300000); // 5 minutes
</script>