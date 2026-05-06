<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireLogin();

$pageTitle = 'Criminal Profile - CJRMS';
$criminal_id = (int)($_GET['id'] ?? 0);

if ($criminal_id <= 0) {
    header('Location: search.php?error=invalid_criminal_id');
    exit();
}

// Get criminal details
$criminal = fetchRow(
    "SELECT * FROM Criminal WHERE criminal_id = ?",
    [$criminal_id]
);

if (!$criminal) {
    header('Location: search.php?error=criminal_not_found');
    exit();
}

// Get linked complaints
$complaints = fetchAll(
    "SELECT c.complaint_id, c.crime_type, c.date, c.complaint_status, v.name as victim_name
     FROM Complaint c
     JOIN Complaint_Criminal_Link ccl ON c.complaint_id = ccl.complaint_id
     JOIN Victim v ON c.victim_id = v.victim_id
     WHERE ccl.criminal_id = ?
     ORDER BY c.date DESC",
    [$criminal_id]
);

// Get linked cases
$cases = fetchAll(
    "SELECT ca.case_id, ca.case_type, ca.start_date, ca.case_status, co.name as court_name
     FROM Cases ca
     JOIN Case_Criminal_Link ccl ON ca.case_id = ccl.case_id
     JOIN Court co ON ca.court_id = co.court_id
     WHERE ccl.criminal_id = ?
     ORDER BY ca.start_date DESC",
    [$criminal_id]
);

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Criminal Profile</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / <a href="search.php">Search</a> / Criminal Profile
    </div>
</div>

<!-- Criminal Information -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Personal Information</h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($criminal['name']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Age</label>
                <input type="text" class="form-control" value="<?php echo $criminal['age'] ?: 'Unknown'; ?>" readonly>
            </div>
        </div>
        <div class="form-group">
            <label>Identification Details</label>
            <textarea class="form-control" rows="3" readonly><?php echo sanitizeOutput($criminal['identification_detail'] ?: 'No details available'); ?></textarea>
        </div>
    </div>
</div>

<!-- Linked Complaints -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Associated Complaints</h3>
    </div>
    <div class="card-body">
        <?php if (empty($complaints)): ?>
            <p>No complaints linked to this criminal.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Complaint ID</th>
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
                            <a href="generate_report.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Linked Cases -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Associated Cases</h3>
    </div>
    <div class="card-body">
        <?php if (empty($cases)): ?>
            <p>No cases linked to this criminal.</p>
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

<!-- Criminal History Summary -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Summary</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="text-align: center;">
                <h4 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo count($complaints); ?></h4>
                <p style="color: #666; margin: 0;">Total Complaints</p>
            </div>
            <div style="text-align: center;">
                <h4 style="color: #28a745; margin-bottom: 0.5rem;"><?php echo count($cases); ?></h4>
                <p style="color: #666; margin: 0;">Total Cases</p>
            </div>
            <div style="text-align: center;">
                <h4 style="color: #ffc107; margin-bottom: 0.5rem;">
                    <?php 
                    $active_cases = array_filter($cases, function($case) {
                        return $case['case_status'] !== 'Closed';
                    });
                    echo count($active_cases);
                    ?>
                </h4>
                <p style="color: #666; margin: 0;">Active Cases</p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
