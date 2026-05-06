    </main>
    
    <footer style="background-color: #343a40; color: white; text-align: center; padding: 2rem 0; margin-top: 3rem;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
            <p>&copy; <?php echo date('Y'); ?> Criminal Justice Records Management System (CJRMS)</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #adb5bd;">
                Built for educational purposes | PHP + MySQL + XAMPP
            </p>
        </div>
    </footer>
    
    <script>
        // Simple JavaScript for form validation and UX improvements
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
            
            // Confirm delete actions
            const deleteButtons = document.querySelectorAll('.btn-danger[href*="delete"], .btn-danger[onclick*="delete"]');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(function(field) {
                        if (!field.value.trim()) {
                            field.style.borderColor = '#dc3545';
                            isValid = false;
                        } else {
                            field.style.borderColor = '#ced4da';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
            
            // Add loading state to form submissions
            const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const form = button.closest('form');
                    if (form && form.checkValidity()) {
                        button.disabled = true;
                        button.textContent = 'Processing...';
                        
                        // Re-enable after 5 seconds as fallback
                        setTimeout(function() {
                            button.disabled = false;
                            button.textContent = button.getAttribute('data-original-text') || 'Submit';
                        }, 5000);
                    }
                });
            });
            
            // Store original button text
            submitButtons.forEach(function(button) {
                button.setAttribute('data-original-text', button.textContent);
            });
        });
        
        // Utility function for AJAX requests (if needed)
        function makeAjaxRequest(url, method, data, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        callback(null, xhr.responseText);
                    } else {
                        callback(new Error('Request failed'), null);
                    }
                }
            };
            
            if (method === 'POST' && data) {
                xhr.send(data);
            } else {
                xhr.send();
            }
        }
        
        // Function to format dates
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
        
        // Function to show status with appropriate styling
        function getStatusClass(status) {
            const statusMap = {
                'New': 'status-new',
                'Under Investigation': 'status-investigation',
                'Withdrawn': 'status-withdrawn',
                'Charge-Sheeted': 'status-charge-sheeted',
                'Promoted to Case': 'status-promoted',
                'Open': 'status-open',
                'Under Trial': 'status-trial',
                'Closed': 'status-closed'
            };
            return statusMap[status] || 'status-new';
        }
    </script>
</body>
</html>
