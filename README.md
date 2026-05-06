# Criminal Justice Records Management System (CJRMS)

A comprehensive web-based application for managing criminal justice records, built with PHP, MySQL, and designed for XAMPP deployment.

## Features

- **Role-based Authentication**: Police, Court Clerk, Admin, and Lawyer roles
- **Complaint Management**: Register, track, and manage complaints
- **Case Management**: Promote complaints to cases, assign lawyers, record judgments
- **Criminal Profiles**: Link criminals to complaints and cases
- **PDF Report Generation**: Generate FIR reports
- **Search Functionality**: Search across complaints, cases, and criminals
- **Admin Panel**: Manage users, courts, and lawyers

## System Requirements

- XAMPP (Apache + MySQL + PHP 8.x)
- Web browser (Chrome, Firefox, Safari, Edge)
- At least 100MB free disk space

## Installation Instructions

### Step 1: Setup XAMPP
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start XAMPP Control Panel
3. Start **Apache** and **MySQL** services

### Step 2: Database Setup
1. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
2. Create a new database named `cjrms`
3. Import the database schema:
   - Click on the `cjrms` database
   - Go to **Import** tab
   - Choose file: `cjrms_schema.sql`
   - Click **Go**
4. Import the seed data:
   - Go to **Import** tab again
   - Choose file: `cjrms_seed.sql`
   - Click **Go**

### Step 3: Deploy Application
1. Copy the entire `cjrms` folder to `C:\xampp\htdocs\`
2. The final path should be: `C:\xampp\htdocs\cjrms\`

### Step 4: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/cjrms/login.php`
3. Use the test credentials below to login

## Test Credentials

| Role | Username | Password | Description |
|------|----------|----------|-------------|
| Police | police_user | password123 | Can register and manage complaints |
| Court | court_user | password123 | Can promote complaints to cases |
| Admin | admin_user | password123 | Full system access |
| Lawyer | lawyer_user | password123 | Can view assigned cases |

## Testing Checklist

### 1. Authentication Testing
- [ ] Login with each role
- [ ] Verify role-based navigation
- [ ] Test logout functionality
- [ ] Verify access control (try accessing restricted pages)

### 2. Police User Testing
- [ ] Register a new complaint
- [ ] View complaint list
- [ ] Update complaint status
- [ ] Generate PDF report
- [ ] Search for records

### 3. Court User Testing
- [ ] View charge-sheeted complaints
- [ ] Promote complaint to case
- [ ] Manage cases
- [ ] Assign lawyers to cases
- [ ] Record judgments

### 4. Admin User Testing
- [ ] Access admin panel
- [ ] View all complaints and cases
- [ ] Manage users, courts, lawyers
- [ ] System overview statistics

## File Structure

```
cjrms/
├── cjrms_schema.sql          # Database schema
├── cjrms_seed.sql           # Sample data
├── db.php                   # Database connection
├── auth_guard.php           # Authentication functions
├── header.php               # Common header
├── footer.php               # Common footer
├── login.php                # Login page
├── handle_login.php         # Login handler
├── logout.php               # Logout handler
├── index.php                # Dashboard
├── register_complaint.php   # Complaint registration
├── handle_register_complaint.php # Complaint handler
├── manage_complaints.php    # Complaint management
├── handle_update_complaint_status.php # Status updates
├── add_case.php            # Case creation
├── generate_report.php     # PDF generation
└── README.md               # This file
```

## Database Schema

### Main Tables
- **Users**: System users with roles
- **Victim**: Complaint victims
- **Criminal**: Criminal records
- **Court**: Court information
- **Lawyer**: Lawyer records
- **Complaint**: Complaint records
- **Cases**: Case records (promoted complaints)
- **Judgement**: Case judgments

### Junction Tables
- **Complaint_Criminal_Link**: Links criminals to complaints
- **Case_Criminal_Link**: Links criminals to cases
- **Case_Lawyer_Link**: Links lawyers to cases

## Security Features

- Password hashing using PHP's `password_hash()`
- CSRF token protection
- SQL injection prevention with prepared statements
- XSS protection with output sanitization
- Role-based access control
- Session management

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `db.php`
   - Verify database `cjrms` exists

2. **Login Issues**
   - Ensure seed data is imported
   - Check if sessions are working
   - Clear browser cache/cookies

3. **Page Not Found**
   - Verify files are in correct location
   - Check Apache is running
   - Ensure URL is correct

4. **Permission Denied**
   - Check file permissions
   - Ensure Apache has read access
   - Verify role-based access

### Error Logs
- Check XAMPP error logs in: `C:\xampp\apache\logs\error.log`
- PHP errors are logged to the same location

## Future Enhancements

- Enhanced PDF generation with FPDF/TCPDF
- Email notifications
- Advanced search filters
- Case timeline tracking
- Document attachments
- Audit trail logging
- Mobile responsive design improvements

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all installation steps
3. Check error logs
4. Ensure all required services are running

## License

This project is created for educational purposes as a college project demonstration.

---

**Note**: This is a demonstration system. For production use, additional security measures, error handling, and testing would be required.
