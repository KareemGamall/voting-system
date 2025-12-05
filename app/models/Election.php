<?php

require_once __DIR__ . '/../core/Model.php';

class Election extends Model {
    protected $table = 'elections';
    
    private $electionId;
    private $electionName;
    private $description;
    private $startDate;
    private $endDate;
    private $status;
    private $candidates = [];
    
    /**
     * Create a new election
     * 
     * @param array $electionData
     * @return bool
     */
    public function createElection($electionData) {
        // Generate unique election ID
        if (!isset($electionData['election_id'])) {
            $electionData['election_id'] = $this->generateElectionId();
        }
        
        // Set default status
        if (!isset($electionData['status'])) {
            $electionData['status'] = 'upcoming';
        }
        
        return $this->create($electionData);
    }
    
    /**
     * Close an election
     * 
     * @param int $id
     * @return bool
     */
    public function closeElection($id) {
        return $this->update($id, ['status' => 'completed']);
    }
    
    /**
     * Get active elections (currently running)
     * 
     * @return array
     */
    public function getActiveElections() {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active' 
                AND start_date <= :now 
                AND end_date >= :now";
        
        return $this->query($sql, ['now' => $now]);
    }
    
    /**
     * Get upcoming elections
     * 
     * @return array
     */
    public function getUpcomingElections() {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'upcoming' 
                AND start_date > :now";
        
        return $this->query($sql, ['now' => $now]);
    }
    
    /**
     * Get completed elections
     * 
     * @return array
     */
    public function getCompletedElections() {
        return $this->where('status = :status', ['status' => 'completed']);
    }
    
    /**
     * Get election with candidates
     * 
     * @param int $id
     * @return array|false
     */
    public function getElectionWithCandidates($id) {
        $election = $this->find($id);
        
        if (!$election) {
            return false;
        }
        
        require_once __DIR__ . '/Candidate.php';
        $candidateModel = new Candidate();
        $election['candidates'] = $candidateModel->getCandidatesByElection($id);
        
        return $election;
    }
    
    /**
     * Check if election is active
     * 
     * @param int $id
     * @return bool
     */
    public function isActive($id) {
        $election = $this->find($id);
        
        if (!$election) {
            return false;
        }
        
        $now = time();
        $startTime = strtotime($election['start_date']);
        $endTime = strtotime($election['end_date']);
        
        return ($election['status'] === 'active' && $now >= $startTime && $now <= $endTime);
    }
    
    /**
     * Start election (change status to active)
     * 
     * @param int $id
     * @return bool
     */
    public function startElection($id) {
        return $this->update($id, ['status' => 'active']);
    }
    
    /**
     * Find election by election_id
     * 
     * @param string $electionId
     * @return array|false
     */
    public function findByElectionId($electionId) {
        return $this->findWhere('election_id = :election_id', ['election_id' => $electionId]);
    }
    
    /**
     * Generate unique election ID
     * 
     * @return string
     */
    private function generateElectionId() {
        return 'ELEC-' . date('Y') . '-' . rand(1000, 9999);
    }
    
    /**
     * Update election status based on dates
     * 
     * @return void
     */
    public function updateElectionStatuses() {
        $now = date('Y-m-d H:i:s');
        
        // Start elections that should be active
        $sql = "UPDATE {$this->table} 
                SET status = 'active' 
                WHERE status = 'upcoming' 
                AND start_date <= :now 
                AND end_date >= :now";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['now' => $now]);
        
        // Close elections that have ended
        $sql = "UPDATE {$this->table} 
                SET status = 'completed' 
                WHERE status = 'active' 
                AND end_date < :now";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['now' => $now]);
    }
    
    /**
     * Get election statistics
     * 
     * @param int $id
     * @return array
     */
    public function getElectionStats($id) {
        $sql = "SELECT 
                    e.id,
                    e.election_name,
                    COUNT(DISTINCT c.id) as total_candidates,
                    COUNT(DISTINCT v.id) as total_votes
                FROM elections e
                LEFT JOIN candidates c ON e.id = c.election_id
                LEFT JOIN votes v ON e.id = v.election_id
                WHERE e.id = :id
                GROUP BY e.id";
        
        $result = $this->query($sql, ['id' => $id]);
        return $result[0] ?? null;
    }
}
