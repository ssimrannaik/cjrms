<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole(['Court', 'Admin']);

$pageTitle = 'Add Case - CJRMS';
$complaint_id = (int)($_GET['complaint_id'] ?? 0);

if ($complaint_id <= 0) {
    header('Location: manage_complaints.php?error=invalid_complaint_id');
    exit();
}

// Get complaint details
$complaint = fetchRow(
    "SELECT c.*, v.name as victim_name 
     FROM Complaint c 
     JOIN Victim v ON c.victim_id = v.victim_id 
     WHERE c.complaint_id = ? AND c.complaint_status = 'Charge-Sheeted'",
    [$complaint_id]
);

if (!$complaint) {
    header('Location: manage_complaints.php?error=complaint_not_eligible');
    exit();
}

// Get courts
$courts = fetchAll("SELECT court_id, name, location FROM Court ORDER BY name");

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Promote Complaint to Case</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / <a href="manage_complaints.php">Complaints</a> / Add Case
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Complaint Details (Read-Only)</h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>Complaint ID</label>
                <input type="text" class="form-control" value="<?php echo $complaint['complaint_id']; ?>" readonly>
            </div>
            <div class="form-group">
                <label>Crime Type</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($complaint['crime_type']); ?>" readonly>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Victim</label>
                <input type="text" class="form-control" value="<?php echo sanitizeOutput($complaint['victim_name']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="text" class="form-control" value="<?php echo $complaint['date']; ?>" readonly>
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" rows="3" readonly><?php echo sanitizeOutput($complaint['description']); ?></textarea>
        </div>
    </div>
</div>

<form action="handle_add_case.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
    
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Case Information</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="case_type">Case Type *</label>
                    <input type="text" id="case_type" name="case_type" class="form-control" 
                           value="Criminal Case - <?php echo sanitizeOutput($complaint['crime_type']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Case Start Date *</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="court_id">Assigned Court *</label>
                    <select id="court_id" name="court_id" class="form-control" required>
                        <option value="">Select Court</option>
                        <?php foreach ($courts as $court): ?>
                            <option value="<?php echo $court['court_id']; ?>">
                                <?php echo sanitizeOutput($court['name'] . ' - ' . $court['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="case_status">Initial Status</label>
                    <select id="case_status" name="case_status" class="form-control">
                        <option value="Open" selected>Open</option>
                        <option value="Under Trial">Under Trial</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <button type="submit" class="btn btn-success">⚖️ Create Case</button>
            <a href="manage_complaints.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<?php include 'footer.php'; ?>
