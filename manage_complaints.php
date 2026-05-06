<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole(['Police', 'Court', 'Admin']);

$pageTitle = 'Manage Complaints - CJRMS';
$currentRole = getCurrentUserRole();
$currentUserId = getCurrentUserId();

// Build query based on role
if ($currentRole === 'Police') {
    $sql = "SELECT c.*, v.name as victim_name FROM Complaint c 
            JOIN Victim v ON c.victim_id = v.victim_id 
            WHERE c.created_by = ? ORDER BY c.date DESC";
    $params = [$currentUserId];
} elseif ($currentRole === 'Court') {
    $sql = "SELECT c.*, v.name as victim_name FROM Complaint c 
            JOIN Victim v ON c.victim_id = v.victim_id 
            WHERE c.complaint_status = 'Charge-Sheeted' ORDER BY c.date DESC";
    $params = [];
} else { // Admin
    $sql = "SELECT c.*, v.name as victim_name FROM Complaint c 
            JOIN Victim v ON c.victim_id = v.victim_id 
            ORDER BY c.date DESC";
    $params = [];
}

$complaints = fetchAll($sql, $params);

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Manage Complaints</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / Manage Complaints
    </div>
</div>

<?php if ($currentRole === 'Police'): ?>
<div class="card">
    <div class="card-body">
        <a href="register_complaint.php" class="btn btn-primary">📝 Register New Complaint</a>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">
            <?php 
            if ($currentRole === 'Police') echo 'My Complaints';
            elseif ($currentRole === 'Court') echo 'Complaints for Review';
            else echo 'All Complaints';
            ?>
        </h3>
    </div>
    <div class="card-body">
        <?php if (empty($complaints)): ?>
            <p>No complaints found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Crime Type</th>
                        <th>Victim</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><?php echo $complaint['complaint_id']; ?></td>
                        <td><?php echo sanitizeOutput($complaint['crime_type']); ?></td>
                        <td><?php echo sanitizeOutput($complaint['victim_name']); ?></td>
                        <td><?php echo $complaint['date']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $complaint['complaint_status'])); ?>">
                                <?php echo $complaint['complaint_status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="generate_report.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-secondary">PDF</a>
                            
                            <?php if ($currentRole === 'Police' && $complaint['complaint_status'] !== 'Promoted to Case'): ?>
                                <form method="POST" action="handle_update_complaint_status.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" class="btn btn-sm btn-warning">
                                        <option value="">Update Status</option>
                                        <option value="Under Investigation">Under Investigation</option>
                                        <option value="Charge-Sheeted">Charge-Sheeted</option>
                                        <option value="Withdrawn">Withdrawn</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($currentRole === 'Court' && $complaint['complaint_status'] === 'Charge-Sheeted'): ?>
                                <a href="add_case.php?complaint_id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-success">Promote to Case</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
