<?php

namespace Tests\Feature;

use Tests\Helpers\TestCase;
use Tests\Helpers\TestDataFactory;
require_once BASE_PATH . '/app/models/Election.php';
require_once BASE_PATH . '/app/models/Candidate.php';
require_once BASE_PATH . '/app/models/Vote.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Result.php';

use Election;
use Candidate;
use Vote;
use User;
use Result;

/**
 * Complete Voting Flow Integration Tests
 */
class VotingFlowTest extends TestCase
{
    /** @test */
    public function complete_voting_flow_works_correctly()
    {
        $electionModel = new Election();
        $candidateModel = new Candidate();
        $voteModel = new Vote();
        $userModel = new User();
        $resultModel = new Result();
        
        // 1. Admin creates election
        $electionData = TestDataFactory::makeActiveElection([
            'election_name' => 'Presidential Election 2025'
        ]);
        $electionModel->createElection($electionData);
        $election = $electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $this->assertNotNull($election);
        $this->assertEquals('active', $election['status']);
        
        // 2. Admin adds candidates
        $candidatesData = [
            TestDataFactory::makeCandidate($election['id'], ['name' => 'John Doe', 'party' => 'Party A']),
            TestDataFactory::makeCandidate($election['id'], ['name' => 'Jane Smith', 'party' => 'Party B']),
            TestDataFactory::makeCandidate($election['id'], ['name' => 'Bob Wilson', 'party' => 'Independent'])
        ];
        
        foreach ($candidatesData as $candidateData) {
            $candidateModel->create($candidateData);
        }
        
        $candidates = $candidateModel->getCandidatesByElection($election['id']);
        $this->assertCount(3, $candidates);
        
        // 3. Voters register and vote
        $voters = [];
        for ($i = 0; $i < 10; $i++) {
            $userData = TestDataFactory::makeUser([
                'name' => "Voter " . ($i + 1)
            ]);
            $userModel->register($userData);
            $voters[] = $userModel->findByEmail($userData['email']);
        }
        
        // Distribute votes: 5 for John, 3 for Jane, 2 for Bob
        $voteDistribution = [
            0 => [0, 1, 2, 3, 4], // John Doe
            1 => [5, 6, 7],       // Jane Smith
            2 => [8, 9]           // Bob Wilson
        ];
        
        foreach ($voteDistribution as $candidateIndex => $voterIndices) {
            foreach ($voterIndices as $voterIndex) {
                $result = $voteModel->castVote(
                    $election['id'],
                    $candidates[$candidateIndex]['id'],
                    $voters[$voterIndex]['id']
                );
                $this->assertTrue($result);
            }
        }
        
        // 4. Verify vote counts
        $johnVotes = $voteModel->countVotesByCandidate($candidates[0]['id']);
        $janeVotes = $voteModel->countVotesByCandidate($candidates[1]['id']);
        $bobVotes = $voteModel->countVotesByCandidate($candidates[2]['id']);
        
        $this->assertEquals(5, $johnVotes);
        $this->assertEquals(3, $janeVotes);
        $this->assertEquals(2, $bobVotes);
        
        // 5. Generate results
        $resultModel->calculateResults($election['id']);
        
        // 6. Verify results and winner
        $results = $resultModel->getElectionResults($election['id']);
        $winner = $resultModel->getWinnerByPosition($election['id'], 'President');
        
        $this->assertCount(3, $results);
        $this->assertNotNull($winner);
        $this->assertEquals('John Doe', $winner['candidate_name']);
        $this->assertEquals(5, $winner['vote_count']);
        $this->assertEquals(50, $winner['percentage']);
        
        // 7. Verify no ties
        $this->assertFalse($results[0]['is_tied']);
        $this->assertTrue($results[0]['is_winner']);
    }
    
    /** @test */
    public function prevents_double_voting_for_same_candidate()
    {
        $electionModel = new Election();
        $candidateModel = new Candidate();
        $voteModel = new Vote();
        $userModel = new User();
        
        // Setup election and candidate
        $electionData = TestDataFactory::makeActiveElection();
        $electionModel->createElection($electionData);
        $election = $electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidateData = TestDataFactory::makeCandidate($election['id']);
        $candidateModel->create($candidateData);
        $candidate = $candidateModel->findWhere('name = :name', [
            'name' => $candidateData['name']
        ]);
        
        // Create voter
        $userData = TestDataFactory::makeUser();
        $userModel->register($userData);
        $user = $userModel->findByEmail($userData['email']);
        
        // First vote succeeds
        $firstVote = $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        $this->assertTrue($firstVote);
        
        // Second vote fails
        $secondVote = $voteModel->castVote($election['id'], $candidate['id'], $user['id']);
        $this->assertFalse($secondVote);
        
        // Only one vote recorded
        $voteCount = $this->getRecordCount('votes', [
            'voter_id' => $user['id'],
            'candidate_id' => $candidate['id']
        ]);
        $this->assertEquals(1, $voteCount);
    }
    
    /** @test */
    public function handles_tie_scenario_correctly()
    {
        $electionModel = new Election();
        $candidateModel = new Candidate();
        $voteModel = new Vote();
        $userModel = new User();
        $resultModel = new Result();
        
        // Create election with two candidates
        $electionData = TestDataFactory::makeCompletedElection();
        $electionModel->createElection($electionData);
        $election = $electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidate1 = TestDataFactory::makeCandidate($election['id'], ['name' => 'Candidate A']);
        $candidate2 = TestDataFactory::makeCandidate($election['id'], ['name' => 'Candidate B']);
        
        $candidateModel->create($candidate1);
        $candidateModel->create($candidate2);
        
        $candidates = $candidateModel->getCandidatesByElection($election['id']);
        
        // Cast equal votes (3 each)
        for ($i = 0; $i < 6; $i++) {
            $userData = TestDataFactory::makeUser();
            $userModel->register($userData);
            $user = $userModel->findByEmail($userData['email']);
            
            $candidateIndex = $i < 3 ? 0 : 1;
            $voteModel->castVote($election['id'], $candidates[$candidateIndex]['id'], $user['id']);
        }
        
        // Generate results
        $resultModel->calculateResults($election['id']);
        
        // Verify tie
        $results = $resultModel->getElectionResults($election['id']);
        $winner = $resultModel->getWinnerByPosition($election['id'], 'President');
        
        $this->assertTrue($results[0]['is_tied']);
        $this->assertTrue($results[1]['is_tied']);
        $this->assertNull($winner); // No winner when tied
    }
}
