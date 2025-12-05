# Voting System - Database Documentation

## Overview
This voting system uses a MySQL database with the following structure based on the Builder Design Pattern:

## Database Tables

### 1. Users Table
Stores all system users (voters and administrators).

**Columns:**
- `id` - Primary key
- `user_id` - Unique user identifier
- `name` - User's full name
- `email` - User's email (unique)
- `password` - Hashed password
- `is_admin` - Boolean flag for admin privileges
- `is_voter` - Boolean flag for voter privileges
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update

### 2. Elections Table
Stores election information.

**Columns:**
- `id` - Primary key
- `election_id` - Unique election identifier
- `election_name` - Name of the election
- `description` - Election description
- `start_date` - When the election starts
- `end_date` - When the election ends
- `status` - Election status (upcoming/active/completed/cancelled)
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update

### 3. Candidates Table
Stores candidate information.

**Columns:**
- `id` - Primary key
- `candidate_id` - Unique candidate identifier
- `name` - Candidate's name
- `position` - Position running for
- `party` - Political party or affiliation
- `photo` - Photo filename
- `vote_count` - Current vote count
- `election_id` - Foreign key to elections table
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update

### 4. Votes Table
Stores vote records.

**Columns:**
- `id` - Primary key
- `vote_id` - Unique vote identifier
- `election_id` - Foreign key to elections table
- `candidate_id` - Foreign key to candidates table
- `voter_id` - Foreign key to users table
- `vote_time` - When the vote was cast
- `created_at` - Timestamp of creation

**Constraints:**
- Unique constraint on (election_id, voter_id) - ensures one vote per election per voter

### 5. Results Table
Stores calculated election results.

**Columns:**
- `id` - Primary key
- `election_id` - Foreign key to elections table
- `candidate_id` - Foreign key to candidates table
- `vote_count` - Total votes for candidate
- `percentage` - Percentage of total votes
- `created_at` - Timestamp of creation
- `updated_at` - Timestamp of last update

## Models

### User Model (`app/models/User.php`)
**Methods:**
- `login($email, $password)` - Authenticate user
- `logout()` - End user session
- `register($userData)` - Register new user
- `isAdmin($userId)` - Check admin status
- `isVoter($userId)` - Check voter status
- `findByEmail($email)` - Find user by email
- `findByUserId($userId)` - Find user by user_id
- `updatePassword($id, $newPassword)` - Update user password
- `getVoters()` - Get all voters
- `getAdmins()` - Get all admins

### Election Model (`app/models/Election.php`)
**Methods:**
- `createElection($electionData)` - Create new election
- `closeElection($id)` - Close/end election
- `getActiveElections()` - Get currently active elections
- `getUpcomingElections()` - Get future elections
- `getCompletedElections()` - Get past elections
- `getElectionWithCandidates($id)` - Get election with all candidates
- `isActive($id)` - Check if election is active
- `startElection($id)` - Start an election
- `findByElectionId($electionId)` - Find election by election_id
- `updateElectionStatuses()` - Auto-update election statuses based on dates
- `getElectionStats($id)` - Get election statistics

### Candidate Model (`app/models/Candidate.php`)
**Methods:**
- `addCandidate($candidateData)` - Add new candidate
- `getCandidatesByElection($electionId)` - Get all candidates in an election
- `getCandidatesByPosition($electionId, $position)` - Get candidates for specific position
- `incrementVoteCount($id)` - Increment candidate's vote count
- `getCandidateWithVotes($id)` - Get candidate with vote count
- `getTopCandidates($electionId, $limit)` - Get top candidates by votes
- `findByCandidateId($candidateId)` - Find candidate by candidate_id
- `updatePhoto($id, $photoPath)` - Update candidate photo
- `deletePhoto($id)` - Delete candidate photo
- `getPositionsByElection($electionId)` - Get all positions in election

### Vote Model (`app/models/Vote.php`)
**Methods:**
- `castVote($electionId, $candidateId, $voterId)` - Cast a vote
- `hasVoted($electionId, $voterId)` - Check if user has voted
- `getVotesByElection($electionId)` - Get all votes in an election
- `getVotesByCandidate($candidateId)` - Get votes for a candidate
- `getVotesByVoter($voterId)` - Get voter's voting history
- `getVoteDetails($id)` - Get detailed vote information
- `countVotesByElection($electionId)` - Count total votes in election
- `countVotesByCandidate($candidateId)` - Count votes for candidate
- `getVoterElectionVote($electionId, $voterId)` - Get specific voter's vote
- `getElectionVotingStats($electionId)` - Get voting statistics
- `getVotesByHour($electionId)` - Get vote distribution by hour
- `verifyVote($voteId)` - Verify vote integrity

### Result Model (`app/models/Result.php`)
**Methods:**
- `calculateResults($electionId)` - Calculate and generate results
- `getElectionResults($electionId)` - Get results for election
- `getResultsByPosition($electionId)` - Get results grouped by position
- `getWinnerByPosition($electionId, $position)` - Get winner for a position
- `getAllWinners($electionId)` - Get all winners in an election
- `generateReport($electionId)` - Generate comprehensive election report
- `getCandidateResult($electionId, $candidateId)` - Get specific candidate result
- `updateCandidateResult($electionId, $candidateId)` - Update candidate result

## Installation & Setup

### 1. Configure Database
Edit `config/database.php` with your MySQL credentials:
```php
'host' => 'localhost',
'database' => 'voting_system',
'username' => 'root',
'password' => '',
```

### 2. Run Migrations

**Option A - Using PHP Script (Recommended):**
```bash
php database/migrate.php
```

**Option B - Using MySQL Command Line:**
```bash
mysql -u root -p < database/voting_system.sql
```

**Option C - Manual Import via phpMyAdmin:**
1. Open phpMyAdmin
2. Create new database named `voting_system`
3. Import `database/voting_system.sql`

### 3. Seed Sample Data (Optional)
If you didn't use the migrate.php script, import sample data:
```bash
mysql -u root -p voting_system < database/seeds/sample_data.sql
```

### 4. Test Credentials
After seeding sample data, you can login with:
- **Admin:**
  - Email: `admin@voting.com`
  - Password: `password`

- **Voter:**
  - Email: `john.doe@email.com`
  - Password: `password`

## Design Pattern Implementation

This system implements the **Builder Design Pattern** as shown in the class diagram:

### Directors
- **ElectionDirector** - Orchestrates election creation
- **Admin** - Manages system administration

### Builders
- **ElectionBuilder** - Builds election objects
- **ConcreteElectionBuilder** - Concrete implementation of election building

### Services
- **VotingEngine** - Handles voting logic
- **RegistrationService** - Manages user registration
- **AuthorizationService** - Handles authentication and authorization

### Entities
- **User** - System users
- **Voter** - Voting users
- **Election** - Election data
- **Candidate** - Candidate information
- **Vote** - Vote records
- **Result** - Election results

## Entity Relationships

```
User (1) ----< (Many) Vote
Election (1) ----< (Many) Candidate
Election (1) ----< (Many) Vote
Election (1) ----< (Many) Result
Candidate (1) ----< (Many) Vote
Candidate (1) ----< (Many) Result
```

## Security Features

1. **Password Hashing** - All passwords are hashed using bcrypt
2. **Prepared Statements** - All database queries use PDO prepared statements
3. **Unique Vote Constraint** - Database enforces one vote per user per election
4. **Foreign Key Constraints** - Maintains referential integrity
5. **Index Optimization** - Strategic indexes for performance

## File Structure

```
database/
├── migrations/                      # SQL migration files
│   ├── 001_create_users_table.sql
│   ├── 002_create_elections_table.sql
│   ├── 003_create_candidates_table.sql
│   ├── 004_create_votes_table.sql
│   └── 005_create_results_table.sql
├── seeds/                           # Sample data
│   └── sample_data.sql
├── voting_system.sql                # Complete database schema
├── migrate.php                      # Migration runner script
└── README.md                        # This file

app/
├── models/                          # Model classes
│   ├── User.php
│   ├── Election.php
│   ├── Candidate.php
│   ├── Vote.php
│   └── Result.php
└── core/                            # Core framework files
    ├── Database.php                 # Database connection (Singleton)
    └── Model.php                    # Base model class

config/
└── database.php                     # Database configuration
```

## Usage Examples

### Creating an Election
```php
require_once 'app/models/Election.php';
$election = new Election();

$electionData = [
    'election_name' => '2024 Student Council Election',
    'description' => 'Annual election',
    'start_date' => '2024-12-10 08:00:00',
    'end_date' => '2024-12-10 18:00:00'
];

$election->createElection($electionData);
```

### Adding a Candidate
```php
require_once 'app/models/Candidate.php';
$candidate = new Candidate();

$candidateData = [
    'name' => 'John Doe',
    'position' => 'President',
    'party' => 'Progressive Party',
    'election_id' => 1
];

$candidate->addCandidate($candidateData);
```

### Casting a Vote
```php
require_once 'app/models/Vote.php';
$vote = new Vote();

$vote->castVote($electionId, $candidateId, $voterId);
```

### Generating Results
```php
require_once 'app/models/Result.php';
$result = new Result();

// Calculate results
$result->calculateResults($electionId);

// Get report
$report = $result->generateReport($electionId);
```

## Notes

- All IDs are auto-generated with unique prefixes (USR-, ELEC-, CAND-, VOTE-)
- The system uses the Singleton pattern for database connections
- All models extend a base Model class with common CRUD operations
- Timestamps are automatically managed by MySQL
- Vote counting is handled both in real-time and through result calculations
