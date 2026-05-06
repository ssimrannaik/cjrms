<?php
/**
 * Register Complaint Page for CJRMS
 * Allows police officers to register new complaints
 */
require_once 'auth_guard.php';
require_once 'db.php';

// Require Police role
requireRole('Police');

$pageTitle = 'Register Complaint - CJRMS';

// Get all criminals for linking (optional)
$criminals = fetchAll("SELECT criminal_id, name FROM Criminal ORDER BY name");

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Register New Complaint</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / Register Complaint
    </div>
</div>

<form action="handle_register_complaint.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Victim Information</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="victim_name">Victim Name *</label>
                    <input type="text" id="victim_name" name="victim_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="victim_contact">Contact Information</label>
                    <textarea id="victim_contact" name="victim_contact" class="form-control" rows="3" 
                              placeholder="Phone, Address, Email, etc."></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Complaint Details</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="crime_type">Crime Type *</label>
                    <select id="crime_type" name="crime_type" class="form-control" required>
                        <option value="">Select Crime Type</option>
                        <option value="Theft">Theft</option>
                        <option value="Assault">Assault</option>
                        <option value="Fraud">Fraud</option>
                        <option value="Vandalism">Vandalism</option>
                        <option value="Domestic Violence">Domestic Violence</option>
                        <option value="Burglary">Burglary</option>
                        <option value="Robbery">Robbery</option>
                        <option value="Drug Offense">Drug Offense</option>
                        <option value="Cybercrime">Cybercrime</option>
                        <option value="Murder">Murder</option>
                        <option value="Kidnapping">Kidnapping</option>
                        <option value="Sexual Assault">Sexual Assault</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="complaint_date">Date of Incident *</label>
                    <input type="date" id="complaint_date" name="complaint_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Detailed Description *</label>
                <textarea id="description" name="description" class="form-control" rows="6" required
                          placeholder="Provide a detailed description of the incident, including time, location, circumstances, and any other relevant information..."></textarea>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Suspect Information (Optional)</h3>
            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #666;">
                You can link known suspects to this complaint or add this information later.
            </p>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="suspects">Known Suspects</label>
                <select id="suspects" name="suspects[]" class="form-control" multiple size="5">
                    <?php foreach ($criminals as $criminal): ?>
                        <option value="<?php echo $criminal['criminal_id']; ?>">
                            <?php echo sanitizeOutput($criminal['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #666; font-size: 0.8rem;">
                    Hold Ctrl (Windows) or Cmd (Mac) to select multiple suspects. Leave empty if no suspects identified.
                </small>
            </div>
            
            <div style="margin-top: 1rem; padding: 1rem; background-color: #f8f9fa; border-radius: 5px;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Add New Suspect</h4>
                <p style="margin: 0; font-size: 0.9rem; color: #666;">
                    If the suspect is not in the list above, you can add them to the system first by contacting an administrator, 
                    or link them to this complaint later through the complaint management page.
                </p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <button type="submit" class="btn btn-primary">📝 Register Complaint</button>
            <a href="manage_complaints.php" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.getElementById('description');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    
    // Validate form before submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const description = document.getElementById('description').value.trim();
        const victimName = document.getElementById('victim_name').value.trim();
        const crimeType = document.getElementById('crime_type').value;
        
        if (description.length < 20) {
            e.preventDefault();
            alert('Please provide a more detailed description (at least 20 characters).');
            return;
        }
        
        if (!victimName || !crimeType) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return;
        }
        
        // Confirm submission
        if (!confirm('Are you sure you want to register this complaint? Please review all information before submitting.')) {
            e.preventDefault();
        }
    });
    
    // Set max date to today
    const dateInput = document.getElementById('complaint_date');
    dateInput.max = new Date().toISOString().split('T')[0];
});
</script>

<?php include 'footer.php'; ?>
