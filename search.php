<?php
require_once 'auth_guard.php';
require_once 'db.php';

requireLogin();

$pageTitle = 'Search - CJRMS';
$query = sanitizeInput($_GET['q'] ?? '');
$results = [];

if (!empty($query)) {
    try {
        // Search complaints
        $complaint_results = fetchAll(
            "SELECT 'complaint' as type, complaint_id as id, crime_type as title, 
                    description, date as result_date
             FROM Complaint 
             WHERE crime_type LIKE ? OR description LIKE ?",
            ['%' . $query . '%', '%' . $query . '%']
        );
        
        // Search cases
        $case_results = fetchAll(
            "SELECT 'case' as type, case_id as id, case_type as title, 
                    '' as description, start_date as result_date
             FROM Cases 
             WHERE case_type LIKE ?",
            ['%' . $query . '%']
        );
        
        // Search criminals
        $criminal_results = fetchAll(
            "SELECT 'criminal' as type, criminal_id as id, name as title, 
                    identification_detail as description, '' as result_date
             FROM Criminal 
             WHERE name LIKE ? OR identification_detail LIKE ?",
            ['%' . $query . '%', '%' . $query . '%']
        );
        
        $results = array_merge($complaint_results, $case_results, $criminal_results);
        
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
    }
}

include 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">Search Records</h1>
    <div class="breadcrumb">
        <a href="index.php">Dashboard</a> / Search
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="search.php">
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <input type="text" name="q" class="form-control" 
                           placeholder="Search complaints, cases, or criminals..." 
                           value="<?php echo sanitizeOutput($query); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($query)): ?>
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Search Results for "<?php echo sanitizeOutput($query); ?>"</h3>
    </div>
    <div class="card-body">
        <?php if (empty($results)): ?>
            <p>No results found.</p>
        <?php else: ?>
            <?php foreach ($results as $result): ?>
                <div style="border-bottom: 1px solid #eee; padding: 1rem 0;">
                    <h4 style="margin: 0 0 0.5rem 0;">
                        <?php if ($result['type'] === 'complaint'): ?>
                            📋 Complaint #<?php echo $result['id']; ?>: <?php echo sanitizeOutput($result['title']); ?>
                        <?php elseif ($result['type'] === 'case'): ?>
                            ⚖️ Case #<?php echo $result['id']; ?>: <?php echo sanitizeOutput($result['title']); ?>
                        <?php else: ?>
                            👤 Criminal: <?php echo sanitizeOutput($result['title']); ?>
                        <?php endif; ?>
                    </h4>
                    
                    <?php if (!empty($result['description'])): ?>
                        <p style="margin: 0.5rem 0; color: #666;">
                            <?php echo sanitizeOutput(substr($result['description'], 0, 200)); ?>
                            <?php if (strlen($result['description']) > 200) echo '...'; ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($result['result_date'])): ?>
                        <small style="color: #999;">Date: <?php echo $result['result_date']; ?></small>
                    <?php endif; ?>
                    
                    <div style="margin-top: 0.5rem;">
                        <?php if ($result['type'] === 'complaint'): ?>
                            <a href="generate_report.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-secondary">View PDF</a>
                        <?php elseif ($result['type'] === 'case'): ?>
                            <a href="case_details.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-primary">View Case</a>
                        <?php else: ?>
                            <a href="criminal_profile.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-primary">View Profile</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
