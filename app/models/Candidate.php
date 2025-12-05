<?php

require_once __DIR__ . '/../core/Model.php';

class Candidate extends Model {
    protected $table = 'candidates';
    
    private $candidateId;
    private $name;
    private $position;
    private $party;
    private $photo;
    private $voteCount;
    private $electionId;
    
    /**
     * Add a new candidate to an election
     * 
     * @param array $candidateData
     * @return bool
     */
    public function addCandidate($candidateData) {
        // Generate unique candidate ID
        if (!isset($candidateData['candidate_id'])) {
            $candidateData['candidate_id'] = $this->generateCandidateId();
        }
        
        // Set default vote count
        if (!isset($candidateData['vote_count'])) {
            $candidateData['vote_count'] = 0;
        }
        
        return $this->create($candidateData);
    }
    
    /**
     * Get all candidates for a specific election
     * 
     * @param int $electionId
     * @return array
     */
    public function getCandidatesByElection($electionId) {
        return $this->where('election_id = :election_id', ['election_id' => $electionId]);
    }
    
    /**
     * Get candidates by position within an election
     * 
     * @param int $electionId
     * @param string $position
     * @return array
     */
    public function getCandidatesByPosition($electionId, $position) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE election_id = :election_id 
                AND position = :position 
                ORDER BY name ASC";
        
        return $this->query($sql, [
            'election_id' => $electionId,
            'position' => $position
        ]);
    }
    
    /**
     * Update candidate vote count
     * 
     * @param int $id
     * @return bool
     */
    public function incrementVoteCount($id) {
        $sql = "UPDATE {$this->table} 
                SET vote_count = vote_count + 1 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Get candidate with vote count
     * 
     * @param int $id
     * @return array|false
     */
    public function getCandidateWithVotes($id) {
        $sql = "SELECT c.*, COUNT(v.id) as total_votes 
                FROM {$this->table} c
                LEFT JOIN votes v ON c.id = v.candidate_id
                WHERE c.id = :id
                GROUP BY c.id";
        
        $result = $this->query($sql, ['id' => $id]);
        return $result[0] ?? false;
    }
    
    /**
     * Get top candidates by vote count for an election
     * 
     * @param int $electionId
     * @param int $limit
     * @return array
     */
    public function getTopCandidates($electionId, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE election_id = :election_id 
                ORDER BY vote_count DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':election_id', $electionId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find candidate by candidate_id
     * 
     * @param string $candidateId
     * @return array|false
     */
    public function findByCandidateId($candidateId) {
        return $this->findWhere('candidate_id = :candidate_id', ['candidate_id' => $candidateId]);
    }
    
    /**
     * Upload candidate photo
     * 
     * @param int $id
     * @param string $photoPath
     * @return bool
     */
    public function updatePhoto($id, $photoPath) {
        return $this->update($id, ['photo' => $photoPath]);
    }
    
    /**
     * Delete candidate photo
     * 
     * @param int $id
     * @return bool
     */
    public function deletePhoto($id) {
        $candidate = $this->find($id);
        
        if ($candidate && !empty($candidate['photo'])) {
            $photoPath = __DIR__ . '/../../storage/uploads/candidate_photos/' . $candidate['photo'];
            
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
            
            return $this->update($id, ['photo' => null]);
        }
        
        return false;
    }
    
    /**
     * Get all positions in an election
     * 
     * @param int $electionId
     * @return array
     */
    public function getPositionsByElection($electionId) {
        $sql = "SELECT DISTINCT position FROM {$this->table} 
                WHERE election_id = :election_id 
                ORDER BY position ASC";
        
        return $this->query($sql, ['election_id' => $electionId]);
    }
    
    /**
     * Generate unique candidate ID
     * 
     * @return string
     */
    private function generateCandidateId() {
        return 'CAND-' . time() . '-' . rand(1000, 9999);
    }
    
    /**
     * Delete candidate (override to handle photo deletion)
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Delete photo first
        $this->deletePhoto($id);
        
        // Then delete the candidate record
        return parent::delete($id);
    }
}
