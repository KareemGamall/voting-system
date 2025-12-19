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
        
        // Get active elections
        $activeElections = $this->electionModel->getActiveElections();
        
        // Get upcoming elections
        $upcomingElections = $this->electionModel->getUpcomingElections();
        
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
        // Check if user is logged in as voter
        if (!Session::isLoggedIn() || !Session::isVoter()) {
            $this->setFlash('error', 'Access denied. Voter login required.');
            $this->redirect('/auth/login');
            return;
        }
        
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/voter/dashboard');
            return;
        }
        
        $electionId = $_POST['election_id'] ?? null;
        $candidates = $_POST['candidates'] ?? null;
        $userId = Session::get('user_id');
        
        // Validate input - handle both single and multiple candidates
        if (!$electionId || empty($candidates)) {
            $this->setFlash('error', 'Invalid vote submission. Please select a candidate for each position.');
            $this->redirect('/voter/ballot?id=' . $electionId);
            return;
        }
        
        // Check if user has already voted
        if ($this->voteModel->hasVoted($electionId, $userId)) {
            $this->setFlash('error', 'You have already voted in this election.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Verify election is active
        $election = $this->electionModel->find($electionId);
        $now = date('Y-m-d H:i:s');
        
        if (!$election || $election['status'] !== 'active' || $now < $election['start_date'] || $now > $election['end_date']) {
            $this->setFlash('error', 'This election is not currently accepting votes.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Convert to array if single candidate (for compatibility)
        if (!is_array($candidates)) {
            $candidates = [$candidates];
        }
        
        // Verify all candidates belong to this election
        $selectedCandidates = [];
        foreach ($candidates as $position => $candidateId) {
            $candidate = $this->candidateModel->find($candidateId);
            
            if (!$candidate || $candidate['election_id'] != $electionId) {
                $this->setFlash('error', 'Invalid candidate selection.');
                $this->redirect('/voter/ballot?id=' . $electionId);
                return;
            }
            
            $selectedCandidates[] = $candidate;
        }
        
        // Cast votes for all selected candidates
        $allVotesSuccessful = true;
        foreach ($selectedCandidates as $candidate) {
            $voteResult = $this->voteModel->castVote($electionId, $candidate['id'], $userId);
            if (!$voteResult) {
                $allVotesSuccessful = false;
                break;
            }
        }
        
        if ($allVotesSuccessful) {
            // Get vote details for confirmation
            $voteData = [
                'election' => $election,
                'candidate' => $selectedCandidates[0], // Show first candidate for confirmation
                'candidates' => $selectedCandidates,
                'vote_time' => date('Y-m-d H:i:s')
            ];
            
            $this->setFlash('success', 'Your vote has been successfully recorded!');
            Session::set('vote_confirmation', $voteData);
            $this->redirect('/voter/verify');
        } else {
            $this->setFlash('error', 'Failed to cast vote. Please try again.');
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
            $this->setFlash('error', 'No vote confirmation found.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
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
        
        // Only show results for completed elections
        if ($election['status'] !== 'completed') {
            $this->setFlash('error', 'Results are only available for completed elections.');
            $this->redirect('/voter/dashboard');
            return;
        }
        
        // Sort candidates by vote count
        usort($election['candidates'], function($a, $b) {
            return $b['vote_count'] - $a['vote_count'];
        });
        
        $data = [
            'title' => 'Election Results - ' . $election['election_name'],
            'election' => $election
        ];
        
        $this->view('voter/results', $data);
    }
}
