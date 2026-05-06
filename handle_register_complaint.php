<?php
/**
 * Handle Register Complaint for CJRMS
 * Processes complaint registration form submission
 */
require_once 'auth_guard.php';
require_once 'db.php';

// Require Police role
requireRole('Police');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register_complaint.php');
    exit();
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: register_complaint.php?error=invalid_request');
    exit();
}

// Get and sanitize input
$victim_name = sanitizeInput($_POST['victim_name'] ?? '');
$victim_contact = sanitizeInput($_POST['victim_contact'] ?? '');
$crime_type = sanitizeInput($_POST['crime_type'] ?? '');
$complaint_date = sanitizeInput($_POST['complaint_date'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$suspects = $_POST['suspects'] ?? [];

// Validate required fields
$errors = [];

if (empty($victim_name)) {
    $errors[] = 'Victim name is required.';
}

if (empty($crime_type)) {
    $errors[] = 'Crime type is required.';
}

if (empty($complaint_date)) {
    $errors[] = 'Incident date is required.';
}

if (empty($description) || strlen($description) < 20) {
    $errors[] = 'Detailed description is required (minimum 20 characters).';
}

// Validate date
if (!empty($complaint_date)) {
    $date = DateTime::createFromFormat('Y-m-d', $complaint_date);
    if (!$date || $date->format('Y-m-d') !== $complaint_date) {
        $errors[] = 'Invalid date format.';
    } elseif ($date > new DateTime()) {
        $errors[] = 'Incident date cannot be in the future.';
    }
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    $error_message = implode(' ', $errors);
    header('Location: register_complaint.php?error=' . urlencode($error_message));
    exit();
}

try {
    // Begin transaction
    beginTransaction();
    
    $current_user_id = getCurrentUserId();
    $victim_id = null;
    
    // Check if victim already exists (by name - simple approach)
    $existing_victim = fetchRow(
        "SELECT victim_id FROM Victim WHERE name = ? LIMIT 1",
        [$victim_name]
    );
    
    if ($existing_victim) {
        // Use existing victim
        $victim_id = $existing_victim['victim_id'];
        
        // Update contact info if provided and different
        if (!empty($victim_contact)) {
            executeQuery(
                "UPDATE Victim SET contact_info = ? WHERE victim_id = ?",
                [$victim_contact, $victim_id]
            );
        }
    } else {
        // Insert new victim
        executeQuery(
            "INSERT INTO Victim (name, contact_info) VALUES (?, ?)",
            [$victim_name, $victim_contact]
        );
        $victim_id = getLastInsertId();
    }
    
    // Insert complaint
    executeQuery(
        "INSERT INTO Complaint (description, date, crime_type, victim_id, complaint_status, created_by) 
         VALUES (?, ?, ?, ?, 'New', ?)",
        [$description, $complaint_date, $crime_type, $victim_id, $current_user_id]
    );
    
    $complaint_id = getLastInsertId();
    
    // Link suspects if any were selected
    if (!empty($suspects) && is_array($suspects)) {
        foreach ($suspects as $criminal_id) {
            $criminal_id = (int)$criminal_id;
            if ($criminal_id > 0) {
                // Check if criminal exists
                $criminal_exists = fetchRow(
                    "SELECT criminal_id FROM Criminal WHERE criminal_id = ?",
                    [$criminal_id]
                );
                
                if ($criminal_exists) {
                    // Insert complaint-criminal link
                    executeQuery(
                        "INSERT IGNORE INTO Complaint_Criminal_Link (complaint_id, criminal_id) VALUES (?, ?)",
                        [$complaint_id, $criminal_id]
                    );
                }
            }
        }
    }
    
    // Commit transaction
    commitTransaction();
    
    // Redirect with success message
    header('Location: manage_complaints.php?success=complaint_registered&id=' . $complaint_id);
    exit();
    
} catch (Exception $e) {
    // Rollback transaction
    rollbackTransaction();
    
    // Log error
    error_log("Complaint registration error: " . $e->getMessage());
    
    // Redirect with error
    header('Location: register_complaint.php?error=registration_failed');
    exit();
}
?>
