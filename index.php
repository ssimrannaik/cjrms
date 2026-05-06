<?php
/**
 * Dashboard Page for CJRMS
 * Shows role-based statistics and quick actions
 */
require_once 'auth_guard.php';
require_once 'db.php';

// Require login
requireLogin();

$pageTitle = 'Dashboard - CJRMS';
$currentRole = getCurrentUserRole();
$currentUserId = getCurrentUserId();

// Get statistics based on role
$stats = [];

try {
    if ($currentRole === 'Police') {
        // Police statistics - their own complaints
        $stats['new_complaints'] = fetchRow(
            "SELECT COUNT(*) as count FROM Complaint WHERE complaint_status = 'New' AND created_by = ?",
            [$currentUserId]
        )['count'];
        
        $stats['under_investigation'] = fetchRow(
            "SELECT COUNT(*) as count FROM Complaint WHERE complaint_status = 'Under Investigation' AND created_by = ?",
            [$currentUserId]
        )['count'];
        
        $stats['charge_sheeted'] = fetchRow(
            "SELECT COUNT(*) as count FROM Complaint WHERE complaint_status = 'Charge-Sheeted' AND created_by = ?",
            [$currentUserId]
        )['count'];
        
        $stats['total_complaints'] = fetchRow(
            "SELECT COUNT(*) as count FROM Complaint WHERE created_by = ?",
            [$currentUserId]
        )['count'];
        
        // Recent complaints by this officer
        $recent_complaints = fetchAll(
            "SELECT c.complaint_id, c.description, c.date, c.crime_type, c.complaint_status, v.name as victim_name 
             FROM Complaint c 
             JOIN Victim v ON c.victim_id = v.victim_id 
             WHERE c.created_by = ? 
             ORDER BY c.date DESC LIMIT 5",
            [$currentUserId]
        );
        
    } elseif ($currentRole === 'Court') {
        // Court statistics - charge-sheeted complaints and cases
        $stats['charge_sheeted_complaints'] = fetchRow(
            "SELECT COUNT(*) as count FROM Complaint WHERE complaint_status = 'Charge-Sheeted'"
        )['count'];
        
        $stats['open_cases'] = fetchRow(
            "SELECT COUNT(*) as count FROM Cases WHERE case_status = 'Open'"
        )['count'];
        
        $stats['under_trial'] = fetchRow(
            "SELECT COUNT(*) as count FROM Cases WHERE case_status = 'Under Trial'"
        )['count'];
        
        $stats['total_cases'] = fetchRow(
            "SELECT COUNT(*) as count FROM Cases"
        )['count'];
        
        // Recent cases
        $recent_cases = fetchAll(
            "SELECT ca.case_id, ca.case_type, ca.start_date, ca.case_status, 
                    co.name as court_name, c.description as complaint_desc
             FROM Cases ca
             JOIN Court co ON ca.court_id = co.court_id
             JOIN Complaint c ON ca.complaint_id = c.complaint_id
             ORDER BY ca.start_date DESC LIMIT 5"
        );
        
    } elseif ($currentRole === 'Admin') {
        // Admin statistics - overview of entire system
        $stats['total_complaints'] = fetchRow(
            "SELECT COUNT(*) as count FROM Complaint"
        )['count'];
        
        $stats['total_cases'] = fetchRow(
            "SELECT COUNT(*) as count FROM Cases"
        )['count'];
        
        $stats['total_criminals'] = fetchRow(
            "SELECT COUNT(*) as count FROM Criminal"
        )['count'];
        
        $stats['total_users'] = fetchRow(
            "SELECT COUNT(*) as count FROM Users"
        )['count'];
        
        // System activity overview
        $recent_activity = fetchAll(
            "SELECT 'Complaint' as type, complaint_id as id, description as title, date as activity_date 
             FROM Complaint 
             ORDER BY created_at DESC LIMIT 3
             UNION ALL
             SELECT 'Case' as type, case_id as id, case_type as title, start_date as activity_date 
             FROM Cases 
             ORDER BY created_at DESC LIMIT 3
             ORDER BY activity_date DESC LIMIT 5"
        );
        
    } elseif ($currentRole === 'Lawyer') {
        // Lawyer statistics - their assigned cases
        $stats['assigned_cases'] = fetchRow(
            "SELECT COUNT(*) as count FROM Case_Lawyer_Link cll 
             JOIN Cases c ON cll.case_id = c.case_id 
             WHERE cll.lawyer_id IN (SELECT lawyer_id FROM Lawyer WHERE name = ?)",
            [getCurrentUserFullName()]
        )['count'];
        
        $stats['open_cases'] = fetchRow(
            "SELECT COUNT(*) as count FROM Case_Lawyer_Link cll 
             JOIN Cases c ON cll.case_id = c.case_id 
             WHERE c.case_status = 'Open' AND cll.lawyer_id IN (SELECT lawyer_id FROM Lawyer WHERE name = ?)",
            [getCurrentUserFullName()]
        )['count'];
        
        // Assigned cases
        $assigned_cases = fetchAll(
            "SELECT c.case_id, c.case_type, c.start_date, c.case_status, 
                    cll.role as lawyer_role, co.name as court_name
             FROM Cases c
             JOIN Case_Lawyer_Link cll ON c.case_id = cll.case_id
             JOIN Court co ON c.court_id = co.court_id
             JOIN Lawyer l ON cll.lawyer_id = l.lawyer_id
             WHERE l.name = ?
             ORDER BY c.start_date DESC LIMIT 5",
            [getCurrentUserFullName()]
        );
    }
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = [];
}

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <div class="breadcrumb">
        <span>Welcome back, <?php echo sanitizeOutput(getCurrentUserFullName()); ?>!</span>
    </div>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <?php if ($currentRole === 'Police'): ?>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['new_complaints'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">New Complaints</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #ffc107; margin-bottom: 0.5rem;"><?php echo $stats['under_investigation'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Under Investigation</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['charge_sheeted'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Charge-Sheeted</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #6c757d; margin-bottom: 0.5rem;"><?php echo $stats['total_complaints'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Total Complaints</p>
            </div>
        </div>
    <?php elseif ($currentRole === 'Court'): ?>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['charge_sheeted_complaints'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Pending Promotion</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['open_cases'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Open Cases</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #ffc107; margin-bottom: 0.5rem;"><?php echo $stats['under_trial'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Under Trial</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #6c757d; margin-bottom: 0.5rem;"><?php echo $stats['total_cases'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Total Cases</p>
            </div>
        </div>
    <?php elseif ($currentRole === 'Admin'): ?>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['total_complaints'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Total Complaints</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['total_cases'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Total Cases</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #dc3545; margin-bottom: 0.5rem;"><?php echo $stats['total_criminals'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Total Criminals</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #6c757d; margin-bottom: 0.5rem;"><?php echo $stats['total_users'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">System Users</p>
            </div>
        </div>
    <?php elseif ($currentRole === 'Lawyer'): ?>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['assigned_cases'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Assigned Cases</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <h3 style="color: #28a745; margin-bottom: 0.5rem;"><?php echo $stats['open_cases'] ?? 0; ?></h3>
                <p style="color: #666; margin: 0;">Active Cases</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Quick Actions</h3>
    </div>
    <div class="card-body">
        <?php if ($currentRole === 'Police'): ?>
            <a href="register_complaint.php" class="btn btn-primary">📝 Register New Complaint</a>
            <a href="manage_complaints.php" class="btn btn-secondary">📋 Manage Complaints</a>
            <a href="search.php" class="btn btn-secondary">🔍 Search Records</a>
        <?php elseif ($currentRole === 'Court'): ?>
            <a href="manage_complaints.php" class="btn btn-primary">📋 Review Complaints</a>
            <a href="manage_cases.php" class="btn btn-secondary">⚖️ Manage Cases</a>
            <a href="search.php" class="btn btn-secondary">🔍 Search Records</a>
        <?php elseif ($currentRole === 'Admin'): ?>
            <a href="manage_admin.php" class="btn btn-primary">⚙️ Admin Panel</a>
            <a href="manage_complaints.php" class="btn btn-secondary">📋 All Complaints</a>
            <a href="manage_cases.php" class="btn btn-secondary">⚖️ All Cases</a>
            <a href="search.php" class="btn btn-secondary">🔍 Search Records</a>
        <?php elseif ($currentRole === 'Lawyer'): ?>
            <a href="manage_cases.php" class="btn btn-primary">⚖️ My Cases</a>
            <a href="search.php" class="btn btn-secondary">🔍 Search Records</a>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activity -->
<?php if ($currentRole === 'Police' && !empty($recent_complaints)): ?>
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
                    <th>Victim</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_complaints as $complaint): ?>
                <tr>
                    <td><?php echo sanitizeOutput($complaint['complaint_id']); ?></td>
                    <td><?php echo sanitizeOutput($complaint['crime_type']); ?></td>
                    <td><?php echo sanitizeOutput($complaint['victim_name']); ?></td>
                    <td><?php echo sanitizeOutput($complaint['date']); ?></td>
                    <td>
                        <span class="status-badge <?php echo getStatusClass($complaint['complaint_status']); ?>">
                            <?php echo sanitizeOutput($complaint['complaint_status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="generate_report.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-secondary">PDF</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($currentRole === 'Court' && !empty($recent_cases)): ?>
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Recent Cases</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Case Type</th>
                    <th>Court</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_cases as $case): ?>
                <tr>
                    <td><?php echo sanitizeOutput($case['case_id']); ?></td>
                    <td><?php echo sanitizeOutput($case['case_type']); ?></td>
                    <td><?php echo sanitizeOutput($case['court_name']); ?></td>
                    <td><?php echo sanitizeOutput($case['start_date']); ?></td>
                    <td>
                        <span class="status-badge <?php echo getStatusClass($case['case_status']); ?>">
                            <?php echo sanitizeOutput($case['case_status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="case_details.php?id=<?php echo $case['case_id']; ?>" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($currentRole === 'Lawyer' && !empty($assigned_cases)): ?>
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">My Assigned Cases</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Case Type</th>
                    <th>My Role</th>
                    <th>Court</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned_cases as $case): ?>
                <tr>
                    <td><?php echo sanitizeOutput($case['case_id']); ?></td>
                    <td><?php echo sanitizeOutput($case['case_type']); ?></td>
                    <td><?php echo sanitizeOutput($case['lawyer_role']); ?></td>
                    <td><?php echo sanitizeOutput($case['court_name']); ?></td>
                    <td>
                        <span class="status-badge <?php echo getStatusClass($case['case_status']); ?>">
                            <?php echo sanitizeOutput($case['case_status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="case_details.php?id=<?php echo $case['case_id']; ?>" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
// Add JavaScript for status styling
echo '<script>';
echo 'function getStatusClass(status) {';
echo '    const statusMap = {';
echo '        "New": "status-new",';
echo '        "Under Investigation": "status-investigation",';
echo '        "Withdrawn": "status-withdrawn",';
echo '        "Charge-Sheeted": "status-charge-sheeted",';
echo '        "Promoted to Case": "status-promoted",';
echo '        "Open": "status-open",';
echo '        "Under Trial": "status-trial",';
echo '        "Closed": "status-closed"';
echo '    };';
echo '    return statusMap[status] || "status-new";';
echo '}';
echo '</script>';

include 'footer.php';
?>
