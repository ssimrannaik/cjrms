<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole(['Court', 'Admin', 'Lawyer']);

$pageTitle = 'Manage Cases - CJRMS';
$currentRole = getCurrentUserRole();

// Get cases based on role
if ($currentRole === 'Lawyer') {
    $cases = fetchAll(
        "SELECT c.*, co.name as court_name, comp.crime_type
         FROM Cases c
         JOIN Court co ON c.court_id = co.court_id
         JOIN Complaint comp ON c.complaint_id = comp.complaint_id
         JOIN Case_Lawyer_Link cll ON c.case_id = cll.case_id
         JOIN Lawyer l ON cll.lawyer_id = l.lawyer_id
         WHERE l.name = ?
         ORDER BY c.start_date DESC",
        [getCurrentUserFullName()]
    );
} else {
    $cases = fetchAll(
        "SELECT c.*, co.name as court_name, comp.crime_type
         FROM Cases c
         JOIN Court co ON c.court_id = co.court_id
         JOIN Complaint comp ON c.complaint_id = comp.complaint_id
         ORDER BY c.start_date DESC"
    );
}

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Manage Cases</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / Manage Cases
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">
            <?php echo $currentRole === 'Lawyer' ? 'My Assigned Cases' : 'All Cases'; ?>
        </h3>
    </div>
    <div class="card-body">
        <?php if (empty($cases)): ?>
            <p>No cases found.</p>
        <?php else: ?>
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
                    <?php foreach ($cases as $case): ?>
                    <tr>
                        <td><?php echo $case['case_id']; ?></td>
                        <td><?php echo sanitizeOutput($case['case_type']); ?></td>
                        <td><?php echo sanitizeOutput($case['court_name']); ?></td>
                        <td><?php echo $case['start_date']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $case['case_status'])); ?>">
                                <?php echo $case['case_status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="case_details.php?id=<?php echo $case['case_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
