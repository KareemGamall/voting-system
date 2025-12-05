<?php

require_once __DIR__ . '/../core/Model.php';

class Result extends Model {
    protected $table = 'results';
    
    private $electionId;
    private $candidateId;
    private $voteCount;
    
    /**
     * Calculate and generate results for an election
     * 
     * @param int $electionId
     * @return bool
     */
    public function calculateResults($electionId) {
        // First, clear any existing results for this election
        $this->clearElectionResults($electionId);
        
        // Get vote counts for each candidate in the election
        $sql = "SELECT 
                    c.id as candidate_id,
                    COUNT(v.id) as vote_count
                FROM candidates c
                LEFT JOIN votes v ON c.id = v.candidate_id
                WHERE c.election_id = :election_id
                GROUP BY c.id";
        
        require_once __DIR__ . '/Vote.php';
        $voteModel = new Vote();
        $results = $voteModel->query($sql, ['election_id' => $electionId]);
        
        // Calculate total votes
        $totalVotes = array_sum(array_column($results, 'vote_count'));
        
        // Insert results with percentages
        foreach ($results as $result) {
            $percentage = $totalVotes > 0 
                ? round(($result['vote_count'] / $totalVotes) * 100, 2) 
                : 0;
            
            $this->create([
                'election_id' => $electionId,
                'candidate_id' => $result['candidate_id'],
                'vote_count' => $result['vote_count'],
                'percentage' => $percentage
            ]);
        }
        
        return true;
    }
    
    /**
     * Get results for a specific election
     * 
     * @param int $electionId
     * @return array
     */
    public function getElectionResults($electionId) {
        $sql = "SELECT 
                    r.*,
                    c.name as candidate_name,
                    c.position,
                    c.party,
                    c.photo,
                    e.election_name
                FROM {$this->table} r
                JOIN candidates c ON r.candidate_id = c.id
                JOIN elections e ON r.election_id = e.id
                WHERE r.election_id = :election_id
                ORDER BY r.vote_count DESC";
        
        return $this->query($sql, ['election_id' => $electionId]);
    }
    
    /**
     * Get results grouped by position for an election
     * 
     * @param int $electionId
     * @return array
     */
    public function getResultsByPosition($electionId) {
        $sql = "SELECT 
                    c.position,
                    r.*,
                    c.name as candidate_name,
                    c.party,
                    c.photo
                FROM {$this->table} r
                JOIN candidates c ON r.candidate_id = c.id
                WHERE r.election_id = :election_id
                ORDER BY c.position ASC, r.vote_count DESC";
        
        $results = $this->query($sql, ['election_id' => $electionId]);
        
        // Group by position
        $groupedResults = [];
        foreach ($results as $result) {
            $position = $result['position'];
            if (!isset($groupedResults[$position])) {
                $groupedResults[$position] = [];
            }
            $groupedResults[$position][] = $result;
        }
        
        return $groupedResults;
    }
    
    /**
     * Get winner(s) for a specific position in an election
     * 
     * @param int $electionId
     * @param string $position
     * @return array|null
     */
    public function getWinnerByPosition($electionId, $position) {
        $sql = "SELECT 
                    r.*,
                    c.name as candidate_name,
                    c.position,
                    c.party,
                    c.photo
                FROM {$this->table} r
                JOIN candidates c ON r.candidate_id = c.id
                WHERE r.election_id = :election_id 
                AND c.position = :position
                ORDER BY r.vote_count DESC
                LIMIT 1";
        
        $result = $this->query($sql, [
            'election_id' => $electionId,
            'position' => $position
        ]);
        
        return $result[0] ?? null;
    }
    
    /**
     * Get all winners for an election
     * 
     * @param int $electionId
     * @return array
     */
    public function getAllWinners($electionId) {
        // Get all positions
        require_once __DIR__ . '/Candidate.php';
        $candidateModel = new Candidate();
        $positions = $candidateModel->getPositionsByElection($electionId);
        
        $winners = [];
        foreach ($positions as $positionData) {
            $position = $positionData['position'];
            $winner = $this->getWinnerByPosition($electionId, $position);
            
            if ($winner) {
                $winners[] = $winner;
            }
        }
        
        return $winners;
    }
    
    /**
     * Generate a report for an election
     * 
     * @param int $electionId
     * @return array
     */
    public function generateReport($electionId) {
        $sql = "SELECT 
                    e.election_name,
                    e.start_date,
                    e.end_date,
                    COUNT(DISTINCT r.candidate_id) as total_candidates,
                    SUM(r.vote_count) as total_votes,
                    COUNT(DISTINCT c.position) as total_positions
                FROM elections e
                LEFT JOIN {$this->table} r ON e.id = r.election_id
                LEFT JOIN candidates c ON e.id = c.election_id
                WHERE e.id = :election_id
                GROUP BY e.id";
        
        $summary = $this->query($sql, ['election_id' => $electionId]);
        
        return [
            'summary' => $summary[0] ?? null,
            'results' => $this->getElectionResults($electionId),
            'results_by_position' => $this->getResultsByPosition($electionId),
            'winners' => $this->getAllWinners($electionId)
        ];
    }
    
    /**
     * Clear results for an election
     * 
     * @param int $electionId
     * @return bool
     */
    private function clearElectionResults($electionId) {
        $sql = "DELETE FROM {$this->table} WHERE election_id = :election_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['election_id' => $electionId]);
    }
    
    /**
     * Get candidate result in an election
     * 
     * @param int $electionId
     * @param int $candidateId
     * @return array|false
     */
    public function getCandidateResult($electionId, $candidateId) {
        $sql = "SELECT 
                    r.*,
                    c.name as candidate_name,
                    c.position,
                    c.party,
                    c.photo
                FROM {$this->table} r
                JOIN candidates c ON r.candidate_id = c.id
                WHERE r.election_id = :election_id 
                AND r.candidate_id = :candidate_id";
        
        $result = $this->query($sql, [
            'election_id' => $electionId,
            'candidate_id' => $candidateId
        ]);
        
        return $result[0] ?? false;
    }
    
    /**
     * Update results for a specific candidate
     * 
     * @param int $electionId
     * @param int $candidateId
     * @return bool
     */
    public function updateCandidateResult($electionId, $candidateId) {
        // Count votes for this candidate
        require_once __DIR__ . '/Vote.php';
        $voteModel = new Vote();
        $voteCount = $voteModel->countVotesByCandidate($candidateId);
        
        // Get total votes in election
        $totalVotes = $voteModel->countVotesByElection($electionId);
        
        // Calculate percentage
        $percentage = $totalVotes > 0 
            ? round(($voteCount / $totalVotes) * 100, 2) 
            : 0;
        
        // Check if result exists
        $existing = $this->findWhere(
            'election_id = :election_id AND candidate_id = :candidate_id',
            [
                'election_id' => $electionId,
                'candidate_id' => $candidateId
            ]
        );
        
        if ($existing) {
            // Update existing result
            $sql = "UPDATE {$this->table} 
                    SET vote_count = :vote_count, percentage = :percentage 
                    WHERE election_id = :election_id AND candidate_id = :candidate_id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'vote_count' => $voteCount,
                'percentage' => $percentage,
                'election_id' => $electionId,
                'candidate_id' => $candidateId
            ]);
        } else {
            // Create new result
            return $this->create([
                'election_id' => $electionId,
                'candidate_id' => $candidateId,
                'vote_count' => $voteCount,
                'percentage' => $percentage
            ]);
        }
    }
}
