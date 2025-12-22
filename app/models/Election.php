<?php

require_once __DIR__ . '/../core/Model.php';

class Election extends Model {
    protected $table = 'elections';
    
    public $electionId;
    public $electionName;
    public $description;
    public $startDate;
    public $endDate;
    public $status;
    public $candidates = [];
    
    /**
     * Get all elections with updated statuses
     * Override parent getAll to ensure statuses are current
     * 
     * @return array
     */
    public function getAll() {
        // Update election statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
        return parent::getAll();
    }
    
    /**
     * Find election by ID with updated status
     * Override parent find to ensure status is current
     * 
     * @param int $id
     * @return array|false
     */
    public function find($id) {
        // Update election statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
        return parent::find($id);
    }
    
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
        // Update election statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active'
                ORDER BY start_date DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Get latest elections (newest first) with limit
     * 
     * @param int $limit Maximum number of elections to return
     * @return array
     */
    public function getLatestElections($limit = 6) {
        // Update election statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE status IN ('active', 'upcoming')
                ORDER BY created_at DESC, start_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get upcoming elections
     * 
     * @return array
     */
    public function getUpcomingElections() {
        // Update statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'upcoming' 
                ORDER BY start_date ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Get completed elections
     * 
     * @return array
     */
    public function getCompletedElections() {
        // Update statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
        return $this->where('status = :status', ['status' => 'completed']);
    }
    
    /**
     * Get election with candidates
     * Note: Uses overridden find() which updates statuses
     * 
     * @param int $id
     * @return array|false
     */
    public function getElectionWithCandidates($id) {
        // find() now automatically updates statuses
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
     * Note: find() automatically updates statuses
     * 
     * @param int $id
     * @return bool
     */
    public function isActive($id) {
        // find() already updates statuses
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
        // Update statuses first to ensure accuracy
        $this->updateElectionStatuses();
        
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
        
        // Update all elections based on their dates (except cancelled ones)
        // Fix elections that should be upcoming (start date in future)
        $sql = "UPDATE {$this->table} 
                SET status = 'upcoming' 
                WHERE start_date > :now 
                AND status NOT IN ('cancelled')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['now' => $now]);
        
        // Start elections that should be active (between start and end date)
        $sql = "UPDATE {$this->table} 
                SET status = 'active' 
                WHERE start_date <= :now1 
                AND end_date >= :now2
                AND status NOT IN ('cancelled')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['now1' => $now, 'now2' => $now]);
        
        // Close elections that have ended
        $sql = "UPDATE {$this->table} 
                SET status = 'completed' 
                WHERE end_date < :now
                AND status NOT IN ('cancelled')";
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
    
    /**
     * Count all elections in the system
     * 
     * @return int
     */
    public function countAllElections() {
        $sql = "SELECT COUNT(*) as total FROM elections";
        $result = $this->query($sql);
        return isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
    }
    
    /**
     * Count active elections
     * 
     * @return int
     */
    public function countActiveElections() {
        $sql = "SELECT COUNT(*) as total FROM elections WHERE status = 'active'";
        $result = $this->query($sql);
        return isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
    }
}
