<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole(['Court', 'Admin', 'Lawyer']);

$pageTitle = 'Case Details - CJRMS';
$case_id = (int)($_GET['id'] ?? 0);

if ($case_id <= 0) {
    header('Location: manage_cases.php?error=invalid_case_id');
    exit();
}

// Get case details
$case = fetchRow(
    "SELECT c.*, co.name as court_name, co.location as court_location,
            comp.description as complaint_desc, comp.crime_type, comp.date as complaint_date,
            v.name as victim_name
     FROM Cases c
     JOIN Court co ON c.court_id = co.court_id
     JOIN Complaint comp ON c.complaint_id = comp.complaint_id
     JOIN Victim v ON comp.victim_id = v.victim_id
     WHERE c.case_id = ?",
    [$case_id]
);

if (!$case) {
    header('Location: manage_cases.php?error=case_not_found');
    exit();
}

// Get linked criminals
$criminals = fetchAll(
    "SELECT cr.criminal_id, cr.name, cr.age, cr.identification_detail
     FROM Criminal cr
     JOIN Case_Criminal_Link ccl ON cr.criminal_id = ccl.criminal_id
     WHERE ccl.case_id = ?",
    [$case_id]
);

// Get assigned lawyers
$lawyers = fetchAll(
    "SELECT l.lawyer_id, l.name, l.type, l.contact_info, cll.role
     FROM Lawyer l
     JOIN Case_Lawyer_Link cll ON l.lawyer_id = cll.lawyer_id
     WHERE cll.case_id = ?",
    [$case_id]
);

// Get judgments
$judgments = fetchAll(
    "SELECT * FROM Judgement WHERE case_id = ? ORDER BY date DESC",
    [$case_id]
);

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Case Details #<?php echo $case_id; ?></h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / <a href="manage_cases.php">Cases</a> / Case Details
    </div>
</div>

<!-- Case Information -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Case Information</h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>Case Type</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($case['case_type']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Status</label>
                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $case['case_status'])); ?>">
                    <?php echo $case['case_status']; ?>
                </span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Court</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($case['court_name'] . ' - ' . $case['court_location']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="text" class="form-control" value="<?php echo $case['start_date']; ?>" readonly>
            </div>
        </div>
    </div>
</div>

<!-- Original Complaint -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Original Complaint</h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>Crime Type</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($case['crime_type']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Victim</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($case['victim_name']); ?>" readonly>
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" rows="3" readonly><?php echo sanitizeOutput($case['complaint_desc']); ?></textarea>
        </div>
    </div>
</div>

<!-- Linked Criminals -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Accused/Suspects</h3>
    </div>
    <div class="card-body">
        <?php if (empty($criminals)): ?>
            <p>No criminals linked to this case.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Identification Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($criminals as $criminal): ?>
                    <tr>
                        <td><?php echo sanitizeOutput($criminal['name']); ?></td>
                        <td><?php echo $criminal['age'] ?: 'Unknown'; ?></td>
                        <td><?php echo sanitizeOutput($criminal['identification_detail'] ?: 'None'); ?></td>
                        <td>
                            <a href="criminal_profile.php?id=<?php echo $criminal['criminal_id']; ?>" class="btn btn-sm btn-primary">View Profile</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Assigned Lawyers -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Assigned Lawyers</h3>
    </div>
    <div class="card-body">
        <?php if (empty($lawyers)): ?>
            <p>No lawyers assigned to this case.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Role in Case</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lawyers as $lawyer): ?>
                    <tr>
                        <td><?php echo sanitizeOutput($lawyer['name']); ?></td>
                        <td><?php echo sanitizeOutput($lawyer['type']); ?></td>
                        <td><?php echo sanitizeOutput($lawyer['role']); ?></td>
                        <td><?php echo sanitizeOutput($lawyer['contact_info']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Judgments -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Judgments & Orders</h3>
    </div>
    <div class="card-body">
        <?php if (empty($judgments)): ?>
            <p>No judgments recorded for this case.</p>
        <?php else: ?>
            <?php foreach ($judgments as $judgment): ?>
                <div style="border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">
                    <h4 style="margin: 0 0 0.5rem 0;"><?php echo sanitizeOutput($judgment['verdict']); ?></h4>
                    <p style="margin: 0.5rem 0; color: #666;">Date: <?php echo $judgment['date']; ?></p>
                    <?php if ($judgment['punishment']): ?>
                        <p style="margin: 0.5rem 0;"><?php echo sanitizeOutput($judgment['punishment']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
