<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Election.php';
require_once __DIR__ . '/../models/Candidate.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/User.php';

/**
 * VoterController - Handles all voter-related actions
 * Manages voter dashboard, ballot display, and vote casting
 */
class VoterController extends Controller {
    
    private $electionModel;
    private $candidateModel;
    private $voteModel;
    private $userModel;
    
    public function __construct() {
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->voteModel = new Vote();
        $this->userModel = new User();
    }
    
    /**
     * Voter Dashboard - Shows available elections
     */
    public function dashboard() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            $this->setFlash('error', 'Access denied. Voter login required.');
            $this->redirect('/auth/login');
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Get active elections (statuses are auto-updated)
        $activeElections = $this->electionModel->getActiveElections();
        
        // Get upcoming elections
        $upcomingElections = $this->electionModel->getUpcomingElections();
        
        // Get completed elections
        $completedElections = $this->electionModel->getCompletedElections();
        
        // Get results for each completed election
        require_once __DIR__ . '/../models/Result.php';
        $resultModel = new Result();
        foreach ($completedElections as &$election) {
            $election['results'] = $resultModel->getElectionResults($election['id']);
            $election['winners'] = $resultModel->getAllWinners($election['id']);
        }
        
        // Get elections where user has already voted
        $votedElections = $this->voteModel->getVotesByVoter($userId);
        
        // Create array of election IDs where user has voted
        $votedElectionIds = array_column($votedElections, 'election_id');
        
        // Mark elections as voted
        foreach ($activeElections as &$election) {
            $election['has_voted'] = in_array($election['id'], $votedElectionIds);
        }
        
        $data = [
            'title' => 'Voter Dashboard',
            'activeElections' => $activeElections,
            'upcomingElections' => $upcomingElections,
            'completedElections' => $completedElections,
            'votedElectionIds' => $votedElectionIds
        ];
        
        $this->view('voter/dashboard', $data);
    }
    
    /**
     * Display Ballot - Shows candidates for voting
     * 
     * @param int $electionId
     */
    public function ballot() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            $this->setFlash('error', 'Access denied. Voter login required.');
            $this->redirect('/auth/login');
            return;
        }
        
        $electionId = $_GET['id'] ?? null;
        
        if (!$electionId) {
            $this->setFlash('error', 'Invalid election.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Check if user has already voted in this election
        if ($this->voteModel->hasVoted($electionId, $userId)) {
            $this->setFlash('error', 'You have already voted in this election.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Get election details with candidates
        $election = $this->electionModel->getElectionWithCandidates($electionId);
        
        if (!$election) {
            $this->setFlash('error', 'Election not found.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Check if election is active
        $now = date('Y-m-d H:i:s');
        if ($now > $election['end_date']) {
            $this->setFlash('error', 'This election has ended.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        if ($election['status'] !== 'active' && $now < $election['start_date']) {
            $this->setFlash('error', 'This election has not started yet.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Group candidates by position
        $candidatesByPosition = [];
        foreach ($election['candidates'] as $candidate) {
            $position = $candidate['position'] ?? 'General';
            if (!isset($candidatesByPosition[$position])) {
                $candidatesByPosition[$position] = [];
            }
            $candidatesByPosition[$position][] = $candidate;
        }
        
        $data = [
            'title' => 'Cast Your Vote - ' . $election['election_name'],
            'election' => $election,
            'candidatesByPosition' => $candidatesByPosition
        ];
        
        $this->view('voter/ballot', $data);
    }
    
    /**
     * Cast Vote - Process the submitted vote
     */
    public function castVote() {
        file_put_contents('debug_log.txt', "DEBUG: castVote() method ENTERED\n", FILE_APPEND);
        
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            file_put_contents('debug_log.txt', "DEBUG: Not logged in or not voter\n", FILE_APPEND);
            $this->setFlash('error', 'Access denied. Voter login required.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            file_put_contents('debug_log.txt', "DEBUG: Not a POST request\n", FILE_APPEND);
            $this->redirect('/voter/dashboard');
            return;
        }
        
        $electionId = $_POST['election_id'] ?? null;
        $candidates = $_POST['candidates'] ?? null;
        $user = Session::getUser();
        $userId = $user['id'] ?? null;
        
        file_put_contents('debug_log.txt', "DEBUG: ElectionID: $electionId, UserID: $userId\n", FILE_APPEND);
        
        if (!$userId) {
            file_put_contents('debug_log.txt', "DEBUG: No user ID found\n", FILE_APPEND);
            $this->setFlash('error', 'User not found in session.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Validate election ID
        if (!$electionId) {
            file_put_contents('debug_log.txt', "DEBUG: No election ID\n", FILE_APPEND);
            $this->setFlash('error', 'Invalid election.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Validate input - must have candidate selections
        if (!is_array($candidates) || empty($candidates)) {
            file_put_contents('debug_log.txt', "DEBUG: No candidates selected\n", FILE_APPEND);
            $this->setFlash('error', 'Invalid vote submission. Please select a candidate for each position.');
            $this->redirect('/voter/ballot?id=' . $electionId);
            return;
        }
        
        // Check if user has already voted
        if ($this->voteModel->hasVoted($electionId, $userId)) {
            file_put_contents('debug_log.txt', "DEBUG: Already voted check true\n", FILE_APPEND);
            $this->setFlash('error', 'You have already voted in this election.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Verify election is active
        $election = $this->electionModel->find($electionId);
        $now = date('Y-m-d H:i:s');
        
        if (!$election) {
            file_put_contents('debug_log.txt', "DEBUG: Election not found in DB\n", FILE_APPEND);
            $this->setFlash('error', 'Election not found.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        if ($election['status'] !== 'active' && $now < $election['start_date']) {
            file_put_contents('debug_log.txt', "DEBUG: Election not started\n", FILE_APPEND);
            $this->setFlash('error', 'This election has not started yet.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        if ($now > $election['end_date']) {
            file_put_contents('debug_log.txt', "DEBUG: Election ended\n", FILE_APPEND);
            $this->setFlash('error', 'This election has already ended.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Verify all candidates belong to this election
        $selectedCandidates = [];
        foreach ($candidates as $position => $candidateId) {
            // Skip if not selected
            if (empty($candidateId)) {
                file_put_contents('debug_log.txt', "DEBUG: Empty candidate selection for position $position\n", FILE_APPEND);
                $this->setFlash('error', 'Please select a candidate for each position.');
                $this->redirect('/voter/ballot?id=' . $electionId);
                return;
            }
            
            $candidate = $this->candidateModel->find($candidateId);
            
            if (!$candidate || $candidate['election_id'] != $electionId) {
                file_put_contents('debug_log.txt', "DEBUG: Invalid candidate or wrong election\n", FILE_APPEND);
                $this->setFlash('error', 'Invalid candidate selection.');
                $this->redirect('/voter/ballot?id=' . $electionId);
                return;
            }
            
            $selectedCandidates[] = $candidate;
        }
        
        // Cast votes for all selected candidates
        $allVotesSuccessful = true;
        $failureReason = '';
        
        foreach ($selectedCandidates as $candidate) {
            try {
                $voteResult = $this->voteModel->castVote($electionId, $candidate['id'], $userId);
                if (!$voteResult) {
                    $allVotesSuccessful = false;
                    $failureReason = 'Failed to record vote for ' . htmlspecialchars($candidate['name']);
                    break;
                }
            } catch (Exception $e) {
                // Handle duplicate vote or other database errors gracefully
                $allVotesSuccessful = false;
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'unique_vote') !== false) {
                    $failureReason = 'You have already voted in this election. Duplicate entries are not allowed.';
                } else {
                    $failureReason = 'An error occurred while recording your vote: ' . $e->getMessage();
                }
                break;
            }
        }
        
        if ($allVotesSuccessful) {
            // Get vote details for confirmation
            $voteData = [
                'election' => $election,
                'candidates' => $selectedCandidates,
                'vote_count' => count($selectedCandidates),
                'vote_time' => date('Y-m-d H:i:s')
            ];
            
            // Set session data before flash message
            Session::set('vote_confirmation', $voteData);
            file_put_contents('debug_log.txt', "Vote successful. Session data set. Key: vote_confirmation\n", FILE_APPEND);
            
            $this->setFlash('success', 'Your vote has been successfully recorded!');
            
            // Ensure session is written before redirect to prevent data loss
            session_write_close();
            
            file_put_contents('debug_log.txt', "Redirecting to verify...\n", FILE_APPEND);
            $this->redirect('/voter/verify');
        } else {
            file_put_contents('debug_log.txt', "Vote failed: $failureReason\n", FILE_APPEND);
            $this->setFlash('error', $failureReason ?: 'Failed to cast vote. Please try again.');
            $this->redirect('/voter/ballot?id=' . $electionId);
        }
    }
    
    /**
     * Verify Submission - Shows vote confirmation
     */
    public function verify() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            $this->setFlash('error', 'Access denied. Voter login required.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Get vote confirmation data
        $voteData = Session::get('vote_confirmation');
        
        if (!$voteData) {
            file_put_contents('debug_log.txt', "Verify failed: No session data found.\n", FILE_APPEND);
            $this->setFlash('error', 'No vote confirmation found.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        file_put_contents('debug_log.txt', "Verify success: Session data found.\n", FILE_APPEND);
        
        $data = [
            'title' => 'Vote Confirmation',
            'voteData' => $voteData
        ];
        
        // Clear the confirmation data after displaying
        Session::remove('vote_confirmation');
        
        $this->view('voter/verify', $data);
    }
    
    /**
     * View Election Results (for completed elections)
     */
    public function results() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            $this->setFlash('error', 'Access denied. Voter login required.');
            $this->redirect('/auth/login');
            return;
        }
        
        $electionId = $_GET['id'] ?? null;
        
        if (!$electionId) {
            $this->setFlash('error', 'Invalid election.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Get election with results
        $election = $this->electionModel->getElectionWithCandidates($electionId);
        
        if (!$election) {
            $this->setFlash('error', 'Election not found.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Only show results for completed elections (or allow viewing during active elections if configured)
        if ($election['status'] !== 'completed') {
            $this->setFlash('error', 'Results are only available for completed elections.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Calculate total votes
        $totalVotes = 0;
        foreach ($election['candidates'] as $candidate) {
            $totalVotes += $candidate['vote_count'] ?? 0;
        }
        
        // Sort candidates by vote count and calculate percentages
        usort($election['candidates'], function($a, $b) {
            return ($b['vote_count'] ?? 0) - ($a['vote_count'] ?? 0);
        });
        
        // Add percentages
        foreach ($election['candidates'] as &$candidate) {
            $candidate['percentage'] = $totalVotes > 0 
                ? round(($candidate['vote_count'] / $totalVotes) * 100, 2)
                : 0;
        }
        
        $data = [
            'title' => 'Election Results - ' . $election['election_name'],
            'election' => $election,
            'totalVotes' => $totalVotes
        ];
        
        $this->view('voter/results', $data);
    }
    
    /**
     * Get candidate details with vote count
     * Used for AJAX requests or internal data retrieval
     * 
     * @param int $candidateId
     */
    public function getCandidateDetails() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $candidateId = $_GET['id'] ?? null;
        
        if (!$candidateId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid candidate ID']);
            return;
        }
        
        $candidate = $this->candidateModel->find($candidateId);
        
        if (!$candidate) {
            http_response_code(404);
            echo json_encode(['error' => 'Candidate not found']);
            return;
        }
        
        // Return candidate details as JSON
        header('Content-Type: application/json');
        echo json_encode($candidate);
    }
    
    /**
     * Get election voting statistics
     * Shows live voting statistics during active elections
     */
    public function getElectionStats() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $electionId = $_GET['id'] ?? null;
        
        if (!$electionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid election ID']);
            return;
        }
        
        $election = $this->electionModel->find($electionId);
        
        if (!$election) {
            http_response_code(404);
            echo json_encode(['error' => 'Election not found']);
            return;
        }
        
        // Get voting statistics
        $votingStats = $this->voteModel->getElectionVotingStats($electionId);
        $electionStats = $this->electionModel->getElectionStats($electionId);
        
        // Combine statistics
        $stats = [
            'election' => $electionStats,
            'voting' => $votingStats,
            'status' => $election['status'],
            'start_date' => $election['start_date'],
            'end_date' => $election['end_date']
        ];
        
        header('Content-Type: application/json');
        echo json_encode($stats);
    }
    
    /**
     * Get election with all details and candidates
     * Useful for partial page updates
     */
    public function getElectionDetails() {
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $electionId = $_GET['id'] ?? null;
        
        if (!$electionId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid election ID']);
            return;
        }
        
        $election = $this->electionModel->getElectionWithCandidates($electionId);
        
        if (!$election) {
            http_response_code(404);
            echo json_encode(['error' => 'Election not found']);
            return;
        }
        
        $userId = Session::get('user_id');
        $election['has_voted'] = $this->voteModel->hasVoted($electionId, $userId);
        $election['is_active'] = $this->electionModel->isActive($electionId);
        
        header('Content-Type: application/json');
        echo json_encode($election);
    }
}
