# cjrms
Criminal Justice Records Management System (CJRMS)
A comprehensive web-based application for managing criminal justice records, built with PHP, MySQL, and designed for XAMPP deployment.

Features
Role-based Authentication: Police, Court Clerk, Admin, and Lawyer roles
Complaint Management: Register, track, and manage complaints
Case Management: Promote complaints to cases, assign lawyers, record judgments
Criminal Profiles: Link criminals to complaints and cases
PDF Report Generation: Generate FIR reports
Search Functionality: Search across complaints, cases, and criminals
Admin Panel: Manage users, courts, and lawyers
System Requirements
XAMPP (Apache + MySQL + PHP 8.x)
Web browser (Chrome, Firefox, Safari, Edge)
At least 100MB free disk space
Installation Instructions
Step 1: Setup XAMPP
Download and install XAMPP from https://www.apachefriends.org/
Start XAMPP Control Panel
Start Apache and MySQL services
Step 2: Database Setup
Open phpMyAdmin in your browser: http://localhost/phpmyadmin
Create a new database named cjrms
Import the database schema:
Click on the cjrms database
Go to Import tab
Choose file: database/cjrms_schema.sql
Click Go
Import the seed data:
Go to Import tab again
Choose file: database/cjrms_seed.sql
Click Go
(Optional) Import evidence table updates:
Choose file: database/add_evidence_table.sql
Step 3: Deploy Application
Copy the entire cjrms folder to C:\xampp\htdocs\
The final path should be: C:\xampp\htdocs\cjrms\
Ensure Apache allows .htaccess overrides and mod_rewrite is enabled (XAMPP usually does this by default)
Step 4: Access the Application
Open your web browser
Navigate to: http://localhost/cjrms/login.php
If URL rewriting is disabled, use: http://localhost/cjrms/public/login.php
Use the test credentials below to login
Test Credentials
Role	Username	Password	Description
Police	police_user	password123	Can register and manage complaints
Court	court_user	password123	Can promote complaints to cases
Admin	admin_user	password123	Full system access
Lawyer	lawyer_user	password123	Can view assigned cases
Testing Checklist
1. Authentication Testing
 Login with each role
 Verify role-based navigation
 Test logout functionality
 Verify access control (try accessing restricted pages)
2. Police User Testing
 Register a new complaint
 View complaint list
 Update complaint status
 Generate PDF report
 Search for records
3. Court User Testing
 View charge-sheeted complaints
 Promote complaint to case
 Manage cases
 Assign lawyers to cases
 Record judgments
4. Admin User Testing
 Access admin panel
 View all complaints and cases
 Manage users, courts, lawyers
 System overview statistics
File Structure
cjrms/
  database/
    add_evidence_table.sql
    cjrms_schema.sql
    cjrms_seed.sql
  docs/
    LAWYER_DASHBOARD_DIAGNOSTIC.txt
    LAWYER_DASHBOARD_README.md
  public/
    uploads/
      .htaccess
      .gitkeep
    add_case.php
    index.php
    login.php
    manage_cases.php
    manage_complaints.php
    register_complaint.php
    ...
  scripts/
    button_debug.php
    debug_final.php
    diagnose_case_issue.php
  src/
    auth_guard.php
    db.php
    footer.php
    header.php
  tests/
    test_add_case.php
    test_pdf.php
  .gitignore
  .htaccess
  README.md
Database Schema
Main Tables
Users: System users with roles
Victim: Complaint victims
Criminal: Criminal records
Court: Court information
Lawyer: Lawyer records
Complaint: Complaint records
Cases: Case records (promoted complaints)
Judgement: Case judgments
Junction Tables
Complaint_Criminal_Link: Links criminals to complaints
Case_Criminal_Link: Links criminals to cases
Case_Lawyer_Link: Links lawyers to cases
Security Features
Password hashing using PHP's password_hash()
CSRF token protection
SQL injection prevention with prepared statements
XSS protection with output sanitization
Role-based access control
Session management
Troubleshooting
Common Issues
Database Connection Error

Ensure MySQL is running in XAMPP
Check database credentials in src/db.php
Verify database cjrms exists
Login Issues

Ensure seed data is imported
Check if sessions are working
Clear browser cache/cookies
Page Not Found

Verify files are in correct location
Check Apache is running
Ensure URL is correct
Permission Denied

Check file permissions
Ensure Apache has read access
Verify role-based access
Error Logs
Check XAMPP error logs in: C:\xampp\apache\logs\error.log
PHP errors are logged to the same location
Future Enhancements
Enhanced PDF generation with FPDF/TCPDF
Email notifications
Advanced search filters
Case timeline tracking
Document attachments
Audit trail logging
Mobile responsive design improvements
Support
For issues or questions:

Check the troubleshooting section
Verify all installation steps
Check error logs
Ensure all required services are running
