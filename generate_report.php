<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireLogin();

$complaint_id = (int)($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    header('Location: manage_complaints.php?error=invalid_id');
    exit();
}

try {
    // Get complaint details with victim info
    $complaint = fetchRow(
        "SELECT c.*, v.name as victim_name, v.contact_info as victim_contact,
                u.full_name as officer_name
         FROM Complaint c 
         JOIN Victim v ON c.victim_id = v.victim_id 
         LEFT JOIN Users u ON c.created_by = u.user_id
         WHERE c.complaint_id = ?",
        [$complaint_id]
    );
    
    if (!$complaint) {
        header('Location: manage_complaints.php?error=complaint_not_found');
        exit();
    }
    
    // Get linked criminals
    $criminals = fetchAll(
        "SELECT cr.name, cr.age, cr.identification_detail 
         FROM Criminal cr
         JOIN Complaint_Criminal_Link ccl ON cr.criminal_id = ccl.criminal_id
         WHERE ccl.complaint_id = ?",
        [$complaint_id]
    );
    
    // For now, create a simple HTML report (can be enhanced with FPDF later)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="FIR_' . $complaint_id . '.pdf"');
    
    // Simple text-based report for demonstration
    echo "FIRST INFORMATION REPORT (FIR)\n";
    echo str_repeat("=", 50) . "\n\n";
    echo "FIR No: " . $complaint['complaint_id'] . "\n";
    echo "Date: " . $complaint['date'] . "\n";
    echo "Crime Type: " . $complaint['crime_type'] . "\n";
    echo "Status: " . $complaint['complaint_status'] . "\n\n";
    
    echo "VICTIM INFORMATION:\n";
    echo "Name: " . $complaint['victim_name'] . "\n";
    echo "Contact: " . ($complaint['victim_contact'] ?: 'Not provided') . "\n\n";
    
    echo "INCIDENT DESCRIPTION:\n";
    echo wordwrap($complaint['description'], 70) . "\n\n";
    
    echo "SUSPECTS:\n";
    if (empty($criminals)) {
        echo "None identified at this time.\n";
    } else {
        foreach ($criminals as $criminal) {
            echo "- " . $criminal['name'] . " (Age: " . ($criminal['age'] ?: 'Unknown') . ")\n";
            if ($criminal['identification_detail']) {
                echo "  Details: " . $criminal['identification_detail'] . "\n";
            }
        }
    }
    
    echo "\n\nReporting Officer: " . ($complaint['officer_name'] ?: 'Unknown') . "\n";
    echo "Generated on: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    error_log("Report generation error: " . $e->getMessage());
    header('Location: manage_complaints.php?error=report_failed');
    exit();
}
?>
