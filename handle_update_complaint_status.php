<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole('Police');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_complaints.php');
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: manage_complaints.php?error=invalid_request');
    exit();
}

$complaint_id = (int)($_POST['complaint_id'] ?? 0);
$new_status = sanitizeInput($_POST['new_status'] ?? '');

$valid_statuses = ['Under Investigation', 'Charge-Sheeted', 'Withdrawn'];

if ($complaint_id <= 0 || !in_array($new_status, $valid_statuses)) {
    header('Location: manage_complaints.php?error=invalid_data');
    exit();
}

try {
    // Verify complaint belongs to current user
    $complaint = fetchRow(
        "SELECT complaint_id FROM Complaint WHERE complaint_id = ? AND created_by = ?",
        [$complaint_id, getCurrentUserId()]
    );
    
    if (!$complaint) {
        header('Location: manage_complaints.php?error=access_denied');
        exit();
    }
    
    // Update status
    executeQuery(
        "UPDATE Complaint SET complaint_status = ? WHERE complaint_id = ?",
        [$new_status, $complaint_id]
    );
    
    header('Location: manage_complaints.php?success=status_updated');
    exit();
    
} catch (Exception $e) {
    error_log("Status update error: " . $e->getMessage());
    header('Location: manage_complaints.php?error=update_failed');
    exit();
}
?>
