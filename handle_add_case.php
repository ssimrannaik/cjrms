<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireRole(['Court', 'Admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_complaints.php');
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: manage_complaints.php?error=invalid_request');
    exit();
}

$complaint_id = (int)($_POST['complaint_id'] ?? 0);
$case_type = sanitizeInput($_POST['case_type'] ?? '');
$start_date = sanitizeInput($_POST['start_date'] ?? '');
$court_id = (int)($_POST['court_id'] ?? 0);
$case_status = sanitizeInput($_POST['case_status'] ?? 'Open');

if ($complaint_id <= 0 || empty($case_type) || empty($start_date) || $court_id <= 0) {
    header('Location: add_case.php?complaint_id=' . $complaint_id . '&error=invalid_data');
    exit();
}

try {
    beginTransaction();
    
    // Insert case
    executeQuery(
        "INSERT INTO Cases (complaint_id, start_date, case_type, case_status, court_id) VALUES (?, ?, ?, ?, ?)",
        [$complaint_id, $start_date, $case_type, $case_status, $court_id]
    );
    
    // Update complaint status
    executeQuery(
        "UPDATE Complaint SET complaint_status = 'Promoted to Case' WHERE complaint_id = ?",
        [$complaint_id]
    );
    
    commitTransaction();
    
    header('Location: manage_cases.php?success=case_created');
    exit();
    
} catch (Exception $e) {
    rollbackTransaction();
    error_log("Case creation error: " . $e->getMessage());
    header('Location: add_case.php?complaint_id=' . $complaint_id . '&error=creation_failed');
    exit();
}
?>
