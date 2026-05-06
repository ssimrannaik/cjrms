-- Criminal Justice Records Management System (CJRMS) Seed Data
-- This file populates the database with sample data for testing
-- Make sure to run cjrms_schema.sql first

USE cjrms;

-- Insert Users (passwords are hashed versions of 'password123')
INSERT INTO Users (username, password_hash, role, full_name) VALUES
('police_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Police', 'Officer John Smith'),
('court_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Court', 'Clerk Sarah Johnson'),
('admin_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Administrator Mike Wilson'),
('lawyer_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lawyer', 'Attorney Lisa Brown');

-- Insert Courts
INSERT INTO Court (name, location, type) VALUES
('District Court Central', '123 Justice Street, Downtown', 'District Court'),
('High Court Branch A', '456 Legal Avenue, Uptown', 'High Court'),
('Magistrate Court East', '789 Court Road, East Side', 'Magistrate Court');

-- Insert Lawyers
INSERT INTO Lawyer (name, type, contact_info) VALUES
('Robert Davis', 'Prosecution', 'Phone: 555-0101, Email: r.davis@prosecution.gov'),
('Emily Wilson', 'Defense', 'Phone: 555-0102, Email: e.wilson@defense.org'),
('Michael Chen', 'Private', 'Phone: 555-0103, Email: m.chen@privatelaw.com'),
('Jennifer Taylor', 'Prosecution', 'Phone: 555-0104, Email: j.taylor@prosecution.gov'),
('David Martinez', 'Defense', 'Phone: 555-0105, Email: d.martinez@defense.org');

-- Insert Criminals
INSERT INTO Criminal (name, age, identification_detail) VALUES
('James Anderson', 28, 'ID: DL123456789, Height: 5\'10", Weight: 180lbs, Tattoo on left arm'),
('Maria Rodriguez', 32, 'ID: DL987654321, Height: 5\'6", Weight: 140lbs, Scar on right cheek'),
('Thomas Johnson', 45, 'ID: DL456789123, Height: 6\'2", Weight: 200lbs, Missing left index finger'),
('Sarah Williams', 29, 'ID: DL321654987, Height: 5\'4", Weight: 130lbs, Blonde hair, blue eyes');

-- Insert Victims
INSERT INTO Victim (name, contact_info) VALUES
('Alice Cooper', 'Phone: 555-1001, Address: 123 Elm Street, Email: alice.cooper@email.com'),
('Bob Thompson', 'Phone: 555-1002, Address: 456 Oak Avenue, Email: bob.thompson@email.com'),
('Carol Davis', 'Phone: 555-1003, Address: 789 Pine Road, Email: carol.davis@email.com'),
('Daniel Brown', 'Phone: 555-1004, Address: 321 Maple Drive, Email: daniel.brown@email.com');

-- Insert Complaints with different statuses
INSERT INTO Complaint (description, date, crime_type, victim_id, complaint_status, created_by) VALUES
('Theft of personal belongings from residence. Missing items include laptop, jewelry, and cash totaling $2,500.', '2024-01-15', 'Theft', 1, 'New', 1),
('Physical assault during a dispute at local bar. Victim suffered minor injuries requiring medical attention.', '2024-01-20', 'Assault', 2, 'Under Investigation', 1),
('Fraudulent use of credit card resulting in unauthorized charges of $1,200.', '2024-01-25', 'Fraud', 3, 'Charge-Sheeted', 1),
('Vandalism of private property including broken windows and graffiti on building walls.', '2024-02-01', 'Vandalism', 4, 'Charge-Sheeted', 1),
('Domestic violence incident with threats and property damage.', '2024-02-05', 'Domestic Violence', 1, 'Withdrawn', 1);

-- Link criminals to complaints
INSERT INTO Complaint_Criminal_Link (complaint_id, criminal_id) VALUES
(1, 1), -- James Anderson linked to theft complaint
(2, 2), -- Maria Rodriguez linked to assault complaint
(3, 3), -- Thomas Johnson linked to fraud complaint
(4, 4), -- Sarah Williams linked to vandalism complaint
(3, 1); -- James Anderson also linked to fraud complaint (accomplice)

-- Insert a promoted case
INSERT INTO Cases (complaint_id, start_date, case_type, case_status, court_id) VALUES
(3, '2024-02-10', 'Criminal Case - Fraud', 'Under Trial', 1);

-- Update the complaint status to 'Promoted to Case'
UPDATE Complaint SET complaint_status = 'Promoted to Case' WHERE complaint_id = 3;

-- Link criminals to the case
INSERT INTO Case_Criminal_Link (case_id, criminal_id) VALUES
(1, 3), -- Thomas Johnson linked to case
(1, 1); -- James Anderson linked to case

-- Assign lawyers to the case
INSERT INTO Case_Lawyer_Link (case_id, lawyer_id, role) VALUES
(1, 1, 'Prosecution'), -- Robert Davis as prosecutor
(1, 2, 'Defense');     -- Emily Wilson as defense attorney

-- Insert a judgment for the case
INSERT INTO Judgement (case_id, date, verdict, punishment) VALUES
(1, '2024-03-01', 'Preliminary Hearing Completed', 'Case scheduled for trial on 2024-04-15. Defendant released on bail of $5,000.');

-- Display inserted data counts
SELECT 'Users' as Table_Name, COUNT(*) as Record_Count FROM Users
UNION ALL
SELECT 'Courts', COUNT(*) FROM Court
UNION ALL
SELECT 'Lawyers', COUNT(*) FROM Lawyer
UNION ALL
SELECT 'Criminals', COUNT(*) FROM Criminal
UNION ALL
SELECT 'Victims', COUNT(*) FROM Victim
UNION ALL
SELECT 'Complaints', COUNT(*) FROM Complaint
UNION ALL
SELECT 'Cases', COUNT(*) FROM Cases
UNION ALL
SELECT 'Judgements', COUNT(*) FROM Judgement
UNION ALL
SELECT 'Complaint_Criminal_Links', COUNT(*) FROM Complaint_Criminal_Link
UNION ALL
SELECT 'Case_Criminal_Links', COUNT(*) FROM Case_Criminal_Link
UNION ALL
SELECT 'Case_Lawyer_Links', COUNT(*) FROM Case_Lawyer_Link;
