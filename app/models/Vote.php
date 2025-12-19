<?php

require_once __DIR__ . '/../core/Model.php';

class Vote extends Model {
    protected $table = 'votes';
    
    private $voteId;
    private $electionId;
    private $candidateId;
    private $voterId;
    private $voteTime;
    
    /**
     * Cast a vote
     * 
     * @param int $electionId
     * @param int $candidateId
     * @param int $voterId
     * @return bool
     */
    public function castVote($electionId, $candidateId, $voterId) {
        // Generate unique vote ID
        $voteId = $this->generateVoteId();
        
        // Create vote record
        $voteData = [
            'vote_id' => $voteId,
            'election_id' => $electionId,
            'candidate_id' => $candidateId,
            'voter_id' => $voterId,
            'vote_time' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->create($voteData);
        
        // Update candidate vote count
        if ($result) {
            require_once __DIR__ . '/Candidate.php';
            $candidateModel = new Candidate();
            $candidateModel->incrementVoteCount($candidateId);
        }
        
        return $result;
    }
    
    /**
     * Check if a voter has already voted in an election
     * Returns true if voter has cast ANY vote in this election
     * 
     * @param int $electionId
     * @param int $voterId
     * @return bool
     */
    public function hasVoted($electionId, $voterId) {
        $result = $this->findWhere(
            'election_id = :election_id AND voter_id = :voter_id',
            [
                'election_id' => $electionId,
                'voter_id' => $voterId
            ]
        );
        
        return $result !== false;
    }
    
    /**
     * Check if voter has voted for a specific candidate
     * 
     * @param int $electionId
     * @param int $candidateId
     * @param int $voterId
     * @return bool
     */
    public function hasVotedForCandidate($electionId, $candidateId, $voterId) {
        $result = $this->findWhere(
            'election_id = :election_id AND voter_id = :voter_id AND candidate_id = :candidate_id',
            [
                'election_id' => $electionId,
                'voter_id' => $voterId,
                'candidate_id' => $candidateId
            ]
        );
        
        return $result !== false;
    }
    
    /**
     * Get all votes for a specific election
     * 
     * @param int $electionId
     * @return array
     */
    public function getVotesByElection($electionId) {
        return $this->where('election_id = :election_id', ['election_id' => $electionId]);
    }
    
    /**
     * Get all votes for a specific candidate
     * 
     * @param int $candidateId
     * @return array
     */
    public function getVotesByCandidate($candidateId) {
        return $this->where('candidate_id = :candidate_id', ['candidate_id' => $candidateId]);
    }
    
    /**
     * Get votes cast by a specific voter
     * 
     * @param int $voterId
     * @return array
     */
    public function getVotesByVoter($voterId) {
        return $this->where('voter_id = :voter_id', ['voter_id' => $voterId]);
    }
    
    /**
     * Get vote details with related information
     * 
     * @param int $id
     * @return array|false
     */
    public function getVoteDetails($id) {
        $sql = "SELECT 
                    v.*,
                    e.election_name,
                    c.name as candidate_name,
                    c.position,
                    u.name as voter_name
                FROM {$this->table} v
                JOIN elections e ON v.election_id = e.id
                JOIN candidates c ON v.candidate_id = c.id
                JOIN users u ON v.voter_id = u.id
                WHERE v.id = :id";
        
        $result = $this->query($sql, ['id' => $id]);
        return $result[0] ?? false;
    }
    
    /**
     * Count total votes in an election
     * 
     * @param int $electionId
     * @return int
     */
    public function countVotesByElection($electionId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE election_id = :election_id";
        $result = $this->query($sql, ['election_id' => $electionId]);
        
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Count total votes for a candidate
     * 
     * @param int $candidateId
     * @return int
     */
    public function countVotesByCandidate($candidateId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE candidate_id = :candidate_id";
        $result = $this->query($sql, ['candidate_id' => $candidateId]);
        
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Get voter's vote in a specific election
     * 
     * @param int $electionId
     * @param int $voterId
     * @return array|false
     */
    public function getVoterElectionVote($electionId, $voterId) {
        return $this->findWhere(
            'election_id = :election_id AND voter_id = :voter_id',
            [
                'election_id' => $electionId,
                'voter_id' => $voterId
            ]
        );
    }
    
    /**
     * Get voting statistics for an election
     * 
     * @param int $electionId
     * @return array
     */
    public function getElectionVotingStats($electionId) {
        $sql = "SELECT 
                    COUNT(DISTINCT v.voter_id) as total_voters,
                    COUNT(v.id) as total_votes,
                    MIN(v.vote_time) as first_vote_time,
                    MAX(v.vote_time) as last_vote_time
                FROM {$this->table} v
                WHERE v.election_id = :election_id";
        
        $result = $this->query($sql, ['election_id' => $electionId]);
        return $result[0] ?? null;
    }
    
    /**
     * Get votes grouped by hour for an election
     * 
     * @param int $electionId
     * @return array
     */
    public function getVotesByHour($electionId) {
        $sql = "SELECT 
                    DATE_FORMAT(vote_time, '%Y-%m-%d %H:00:00') as hour,
                    COUNT(*) as vote_count
                FROM {$this->table}
                WHERE election_id = :election_id
                GROUP BY hour
                ORDER BY hour ASC";
        
        return $this->query($sql, ['election_id' => $electionId]);
    }
    
    /**
     * Generate unique vote ID
     * 
     * @return string
     */
    private function generateVoteId() {
        return 'VOTE-' . time() . '-' . rand(10000, 99999);
    }
    
    /**
     * Verify vote integrity (check if vote exists and is valid)
     * 
     * @param string $voteId
     * @return bool
     */
    public function verifyVote($voteId) {
        $vote = $this->findWhere('vote_id = :vote_id', ['vote_id' => $voteId]);
        return $vote !== false;
    }
}
