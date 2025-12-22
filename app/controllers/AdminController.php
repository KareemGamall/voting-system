<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Election.php';
require_once __DIR__ . '/../models/Candidate.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../builders/ElectionBuilder.php';
require_once __DIR__ . '/../builders/ConcreteElectionBuilder.php';
require_once __DIR__ . '/../builders/ElectionDirector.php';

/**
 * Admin Controller
 * Handles admin panel functionality
 * Uses Builder Design Pattern for election creation
 */
class AdminController extends Controller {
    
    private $userModel;
    private $electionModel;
    private $candidateModel;
    private $voteModel;
    private $electionDirector;
    
    public function __construct() {
        $this->userModel = new User();
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->voteModel = new Vote();
        $this->electionDirector = new ElectionDirector();
    }
    
    /**
     * Check admin authentication
     */
    private function isAdmin() {
        Session::start();
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            $this->redirect('/');
            return false;
        }
        return true;
    }
    
    /**
     * Admin Dashboard (with localhost bypass for dev)
     */
    public function dashboard() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        // Get data from database
        $totalVoters = $this->userModel->count(['is_voter' => 1]);
        $elections = $this->electionModel->getAll();
        $activeElections = count(array_filter($elections ?? [], fn($e) => ($e['status'] ?? '') === 'active'));
        $totalVotes = $this->voteModel->count();
        $turnout = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100) : 0;
        
        $data = [
            'title' => 'Admin Dashboard',
            'user' => Session::getUser(),
            'stats' => [
                'totalVoters' => $totalVoters,
                'activeElections' => $activeElections,
                'totalVotes' => $totalVotes,
                'turnout' => $turnout
            ],
            'elections' => $elections ?? []
        ];
        
        $this->view('admin/dashboard', $data);
    }
    
    /**
     * Create/Edit Elections Page
     */
    public function elections() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        $elections = $this->electionModel->getAll();
        
        $data = [
            'title' => 'Create/Manage Elections',
            'user' => Session::getUser(),
            'elections' => $elections
        ];
        
        $this->view('admin/elections', $data);
    }
    
    /**
     * Manage Voters Page
     */
    public function voters() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        $voters = $this->userModel->getByRole('voter');
        $admins = $this->userModel->getByRole('admin');
        
        $data = [
            'title' => 'Manage Users',
            'user' => Session::getUser(),
            'voters' => $voters ?: [],
            'admins' => $admins ?: []
        ];
        
        $this->view('admin/voters', $data);
    }
    
    /**
     * Save election using Builder Pattern (AJAX)
     * 
     * Uses ConcreteElectionBuilder and ElectionDirector to construct elections
     */
    public function saveElection() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        header('Content-Type: application/json');
        
        $title = $_POST['title'] ?? '';
        $status = $_POST['status'] ?? 'upcoming';
        $description = $_POST['description'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $candidates = $_POST['candidates'] ?? [];
        
        if (!$title || !$startDate || !$endDate) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        try {
            // Map status values to match database enum
            $statusMap = [
                'draft' => 'upcoming',
                'upcoming' => 'upcoming',
                'active' => 'active',
                'closed' => 'completed',
                'completed' => 'completed',
                'cancelled' => 'cancelled'
            ];
            $dbStatus = $statusMap[$status] ?? 'upcoming';
            
            // Format dates for database (convert from datetime-local to DATETIME format)
            $startDateFormatted = date('Y-m-d H:i:s', strtotime($startDate));
            $endDateFormatted = date('Y-m-d H:i:s', strtotime($endDate));
            
            // Create builder and use director to construct election (for builder pattern)
            $builder = new ConcreteElectionBuilder();
            
            $electionData = [
                'name' => $title,
                'description' => $description,
                'startDate' => $startDateFormatted,
                'endDate' => $endDateFormatted,
                'status' => $dbStatus,
                'candidates' => $candidates
            ];
            
            // Use director to construct full election
            $election = $this->electionDirector->constructFullElection($builder, $electionData);
            
            // Debug: Check if election object has the required properties
            if (!isset($election->electionName) || empty($election->electionName)) {
                echo json_encode(['success' => false, 'message' => 'Election name is missing or empty']);
                exit;
            }
            
            // Prepare data for database
            $dbData = [
                'election_name' => $election->electionName ?? $title,
                'description' => $election->description ?? $description,
                'start_date' => $election->startDate ?? $startDateFormatted,
                'end_date' => $election->endDate ?? $endDateFormatted,
                'status' => $election->status ?? $dbStatus
            ];
            
            
            // Save election to database using createElection method (generates election_id)
            $electionId = $this->electionModel->createElection($dbData);
            
            // Check if election was created successfully
            if (!$electionId) {
                echo json_encode(['success' => false, 'message' => 'Failed to create election. Please check database connection.']);
                exit;
            }
            
            // Save candidates using addCandidate method (generates candidate_id)
            if (is_array($candidates) && !empty($candidates)) {
                foreach ($candidates as $candidate) {
                    if (isset($candidate['name']) && !empty($candidate['name'])) {
                        $this->candidateModel->addCandidate([
                            'election_id' => $electionId,
                            'name' => $candidate['name'],
                            'position' => $candidate['position'] ?? 'General',
                            'party' => $candidate['party'] ?? ($candidate['affiliation'] ?? '')
                        ]);
                    }
                }
            }
            
            echo json_encode(['success' => true, 'election_id' => $electionId]);
            exit;
            
        } catch (Exception $e) {
            error_log('Election creation error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Error creating election: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            exit;
        } catch (Error $e) {
            error_log('Fatal error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Fatal error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Get election data for editing (AJAX)
     */
    public function getElection() {
        if (!$this->isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Election ID is required']);
            exit;
        }
        
        $election = $this->electionModel->getElectionWithCandidates($id);
        
        if (!$election) {
            echo json_encode(['success' => false, 'message' => 'Election not found']);
            exit;
        }
        
        echo json_encode(['success' => true, 'election' => $election]);
        exit;
    }
    
    /**
     * Update election using Builder Pattern (AJAX)
     */
    public function updateElection() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        header('Content-Type: application/json');
        
        $electionId = $_POST['election_id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $status = $_POST['status'] ?? 'upcoming';
        $description = $_POST['description'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $candidates = $_POST['candidates'] ?? [];
        
        if (!$electionId || !$title || !$startDate || !$endDate) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        try {
            // Map status values to match database enum
            $statusMap = [
                'draft' => 'upcoming',
                'upcoming' => 'upcoming',
                'active' => 'active',
                'closed' => 'completed',
                'completed' => 'completed',
                'cancelled' => 'cancelled'
            ];
            $dbStatus = $statusMap[$status] ?? 'upcoming';
            
            // Format dates for database
            $startDateFormatted = date('Y-m-d H:i:s', strtotime($startDate));
            $endDateFormatted = date('Y-m-d H:i:s', strtotime($endDate));
            
            // Update election in database
            $updated = $this->electionModel->update($electionId, [
                'election_name' => $title,
                'description' => $description,
                'start_date' => $startDateFormatted,
                'end_date' => $endDateFormatted,
                'status' => $dbStatus
            ]);
            
            if (!$updated) {
                echo json_encode(['success' => false, 'message' => 'Failed to update election']);
                exit;
            }
            
            // Delete existing candidates
            $existingCandidates = $this->candidateModel->getCandidatesByElection($electionId);
            foreach ($existingCandidates as $existingCandidate) {
                $this->candidateModel->delete($existingCandidate['id']);
            }
            
            // Add new candidates
            if (is_array($candidates) && !empty($candidates)) {
                foreach ($candidates as $candidate) {
                    if (isset($candidate['name']) && !empty($candidate['name'])) {
                        $this->candidateModel->addCandidate([
                            'election_id' => $electionId,
                            'name' => $candidate['name'],
                            'position' => $candidate['position'] ?? 'General',
                            'party' => $candidate['party'] ?? ($candidate['affiliation'] ?? '')
                        ]);
                    }
                }
            }
            
            echo json_encode(['success' => true, 'election_id' => $electionId]);
            exit;
            
        } catch (Exception $e) {
            error_log('Election update error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error updating election: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Delete election (AJAX)
     * Note: This will cascade delete candidates and votes due to foreign key constraints
     */
    public function deleteElection() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid election ID']);
            exit;
        }
        
        try {
            // Check if election exists
            $election = $this->electionModel->find($id);
            if (!$election) {
                echo json_encode(['success' => false, 'message' => 'Election not found']);
                exit;
            }
            
            // Delete all candidates first (foreign key will handle votes)
            $candidates = $this->candidateModel->getCandidatesByElection($id);
            foreach ($candidates as $candidate) {
                $this->candidateModel->delete($candidate['id']);
            }
            
            // Delete the election (cascade will handle related records)
            $deleted = $this->electionModel->delete($id);
            
            if ($deleted) {
                echo json_encode(['success' => true, 'message' => 'Election deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete election']);
            }
            exit;
            
        } catch (Exception $e) {
            error_log('Election deletion error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error deleting election: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Add voter (AJAX)
     */
    public function addVoter() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        header('Content-Type: application/json');
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (!$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        // Generate unique user ID
        $userId = 'USR-' . time() . '-' . rand(1000, 9999);
        
        // Create voter account
        $voterId = $this->userModel->create([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'password' => password_hash('voter123', PASSWORD_BCRYPT),
            'is_admin' => 0,
            'is_voter' => 1
        ]);
        
        if ($voterId) {
            echo json_encode(['success' => true, 'voter_id' => $voterId, 'message' => 'Voter account created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create voter account']);
        }
        exit;
    }
    
    /**
     * Remove voter (AJAX)
     */
    public function removeVoter() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        header('Content-Type: application/json');
        
        $voterId = $_POST['voter_id'] ?? 0;
        
        if (!$voterId) {
            echo json_encode(['success' => false, 'message' => 'Invalid voter ID']);
            exit;
        }
        
        $this->userModel->delete($voterId);
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    /**
     * Add admin (AJAX)
     */
    public function addAdmin() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        header('Content-Type: application/json');
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (!$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        // Generate unique user ID
        $userId = 'USR-' . time() . '-' . rand(1000, 9999);
        
        // If password is provided, use it; otherwise generate default
        $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : password_hash('admin123', PASSWORD_BCRYPT);
        
        // Create admin account
        $adminId = $this->userModel->create([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'is_admin' => 1,
            'is_voter' => 0
        ]);
        
        if ($adminId) {
            echo json_encode(['success' => true, 'admin_id' => $adminId, 'message' => 'Admin account created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create admin account']);
        }
        exit;
    }
    
    /**
     * Remove admin (AJAX)
     */
    public function removeAdmin() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        header('Content-Type: application/json');
        
        $adminId = $_POST['admin_id'] ?? 0;
        
        if (!$adminId) {
            echo json_encode(['success' => false, 'message' => 'Invalid admin ID']);
            exit;
        }
        
        // Prevent deleting yourself
        Session::start();
        $currentUser = Session::getUser();
        if ($currentUser && $currentUser['id'] == $adminId) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
            exit;
        }
        
        $this->userModel->delete($adminId);
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    /**
     * Live Monitor Page
     */
    public function monitor() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        $data = [
            'title' => 'Live Monitor',
            'user' => Session::getUser()
        ];
        
        $this->view('admin/monitor', $data);
    }
    
    /**
     * Monitor Data API (AJAX)
     */
    public function monitorData() {
        if (!$this->isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        // Get election ID from URL parameter or GET parameter
        $electionId = $_GET['id'] ?? null;
        
        if ($electionId) {
            // Get specific election data
            $election = $this->electionModel->find($electionId);
            if (!$election) {
                echo json_encode(['success' => false, 'message' => 'Election not found']);
                exit;
            }
            
            $candidates = $this->candidateModel->getCandidatesByElection($electionId);
            $voteCounts = [];
            $recentVotes = [];
            
            foreach ($candidates as $candidate) {
                $votes = $this->voteModel->getVotesByCandidate($candidate['id']);
                $voteCounts[] = count($votes);
                
                // Get recent votes for this candidate
                if (!empty($votes)) {
                    $recentVote = end($votes);
                    $voter = $this->userModel->find($recentVote['voter_id']);
                    $recentVotes[] = [
                        'voter_name' => $voter['name'] ?? 'Unknown',
                        'candidate_name' => $candidate['name'],
                        'vote_time' => $recentVote['vote_time'] ?? 'N/A'
                    ];
                }
            }
            
            // Sort recent votes by time (most recent first)
            usort($recentVotes, function($a, $b) {
                return strtotime($b['vote_time']) - strtotime($a['vote_time']);
            });
            $recentVotes = array_slice($recentVotes, 0, 10); // Get last 10
            
            echo json_encode([
                'success' => true,
                'candidates' => $candidates,
                'voteCounts' => $voteCounts,
                'recentVotes' => $recentVotes
            ]);
        } else {
            // Get all elections for dropdown
            $elections = $this->electionModel->getAll();
            echo json_encode([
                'success' => true,
                'elections' => $elections ?? []
            ]);
        }
        exit;
    }
    
    /**
     * Results Page
     */
    public function results() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        $data = [
            'title' => 'Election Results',
            'user' => Session::getUser()
        ];
        
        $this->view('admin/results', $data);
    }
    
    /**
     * Results Data API (AJAX) - Get elections list
     */
    public function resultsData() {
        if (!$this->isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        $elections = $this->electionModel->getAll();
        echo json_encode([
            'success' => true,
            'elections' => $elections ?? []
        ]);
        exit;
    }
    
    /**
     * Get Results for Specific Election (AJAX)
     */
    public function getResults($electionId) {
        if (!$this->isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $election = $this->electionModel->find($electionId);
        if (!$election) {
            echo json_encode(['success' => false, 'message' => 'Election not found']);
            exit;
        }
        
        $candidates = $this->candidateModel->getCandidatesByElection($electionId);
        $totalVotes = 0;
        
        // Get vote counts for each candidate
        foreach ($candidates as &$candidate) {
            $votes = $this->voteModel->getVotesByCandidate($candidate['id']);
            $candidate['voteCount'] = count($votes);
            $totalVotes += $candidate['voteCount'];
        }
        
        echo json_encode([
            'success' => true,
            'election' => $election,
            'candidates' => $candidates,
            'totalVotes' => $totalVotes
        ]);
        exit;
    }
}
?>
