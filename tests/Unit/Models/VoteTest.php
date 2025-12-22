<?php

namespace Tests\Unit\Models;

use Tests\Helpers\TestCase;
use Tests\Helpers\TestDataFactory;
require_once BASE_PATH . '/app/models/Election.php';
require_once BASE_PATH . '/app/models/Candidate.php';
require_once BASE_PATH . '/app/models/Vote.php';
require_once BASE_PATH . '/app/models/User.php';

use Election;
use Candidate;
use Vote;
use User;

/**
 * Vote Model Tests
 */
class VoteTest extends TestCase
{
    private $voteModel;
    private $electionModel;
    private $candidateModel;
    private $userModel;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->voteModel = new Vote();
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->userModel = new User();
    }
    
    /** @test */
    public function it_can_cast_a_vote()
    {
        // Create test data
        $electionData = TestDataFactory::makeActiveElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidateData = TestDataFactory::makeCandidate($election['id']);
        $this->candidateModel->create($candidateData);
        $candidate = $this->candidateModel->findWhere('name = :name', [
            'name' => $candidateData['name']
        ]);
        
        $userData = TestDataFactory::makeUser();
        $this->userModel->register($userData);
        $user = $this->userModel->findByEmail($userData['email']);
        
        // Cast vote
        $result = $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('votes', [
            'election_id' => $election['id'],
            'candidate_id' => $candidate['id'],
            'voter_id' => $user['id']
        ]);
    }
    
    /** @test */
    public function it_prevents_duplicate_votes_for_same_candidate()
    {
        // Create test data
        $electionData = TestDataFactory::makeActiveElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidateData = TestDataFactory::makeCandidate($election['id']);
        $this->candidateModel->create($candidateData);
        $candidate = $this->candidateModel->findWhere('name = :name', [
            'name' => $candidateData['name']
        ]);
        
        $userData = TestDataFactory::makeUser();
        $this->userModel->register($userData);
        $user = $this->userModel->findByEmail($userData['email']);
        
        // Cast first vote
        $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        
        // Try to vote again for same candidate
        $result = $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        
        $this->assertFalse($result);
        $this->assertEquals(1, $this->getRecordCount('votes', [
            'voter_id' => $user['id'],
            'candidate_id' => $candidate['id']
        ]));
    }
    
    /** @test */
    public function it_allows_voting_for_different_positions()
    {
        // Create election
        $electionData = TestDataFactory::makeActiveElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        // Create candidates for different positions
        $president = TestDataFactory::makeCandidate($election['id'], ['position' => 'President']);
        $vicePresident = TestDataFactory::makeCandidate($election['id'], ['position' => 'Vice President']);
        
        $this->candidateModel->create($president);
        $this->candidateModel->create($vicePresident);
        
        $presidentCandidate = $this->candidateModel->findWhere('name = :name', ['name' => $president['name']]);
        $vicePresidentCandidate = $this->candidateModel->findWhere('name = :name', ['name' => $vicePresident['name']]);
        
        // Create voter
        $userData = TestDataFactory::makeUser();
        $this->userModel->register($userData);
        $user = $this->userModel->findByEmail($userData['email']);
        
        // Vote for both positions
        $vote1 = $this->voteModel->castVote($election['id'], $presidentCandidate['id'], $user['id']);
        $vote2 = $this->voteModel->castVote($election['id'], $vicePresidentCandidate['id'], $user['id']);
        
        $this->assertTrue($vote1);
        $this->assertTrue($vote2);
        $this->assertEquals(2, $this->getRecordCount('votes', ['voter_id' => $user['id']]));
    }
    
    /** @test */
    public function it_correctly_counts_votes_by_candidate()
    {
        // Create election and candidate
        $electionData = TestDataFactory::makeActiveElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidateData = TestDataFactory::makeCandidate($election['id']);
        $this->candidateModel->create($candidateData);
        $candidate = $this->candidateModel->findWhere('name = :name', [
            'name' => $candidateData['name']
        ]);
        
        // Create multiple voters and cast votes
        for ($i = 0; $i < 5; $i++) {
            $userData = TestDataFactory::makeUser();
            $this->userModel->register($userData);
            $user = $this->userModel->findByEmail($userData['email']);
            $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        }
        
        $voteCount = $this->voteModel->countVotesByCandidate($candidate['id']);
        
        $this->assertEquals(5, $voteCount);
    }
    
    /** @test */
    public function it_checks_if_voter_has_voted()
    {
        // Create test data
        $electionData = TestDataFactory::makeActiveElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidateData = TestDataFactory::makeCandidate($election['id']);
        $this->candidateModel->create($candidateData);
        $candidate = $this->candidateModel->findWhere('name = :name', [
            'name' => $candidateData['name']
        ]);
        
        $userData = TestDataFactory::makeUser();
        $this->userModel->register($userData);
        $user = $this->userModel->findByEmail($userData['email']);
        
        // Before voting
        $this->assertFalse($this->voteModel->hasVoted($election['id'], $user['id']));
        
        // After voting
        $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        $this->assertTrue($this->voteModel->hasVoted($election['id'], $user['id']));
    }
}
