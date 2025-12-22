<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Result.php';
require_once __DIR__ . '/../models/Election.php';
require_once __DIR__ . '/../models/Candidate.php';
require_once __DIR__ . '/../models/Vote.php';

/**
 * Result Controller
 * Handles results generation, viewing, and reporting
 * For both admin and voter access
 */
class ResultController extends Controller {
    
    private $resultModel;
    private $electionModel;
    private $candidateModel;
    private $voteModel;
    
    public function __construct() {
        $this->resultModel = new Result();
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->voteModel = new Vote();
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
     * Check if user is logged in (voter or admin)
     */
    private function isLoggedIn() {
        Session::start();
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
            return false;
        }
        return true;
    }
    
    /**
     * Admin: Generate Results Page
     * Shows elections that can have results generated
     */
    public function generateResults() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        // Get all elections
        $elections = $this->electionModel->getAll();
        
        $data = [
            'title' => 'Generate Election Results',
            'user' => Session::getUser(),
            'elections' => $elections ?? []
        ];
        
        $this->view('admin/generate_results', $data);
    }
    
    /**
     * Admin: Generate Results Action (AJAX)
     * Calculates and stores results for an election
     */
    public function generateResultsAction() {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $electionId = $_POST['election_id'] ?? 0;
        
        if (!$electionId) {
            echo json_encode(['success' => false, 'message' => 'Election ID is required']);
            exit;
        }
        
        // Check if election exists
        $election = $this->electionModel->find($electionId);
        if (!$election) {
            echo json_encode(['success' => false, 'message' => 'Election not found']);
            exit;
        }
        
        try {
            // Calculate and generate results
            $success = $this->resultModel->calculateResults($electionId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Results generated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to generate results'
                ]);
            }
        } catch (Exception $e) {
            error_log('Result generation error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error generating results: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Admin: View Results Page
     * Enhanced results viewing with charts and detailed reports
     */
    public function viewResults() {
        if (!$this->isAdmin()) return;
        
        Session::start();
        
        $elections = $this->electionModel->getAll();
        
        $data = [
            'title' => 'Election Results & Reports',
            'user' => Session::getUser(),
            'elections' => $elections ?? []
        ];
        
        $this->view('admin/results', $data);
    }
    
    /**
     * Admin: Get Results Data API (AJAX)
     * Returns detailed results for an election
     */
    public function getResultsData() {
        if (!$this->isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $electionId = $_GET['election_id'] ?? 0;
        
        if (!$electionId) {
            echo json_encode(['success' => false, 'message' => 'Election ID is required']);
            exit;
        }
        
        try {
            // Get election details
            $election = $this->electionModel->find($electionId);
            if (!$election) {
                echo json_encode(['success' => false, 'message' => 'Election not found']);
                exit;
            }
            
            // Get results
            $results = $this->resultModel->getElectionResults($electionId);
            $resultsByPosition = $this->resultModel->getResultsByPosition($electionId);
            $winners = $this->resultModel->getAllWinners($electionId);
            
            // Get voting statistics
            $votingStats = $this->voteModel->getElectionVotingStats($electionId);
            
            // Calculate totals
            $totalVotes = array_sum(array_column($results, 'vote_count'));
            $totalCandidates = count($results);
            
            // Get all candidates (including those with 0 votes)
            $allCandidates = $this->candidateModel->getCandidatesByElection($electionId);
            
            echo json_encode([
                'success' => true,
                'election' => $election,
                'results' => $results,
                'resultsByPosition' => $resultsByPosition,
                'winners' => $winners,
                'votingStats' => $votingStats,
                'totalVotes' => $totalVotes,
                'totalCandidates' => $totalCandidates,
                'allCandidates' => $allCandidates
            ]);
        } catch (Exception $e) {
            error_log('Get results error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching results: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Admin: Get Elections List API (AJAX)
     */
    public function getElectionsList() {
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
     * Voter: View Results Page
     * Shows results for completed elections that voters can view
     */
    public function voterResults() {
        if (!$this->isLoggedIn()) return;
        
        Session::start();
        
        // Get completed elections
        $completedElections = $this->electionModel->getCompletedElections();
        
        $data = [
            'title' => 'Election Results',
            'user' => Session::getUser(),
            'elections' => $completedElections ?? []
        ];
        
        $this->view('voter/results', $data);
    }
    
    /**
     * Voter: Get Results Data API (AJAX)
     * Returns results for a specific election (voter view)
     */
    public function getVoterResultsData() {
        if (!$this->isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $electionId = $_GET['election_id'] ?? 0;
        
        if (!$electionId) {
            echo json_encode(['success' => false, 'message' => 'Election ID is required']);
            exit;
        }
        
        try {
            // Get election details
            $election = $this->electionModel->find($electionId);
            if (!$election) {
                echo json_encode(['success' => false, 'message' => 'Election not found']);
                exit;
            }
            
            // Only show results for completed elections
            if ($election['status'] !== 'completed') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Results are only available for completed elections'
                ]);
                exit;
            }
            
            // Get results
            $results = $this->resultModel->getElectionResults($electionId);
            $resultsByPosition = $this->resultModel->getResultsByPosition($electionId);
            $winners = $this->resultModel->getAllWinners($electionId);
            
            // Calculate totals
            $totalVotes = array_sum(array_column($results, 'vote_count'));
            
            echo json_encode([
                'success' => true,
                'election' => $election,
                'results' => $results,
                'resultsByPosition' => $resultsByPosition,
                'winners' => $winners,
                'totalVotes' => $totalVotes
            ]);
        } catch (Exception $e) {
            error_log('Get voter results error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching results: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Admin: Export Results as PDF/CSV (Future enhancement)
     * Currently returns JSON for download
     */
    public function exportResults() {
        if (!$this->isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $electionId = $_GET['election_id'] ?? 0;
        $format = $_GET['format'] ?? 'json';
        
        if (!$electionId) {
            echo json_encode(['success' => false, 'message' => 'Election ID is required']);
            exit;
        }
        
        try {
            $report = $this->resultModel->generateReport($electionId);
            
            if ($format === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="election_results_' . $electionId . '.csv"');
                
                $output = fopen('php://output', 'w');
                
                // Write header
                fputcsv($output, ['Position', 'Candidate Name', 'Party', 'Votes', 'Percentage']);
                
                // Write data
                foreach ($report['results'] as $result) {
                    fputcsv($output, [
                        $result['position'],
                        $result['candidate_name'],
                        $result['party'] ?? 'Independent',
                        $result['vote_count'],
                        $result['percentage'] . '%'
                    ]);
                }
                
                fclose($output);
            } else {
                // JSON format
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="election_results_' . $electionId . '.json"');
                echo json_encode($report, JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            error_log('Export results error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error exporting results: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}


