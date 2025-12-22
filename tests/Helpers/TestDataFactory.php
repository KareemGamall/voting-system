<?php

namespace Tests\Helpers;

/**
 * Test Data Factory
 * Creates test data for tests
 */
class TestDataFactory
{
    /**
     * Create test user data
     */
    public static function makeUser(array $overrides = []): array
    {
        return array_merge([
            'user_id' => 'USR-TEST-' . uniqid(),
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@test.com',
            'password' => password_hash('TestPass123!', PASSWORD_BCRYPT),
            'is_admin' => 0,
            'is_voter' => 1
        ], $overrides);
    }
    
    /**
     * Create test election data
     */
    public static function makeElection(array $overrides = []): array
    {
        $now = date('Y-m-d H:i:s');
        $startDate = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $endDate = date('Y-m-d H:i:s', strtotime('+2 days'));
        
        return array_merge([
            'election_id' => 'ELEC-TEST-' . uniqid(),
            'election_name' => 'Test Election ' . uniqid(),
            'description' => 'Test election description',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'upcoming',
            'created_at' => $now
        ], $overrides);
    }
    
    /**
     * Create active election
     */
    public static function makeActiveElection(array $overrides = []): array
    {
        return self::makeElection(array_merge([
            'start_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'status' => 'active'
        ], $overrides));
    }
    
    /**
     * Create completed election
     */
    public static function makeCompletedElection(array $overrides = []): array
    {
        return self::makeElection(array_merge([
            'start_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'status' => 'completed'
        ], $overrides));
    }
    
    /**
     * Create test candidate data
     */
    public static function makeCandidate(int $electionId, array $overrides = []): array
    {
        return array_merge([
            'election_id' => $electionId,
            'name' => 'Test Candidate ' . uniqid(),
            'position' => 'President',
            'party' => 'Test Party',
            'bio' => 'Test candidate biography',
            'vote_count' => 0
        ], $overrides);
    }
    
    /**
     * Create test vote data
     */
    public static function makeVote(int $electionId, int $candidateId, int $voterId, array $overrides = []): array
    {
        return array_merge([
            'election_id' => $electionId,
            'candidate_id' => $candidateId,
            'voter_id' => $voterId,
            'voted_at' => date('Y-m-d H:i:s')
        ], $overrides);
    }
    
    /**
     * Create multiple candidates for an election
     */
    public static function makeCandidates(int $electionId, int $count = 3, string $position = 'President'): array
    {
        $candidates = [];
        for ($i = 0; $i < $count; $i++) {
            $candidates[] = self::makeCandidate($electionId, [
                'name' => "Candidate " . ($i + 1),
                'position' => $position,
                'party' => "Party " . ($i + 1)
            ]);
        }
        return $candidates;
    }
}
