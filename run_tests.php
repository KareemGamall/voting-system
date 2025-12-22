<?php
/**
 * Simple Manual Test Runner
 * Run without PHPUnit/Composer installation
 */

// Setup
define('BASE_PATH', __DIR__);
define('TESTING', true);

// Load core
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/helpers/functions.php';

// Test database configuration
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'voting_system'; // Using main DB for now
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

echo "\n";
echo "╔══════════════════════════════════════════════╗\n";
echo "║    VOTING SYSTEM - MANUAL TEST RUNNER       ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

// Test Counter
$passed = 0;
$failed = 0;
$errors = [];

// Helper function
function test($description, $callback) {
    global $passed, $failed, $errors;
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        $result = $callback();
        
        $db->rollBack();
        
        if ($result === true) {
            $passed++;
            echo "✓ " . $description . "\n";
        } else {
            $failed++;
            echo "✗ " . $description . "\n";
            $errors[] = $description . ": Test returned false";
        }
    } catch (Exception $e) {
        $failed++;
        echo "✗ " . $description . " - ERROR\n";
        $errors[] = $description . ": " . $e->getMessage();
        try {
            $db->rollBack();
        } catch (Exception $rollbackEx) {}
    }
}

echo "Running tests...\n\n";
echo "═══════════════════════════════════════════════\n";
echo "ELECTION TESTS\n";
echo "═══════════════════════════════════════════════\n";

require_once BASE_PATH . '/app/models/Election.php';
$electionModel = new Election();

test("Can create an election", function() use ($electionModel) {
    $data = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Test Election ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
        'status' => 'upcoming'
    ];
    
    return $electionModel->createElection($data);
});

test("Can retrieve active elections", function() use ($electionModel) {
    $active = $electionModel->getActiveElections();
    return is_array($active);
});

test("Can check if election is active", function() use ($electionModel) {
    $data = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Active Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'status' => 'active'
    ];
    
    $electionModel->createElection($data);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $data['election_name']]);
    
    return $electionModel->isActive($election['id']);
});

echo "\n═══════════════════════════════════════════════\n";
echo "VOTING TESTS\n";
echo "═══════════════════════════════════════════════\n";

require_once BASE_PATH . '/app/models/Candidate.php';
require_once BASE_PATH . '/app/models/Vote.php';
require_once BASE_PATH . '/app/models/User.php';

$voteModel = new Vote();
$candidateModel = new Candidate();
$userModel = new User();

test("Can cast a vote", function() use ($electionModel, $candidateModel, $voteModel, $userModel) {
    // Create election
    $electionData = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Vote Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'status' => 'active'
    ];
    $electionModel->createElection($electionData);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $electionData['election_name']]);
    
    // Create candidate
    $candidateData = [
        'candidate_id' => 'CAND-TEST-' . uniqid(),
        'election_id' => $election['id'],
        'name' => 'Test Candidate ' . uniqid(),
        'position' => 'President',
        'party' => 'Test Party'
    ];
    $candidateModel->create($candidateData);
    $candidate = $candidateModel->findWhere('candidate_id = :cid', ['cid' => $candidateData['candidate_id']]);
    
    // Create voter
    $userData = [
        'user_id' => 'USR-TEST-' . uniqid(),
        'name' => 'Test Voter',
        'email' => 'test' . uniqid() . '@test.com',
        'password' => password_hash('Test123!', PASSWORD_BCRYPT),
        'is_voter' => 1
    ];
    $userModel->register($userData);
    $user = $userModel->findByEmail($userData['email']);
    
    // Cast vote
    return $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
});

test("Prevents duplicate votes", function() use ($electionModel, $candidateModel, $voteModel, $userModel) {
    // Setup
    $electionData = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Duplicate Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'status' => 'active'
    ];
    $electionModel->createElection($electionData);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $electionData['election_name']]);
    
    $candidateData = [
        'election_id' => $election['id'],
        'name' => 'Test Candidate ' . uniqid(),
        'position' => 'President',
        'party' => 'Test Party'
    ];
    $candidateModel->create($candidateData);
    $candidate = $candidateModel->findWhere('name = :name', ['name' => $candidateData['name']]);
    
    $userData = [
        'user_id' => 'USR-TEST-' . uniqid(),
        'name' => 'Test Voter',
        'email' => 'test' . uniqid() . '@test.com',
        'password' => password_hash('Test123!', PASSWORD_BCRYPT),
        'is_voter' => 1
    ];
    $userModel->register($userData);
    $user = $userModel->findByEmail($userData['email']);
    
    // First vote should succeed
    $firstVote = $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
    
    // Second vote should fail
    $secondVote = $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
    
    return $firstVote === true && $secondVote === false;
});

test("Counts votes correctly", function() use ($electionModel, $candidateModel, $voteModel, $userModel) {
    // Setup
    $electionData = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Count Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'status' => 'active'
    ];
    $electionModel->createElection($electionData);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $electionData['election_name']]);
    
    $candidateData = [
        'election_id' => $election['id'],
        'name' => 'Test Candidate ' . uniqid(),
        'position' => 'President',
        'party' => 'Test Party'
    ];
    $candidateModel->create($candidateData);
    $candidate = $candidateModel->findWhere('name = :name', ['name' => $candidateData['name']]);
    
    // Cast 3 votes
    for ($i = 0; $i < 3; $i++) {
        $userData = [
            'user_id' => 'USR-TEST-' . uniqid(),
            'name' => 'Voter ' . $i,
            'email' => 'voter' . uniqid() . '@test.com',
            'password' => password_hash('Test123!', PASSWORD_BCRYPT),
            'is_voter' => 1
        ];
        $userModel->register($userData);
        $user = $userModel->findByEmail($userData['email']);
        $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
    }
    
    $count = $voteModel->countVotesByCandidate($candidate['id']);
    return $count === 3;
});

echo "\n═══════════════════════════════════════════════\n";
echo "RESULT & TIE TESTS\n";
echo "═══════════════════════════════════════════════\n";

require_once BASE_PATH . '/app/models/Result.php';
$resultModel = new Result();

test("Calculates results correctly", function() use ($electionModel, $candidateModel, $voteModel, $userModel, $resultModel) {
    // Setup
    $electionData = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Result Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'end_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'status' => 'completed'
    ];
    $electionModel->createElection($electionData);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $electionData['election_name']]);
    
    // Create 2 candidates
    for ($i = 0; $i < 2; $i++) {
        $candidateModel->create([
            'candidate_id' => 'CAND-TEST-' . uniqid(),
            'election_id' => $election['id'],
            'name' => 'Candidate ' . $i,
            'position' => 'President',
            'party' => 'Party ' . $i
        ]);
    }
    
    $candidates = $candidateModel->getCandidatesByElection($election['id']);
    
    // Cast votes: 5 for first, 3 for second
    $voteCounts = [5, 3];
    foreach ($candidates as $index => $candidate) {
        for ($i = 0; $i < $voteCounts[$index]; $i++) {
            $userData = [
                'user_id' => 'USR-TEST-' . uniqid(),
                'name' => 'Voter',
                'email' => 'voter' . uniqid() . '@test.com',
                'password' => password_hash('Test123!', PASSWORD_BCRYPT),
                'is_voter' => 1
            ];
            $userModel->register($userData);
            $user = $userModel->findByEmail($userData['email']);
            $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        }
    }
    
    // Calculate results
    $resultModel->calculateResults($election['id']);
    $results = $resultModel->getElectionResults($election['id']);
    
    return count($results) === 2 && $results[0]['vote_count'] == 5;
});

test("Detects ties correctly", function() use ($electionModel, $candidateModel, $voteModel, $userModel, $resultModel) {
    // Setup
    $electionData = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'Tie Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'end_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'status' => 'completed'
    ];
    $electionModel->createElection($electionData);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $electionData['election_name']]);
    
    // Create 2 candidates
    for ($i = 0; $i < 2; $i++) {
        $candidateModel->create([
            'election_id' => $election['id'],
            'name' => 'Tied Candidate ' . $i,
            'position' => 'President',
            'party' => 'Party ' . $i
        ]);
    }
    
    $candidates = $candidateModel->getCandidatesByElection($election['id']);
    
    // Cast equal votes: 3 each
    foreach ($candidates as $candidate) {
        for ($i = 0; $i < 3; $i++) {
            $userData = [
                'user_id' => 'USR-TEST-' . uniqid(),
                'name' => 'Voter',
                'email' => 'voter' . uniqid() . '@test.com',
                'password' => password_hash('Test123!', PASSWORD_BCRYPT),
                'is_voter' => 1
            ];
            $userModel->register($userData);
            $user = $userModel->findByEmail($userData['email']);
            $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        }
    }
    
    // Calculate results
    $resultModel->calculateResults($election['id']);
    $results = $resultModel->getElectionResults($election['id']);
    
    // Both should be tied
    return $results[0]['is_tied'] === true && $results[1]['is_tied'] === true;
});

test("Returns null winner when tied", function() use ($electionModel, $candidateModel, $voteModel, $userModel, $resultModel) {
    // Setup
    $electionData = [
        'election_id' => 'ELEC-TEST-' . uniqid(),
        'election_name' => 'No Winner Test ' . uniqid(),
        'description' => 'Test',
        'start_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'end_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'status' => 'completed'
    ];
    $electionModel->createElection($electionData);
    $election = $electionModel->findWhere('election_name = :name', ['name' => $electionData['election_name']]);
    
    // Create 2 candidates
    for ($i = 0; $i < 2; $i++) {
        $candidateModel->create([
            'election_id' => $election['id'],
            'name' => 'Tied Candidate ' . $i,
            'position' => 'President',
            'party' => 'Party ' . $i
        ]);
    }
    
    $candidates = $candidateModel->getCandidatesByElection($election['id']);
    
    // Cast equal votes
    foreach ($candidates as $candidate) {
        for ($i = 0; $i < 3; $i++) {
            $userData = [
                'user_id' => 'USR-TEST-' . uniqid(),
                'name' => 'Voter',
                'email' => 'voter' . uniqid() . '@test.com',
                'password' => password_hash('Test123!', PASSWORD_BCRYPT),
                'is_voter' => 1
            ];
            $userModel->register($userData);
            $user = $userModel->findByEmail($userData['email']);
            $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        }
    }
    
    // Calculate results
    $resultModel->calculateResults($election['id']);
    $winner = $resultModel->getWinnerByPosition($election['id'], 'President');
    
    return $winner === null;
});

echo "\n═══════════════════════════════════════════════\n";
echo "AUTHENTICATION TESTS\n";
echo "═══════════════════════════════════════════════\n";

test("User can register", function() use ($userModel) {
    $userData = [
        'user_id' => 'USR-TEST-' . uniqid(),
        'name' => 'New User',
        'email' => 'newuser' . uniqid() . '@test.com',
        'password' => password_hash('Test123!', PASSWORD_BCRYPT),
        'is_voter' => 1
    ];
    
    return $userModel->register($userData);
});

test("User can login with correct credentials", function() use ($userModel) {
    $password = 'SecurePass123!';
    $email = 'logintest' . uniqid() . '@test.com';
    
    $userData = [
        'user_id' => 'USR-TEST-' . uniqid(),
        'name' => 'Login Test',
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'is_voter' => 1
    ];
    
    $userModel->register($userData);
    $user = $userModel->login($email, $password);
    
    return $user !== false && $user['email'] === $email;
});

test("User cannot login with wrong password", function() use ($userModel) {
    $email = 'wrongpass' . uniqid() . '@test.com';
    
    $userData = [
        'user_id' => 'USR-TEST-' . uniqid(),
        'name' => 'Wrong Pass Test',
        'email' => $email,
        'password' => password_hash('CorrectPass123!', PASSWORD_BCRYPT),
        'is_voter' => 1
    ];
    
    $userModel->register($userData);
    $user = $userModel->login($email, 'WrongPassword');
    
    return $user === false;
});

// Summary
echo "\n═══════════════════════════════════════════════\n";
echo "TEST SUMMARY\n";
echo "═══════════════════════════════════════════════\n\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "Total Tests: {$total}\n";
echo "Passed: {$passed} ✓\n";
echo "Failed: {$failed} ✗\n";
echo "Success Rate: {$percentage}%\n\n";

if ($failed > 0) {
    echo "═══════════════════════════════════════════════\n";
    echo "ERRORS\n";
    echo "═══════════════════════════════════════════════\n\n";
    foreach ($errors as $error) {
        echo "• " . $error . "\n";
    }
    echo "\n";
}

if ($failed === 0) {
    echo "╔══════════════════════════════════════════════╗\n";
    echo "║          🎉 ALL TESTS PASSED! 🎉            ║\n";
    echo "╚══════════════════════════════════════════════╝\n";
} else {
    echo "Some tests failed. Please review the errors above.\n";
}

echo "\n";
