<?php
// This endpoint has been disabled and is no longer available.
http_response_code(404);
header('Content-Type: text/plain');
echo 'Not Found';
exit;

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/models/Election.php';
require_once BASE_PATH . '/app/models/Candidate.php';
require_once BASE_PATH . '/app/models/Vote.php';
require_once BASE_PATH . '/app/models/User.php';

header('Content-Type: application/json');

$electionId = isset($_GET['election_id']) ? (int)$_GET['election_id'] : 0;
$electionName = isset($_GET['election_name']) ? trim((string)$_GET['election_name']) : '';
$votesTarget = isset($_GET['votes']) ? (int)$_GET['votes'] : 500; // total vote operations to attempt
$delayMs = isset($_GET['delay_ms']) ? (int)$_GET['delay_ms'] : 10; // delay between votes
$clear = isset($_GET['clear']) ? (int)$_GET['clear'] : 0; // 1 to clear existing votes for election

// Resolve election by name if id not provided
$electionModel = new Election();
if ($electionId <= 0 && $electionName !== '') {
  // Try exact match first
  $found = $electionModel->findWhere('election_name = :name', ['name' => $electionName]);
  if ($found && isset($found['id'])) {
    $electionId = (int)$found['id'];
  } else {
    // Fallback: case-insensitive contains search
    $all = $electionModel->getAll();
    foreach ($all as $e) {
      $name = strtolower($e['election_name'] ?? '');
      if ($name !== '' && strpos($name, strtolower($electionName)) !== false) {
        $electionId = (int)($e['id'] ?? 0);
        break;
      }
    }
  }
}

if ($electionId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Missing or invalid election identifier (provide election_id or election_name)']);
  exit;
}

$candidateModel = new Candidate();
$voteModel = new Vote();
$userModel = new User();

$election = $electionModel->find($electionId);
if (!$election) {
  echo json_encode(['success' => false, 'message' => 'Election not found']);
  exit;
}

$candidates = $candidateModel->getCandidatesByElection($electionId);
if (!$candidates || count($candidates) === 0) {
  echo json_encode(['success' => false, 'message' => 'No candidates found for election']);
  exit;
}

// Optionally clear existing votes for this election for a clean run
if ($clear === 1) {
  try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('DELETE FROM votes WHERE election_id = :eid');
    $stmt->execute([':eid' => $electionId]);
    // Reset candidate vote_count to match cleared votes
    $stmt2 = $db->prepare('UPDATE candidates SET vote_count = 0 WHERE election_id = :eid');
    $stmt2->execute([':eid' => $electionId]);
  } catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Failed to clear votes: ' . $t->getMessage()]);
    exit;
  }
}

// Ensure we have enough voters; use existing voters, create stress voters if needed
$voters = $userModel->getVoters();
$existingCount = is_array($voters) ? count($voters) : 0;
$needed = max(0, min($votesTarget, $votesTarget - $existingCount));

if ($needed > 0) {
  for ($i = 0; $i < $needed; $i++) {
    $email = 'stress_' . time() . '_' . $i . '@local.test';
    $uid = 'USR-' . str_replace('.', '', uniqid('', true));
    $userModel->register([
      'user_id' => $uid,
      'name' => 'Stress Voter ' . $i,
      'email' => $email,
      'password' => 'TestPass123!',
      'is_admin' => 0,
      'is_voter' => 1
    ]);
  }
  // Refresh voters list
  $voters = $userModel->getVoters();
}

if (!$voters || count($voters) === 0) {
  echo json_encode(['success' => false, 'message' => 'No voters available to simulate votes']);
  exit;
}

$voteAttempts = 0;
$voteSuccess = 0;
$voteDuplicates = 0;
$voteErrors = 0;
$usedPairs = []; // track voterId-candidateId pairs to avoid unique constraint hits

$rand = function($max) { return random_int(0, max(0, $max - 1)); };

$start = microtime(true);
for ($i = 0; $i < $votesTarget; $i++) {
  $voteAttempts++;
  $voterIdx = $rand(count($voters));
  $candIdx = $rand(count($candidates));
  $voterId = (int)($voters[$voterIdx]['id'] ?? 0);
  $candidateId = (int)($candidates[$candIdx]['id'] ?? 0);
  if ($voterId <= 0 || $candidateId <= 0) { $voteErrors++; continue; }

  $pairKey = $voterId . ':' . $candidateId;
  if (isset($usedPairs[$pairKey])) { $voteDuplicates++; continue; }

  try {
    $ok = $voteModel->castVote($electionId, $candidateId, $voterId);
    if ($ok) {
      $usedPairs[$pairKey] = true;
      $voteSuccess++;
    } else {
      $voteErrors++;
    }
  } catch (Throwable $t) {
    // Unique constraint (duplicate for same candidate) or other DB errors
    $voteErrors++;
  }

  if ($delayMs > 0) {
    usleep($delayMs * 1000);
  }
}
$elapsed = round((microtime(true) - $start), 3);

// Aggregate monitor-style summary
$totalVotes = $voteModel->countVotesByElection($electionId);
$stats = $voteModel->getElectionVotingStats($electionId);

// Build per-candidate counts
$voteCounts = [];
foreach ($candidates as $c) {
  $voteCounts[] = [
    'candidate_id' => $c['id'],
    'name' => $c['name'] ?? 'Candidate',
    'votes' => $voteModel->countVotesByCandidate($c['id'])
  ];
}

echo json_encode([
  'success' => true,
  'resolved_election_id' => $electionId,
  'election_name' => $electionName,
  'election_id' => $electionId,
  'attempted' => $voteAttempts,
  'success' => $voteSuccess,
  'duplicates_skipped' => $voteDuplicates,
  'errors' => $voteErrors,
  'elapsed_sec' => $elapsed,
  'summary' => [
    'total_votes' => $totalVotes,
    'stats' => $stats,
    'per_candidate' => $voteCounts
  ]
]);
