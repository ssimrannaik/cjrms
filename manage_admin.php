<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole('Admin');

$pageTitle = 'Admin Panel - CJRMS';

// Get system statistics
$stats = [
    'users' => fetchRow("SELECT COUNT(*) as count FROM Users")['count'],
    'complaints' => fetchRow("SELECT COUNT(*) as count FROM Complaint")['count'],
    'cases' => fetchRow("SELECT COUNT(*) as count FROM Cases")['count'],
    'criminals' => fetchRow("SELECT COUNT(*) as count FROM Criminal")['count'],
    'courts' => fetchRow("SELECT COUNT(*) as count FROM Court")['count'],
    'lawyers' => fetchRow("SELECT COUNT(*) as count FROM Lawyer")['count']
];

// Get recent activity
$recent_users = fetchAll("SELECT user_id, username, role, full_name, created_at FROM Users ORDER BY created_at DESC LIMIT 5");
$recent_complaints = fetchAll("SELECT complaint_id, crime_type, date, complaint_status FROM Complaint ORDER BY created_at DESC LIMIT 5");

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Admin Panel</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / Admin Panel
    </div>
</div>

<!-- System Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <h3 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['users']; ?></h3>
            <p style="color: #666; margin: 0;">System Users</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <h3 style="color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['complaints']; ?></h3>
            <p style="color: #666; margin: 0;">Total Complaints</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <h3 style="color: #ffc107; margin-bottom: 0.5rem;"><?php echo $stats['cases']; ?></h3>
            <p style="color: #666; margin: 0;">Total Cases</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <h3 style="color: #dc3545; margin-bottom: 0.5rem;"><?php echo $stats['criminals']; ?></h3>
            <p style="color: #666; margin: 0;">Criminals</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <h3 style="color: #6c757d; margin-bottom: 0.5rem;"><?php echo $stats['courts']; ?></h3>
            <p style="color: #666; margin: 0;">Courts</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <h3 style="color: #17a2b8; margin-bottom: 0.5rem;"><?php echo $stats['lawyers']; ?></h3>
            <p style="color: #666; margin: 0;">Lawyers</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <h4>User Management</h4>
                <p style="color: #666; font-size: 0.9rem;">Manage system users and their roles</p>
                <button class="btn btn-primary" onclick="alert('User management feature would be implemented here')">Manage Users</button>
            </div>
            <div>
                <h4>Court Management</h4>
                <p style="color: #666; font-size: 0.9rem;">Add and manage court information</p>
                <button class="btn btn-primary" onclick="alert('Court management feature would be implemented here')">Manage Courts</button>
            </div>
            <div>
                <h4>Lawyer Management</h4>
                <p style="color: #666; font-size: 0.9rem;">Add and manage lawyer records</p>
                <button class="btn btn-primary" onclick="alert('Lawyer management feature would be implemented here')">Manage Lawyers</button>
            </div>
            <div>
                <h4>System Reports</h4>
                <p style="color: #666; font-size: 0.9rem;">Generate system-wide reports</p>
                <button class="btn btn-secondary" onclick="alert('System reports feature would be implemented here')">Generate Reports</button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Recent Users</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_users as $user): ?>
                <tr>
                    <td><?php echo sanitizeOutput($user['username']); ?></td>
                    <td><?php echo sanitizeOutput($user['full_name']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($user['role']); ?>">
                            <?php echo $user['role']; ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Complaints -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Recent Complaints</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Crime Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_complaints as $complaint): ?>
                <tr>
                    <td><?php echo $complaint['complaint_id']; ?></td>
                    <td><?php echo sanitizeOutput($complaint['crime_type']); ?></td>
                    <td><?php echo $complaint['date']; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $complaint['complaint_status'])); ?>">
                            <?php echo $complaint['complaint_status']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="generate_report.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-secondary">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.status-police { background-color: #e3f2fd; color: #1976d2; }
.status-court { background-color: #e8f5e8; color: #388e3c; }
.status-admin { background-color: #fce4ec; color: #c2185b; }
.status-lawyer { background-color: #fff3e0; color: #f57c00; }
</style>

<?php include 'footer.php'; ?>
