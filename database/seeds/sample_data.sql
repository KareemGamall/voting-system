-- Sample data for testing the voting system

-- Insert sample users (voters and admins)
INSERT INTO users (user_id, name, email, password, is_admin, is_voter) VALUES
('USR-ADMIN-001', 'Admin User', 'admin@voting.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, FALSE),
('USR-2024-1001', 'John Doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1002', 'Jane Smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1003', 'Michael Johnson', 'michael.j@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1004', 'Emily Davis', 'emily.d@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1005', 'David Wilson', 'david.w@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1006', 'Sarah Brown', 'sarah.b@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1007', 'James Taylor', 'james.t@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1008', 'Lisa Anderson', 'lisa.a@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1009', 'Robert Martinez', 'robert.m@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE),
('USR-2024-1010', 'Jennifer Garcia', 'jennifer.g@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE);

-- Note: Default password for all users is 'password' (hashed with bcrypt)

-- Insert sample elections
INSERT INTO elections (election_id, election_name, description, start_date, end_date, status) VALUES
('ELEC-2024-1001', '2024 Student Council Election', 'Annual student council election for academic year 2024-2025', '2024-12-10 08:00:00', '2024-12-10 18:00:00', 'upcoming'),
('ELEC-2024-1002', '2024 Class Representative Election', 'Election for class representatives', '2024-12-15 09:00:00', '2024-12-15 17:00:00', 'upcoming'),
('ELEC-2024-1003', '2024 Club Presidents Election', 'Election for various club presidents', '2024-12-20 08:00:00', '2024-12-20 16:00:00', 'upcoming');

-- Insert sample candidates for Student Council Election
INSERT INTO candidates (candidate_id, name, position, party, vote_count, election_id) VALUES
-- President candidates
('CAND-2024-1001', 'Alice Johnson', 'President', 'Progressive Party', 0, 1),
('CAND-2024-1002', 'Bob Williams', 'President', 'Unity Party', 0, 1),
('CAND-2024-1003', 'Carol Davis', 'President', 'Independent', 0, 1),

-- Vice President candidates
('CAND-2024-1004', 'Daniel Brown', 'Vice President', 'Progressive Party', 0, 1),
('CAND-2024-1005', 'Emma Wilson', 'Vice President', 'Unity Party', 0, 1),

-- Secretary candidates
('CAND-2024-1006', 'Frank Miller', 'Secretary', 'Progressive Party', 0, 1),
('CAND-2024-1007', 'Grace Taylor', 'Secretary', 'Unity Party', 0, 1),

-- Treasurer candidates
('CAND-2024-1008', 'Henry Anderson', 'Treasurer', 'Progressive Party', 0, 1),
('CAND-2024-1009', 'Isabel Thomas', 'Treasurer', 'Independent', 0, 1);

-- Insert sample candidates for Class Representative Election
INSERT INTO candidates (candidate_id, name, position, party, vote_count, election_id) VALUES
('CAND-2024-2001', 'Kevin Jackson', 'Class A Representative', 'Independent', 0, 2),
('CAND-2024-2002', 'Laura White', 'Class A Representative', 'Independent', 0, 2),
('CAND-2024-2003', 'Mark Harris', 'Class B Representative', 'Independent', 0, 2),
('CAND-2024-2004', 'Nancy Martin', 'Class B Representative', 'Independent', 0, 2);

-- Insert sample candidates for Club Presidents Election
INSERT INTO candidates (candidate_id, name, position, party, vote_count, election_id) VALUES
('CAND-2024-3001', 'Oliver Thompson', 'Drama Club President', 'Independent', 0, 3),
('CAND-2024-3002', 'Patricia Garcia', 'Drama Club President', 'Independent', 0, 3),
('CAND-2024-3003', 'Quinn Martinez', 'Sports Club President', 'Independent', 0, 3),
('CAND-2024-3004', 'Rachel Robinson', 'Sports Club President', 'Independent', 0, 3);

-- Note: You can add votes after the elections start using the application
-- Sample votes would be inserted through the application interface to maintain vote integrity
