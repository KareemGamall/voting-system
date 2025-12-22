<?php

namespace Tests\Unit\Models;

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
 * Result Model Tests
 * Tests result calculation and tie detection
 */
class ResultTest extends TestCase
{
    private $resultModel;
    private $voteModel;
    private $electionModel;
    private $candidateModel;
    private $userModel;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->resultModel = new Result();
        $this->voteModel = new Vote();
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->userModel = new User();
    }
    
    /** @test */
    public function it_calculates_results_correctly()
    {
        // Create election and candidates
        $electionData = TestDataFactory::makeCompletedElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidates = TestDataFactory::makeCandidates($election['id'], 3);
        foreach ($candidates as $candidateData) {
            $this->candidateModel->create($candidateData);
        }
        
        $candidateRecords = $this->candidateModel->getCandidatesByElection($election['id']);
        
        // Cast votes: 5 for first, 3 for second, 2 for third
        $voteCounts = [5, 3, 2];
        foreach ($candidateRecords as $index => $candidate) {
            for ($i = 0; $i < $voteCounts[$index]; $i++) {
                $userData = TestDataFactory::makeUser();
                $this->userModel->register($userData);
                $user = $this->userModel->findByEmail($userData['email']);
                $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
            }
        }
        
        // Calculate results
        $this->resultModel->calculateResults($election['id']);
        
        // Verify results
        $results = $this->resultModel->getElectionResults($election['id']);
        
        $this->assertCount(3, $results);
        $this->assertEquals(5, $results[0]['vote_count']);
        $this->assertEquals(50, $results[0]['percentage']); // 5/10 * 100
        $this->assertTrue($results[0]['is_winner']);
        $this->assertFalse($results[0]['is_tied']);
    }
    
    /** @test */
    public function it_detects_ties_correctly()
    {
        // Create election and candidates
        $electionData = TestDataFactory::makeCompletedElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidates = TestDataFactory::makeCandidates($election['id'], 3);
        foreach ($candidates as $candidateData) {
            $this->candidateModel->create($candidateData);
        }
        
        $candidateRecords = $this->candidateModel->getCandidatesByElection($election['id']);
        
        // Cast equal votes for first two candidates: 5, 5, 2
        $voteCounts = [5, 5, 2];
        foreach ($candidateRecords as $index => $candidate) {
            for ($i = 0; $i < $voteCounts[$index]; $i++) {
                $userData = TestDataFactory::makeUser();
                $this->userModel->register($userData);
                $user = $this->userModel->findByEmail($userData['email']);
                $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
            }
        }
        
        // Calculate results
        $this->resultModel->calculateResults($election['id']);
        
        // Verify tie detection
        $results = $this->resultModel->getElectionResults($election['id']);
        
        $this->assertTrue($results[0]['is_tied']);
        $this->assertTrue($results[1]['is_tied']);
        $this->assertFalse($results[0]['is_winner']);
        $this->assertFalse($results[1]['is_winner']);
        $this->assertFalse($results[2]['is_tied']);
    }
    
    /** @test */
    public function it_returns_null_winner_when_tied()
    {
        // Create election and tied candidates
        $electionData = TestDataFactory::makeCompletedElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidates = TestDataFactory::makeCandidates($election['id'], 2);
        foreach ($candidates as $candidateData) {
            $this->candidateModel->create($candidateData);
        }
        
        $candidateRecords = $this->candidateModel->getCandidatesByElection($election['id']);
        
        // Cast equal votes
        foreach ($candidateRecords as $candidate) {
            for ($i = 0; $i < 3; $i++) {
                $userData = TestDataFactory::makeUser();
                $this->userModel->register($userData);
                $user = $this->userModel->findByEmail($userData['email']);
                $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
            }
        }
        
        // Calculate results
        $this->resultModel->calculateResults($election['id']);
        
        // Get winner
        $winner = $this->resultModel->getWinnerByPosition($election['id'], 'President');
        
        $this->assertNull($winner);
    }
    
    /** @test */
    public function it_returns_winner_when_no_tie()
    {
        // Create election and candidates
        $electionData = TestDataFactory::makeCompletedElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidates = TestDataFactory::makeCandidates($election['id'], 2);
        foreach ($candidates as $candidateData) {
            $this->candidateModel->create($candidateData);
        }
        
        $candidateRecords = $this->candidateModel->getCandidatesByElection($election['id']);
        
        // Cast different votes: 5 and 3
        $voteCounts = [5, 3];
        foreach ($candidateRecords as $index => $candidate) {
            for ($i = 0; $i < $voteCounts[$index]; $i++) {
                $userData = TestDataFactory::makeUser();
                $this->userModel->register($userData);
                $user = $this->userModel->findByEmail($userData['email']);
                $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
            }
        }
        
        // Calculate results
        $this->resultModel->calculateResults($election['id']);
        
        // Get winner
        $winner = $this->resultModel->getWinnerByPosition($election['id'], 'President');
        
        $this->assertNotNull($winner);
        $this->assertEquals(5, $winner['vote_count']);
    }
    
    /** @test */
    public function it_calculates_percentages_correctly()
    {
        // Create election and candidates
        $electionData = TestDataFactory::makeCompletedElection();
        $this->electionModel->createElection($electionData);
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $electionData['election_name']
        ]);
        
        $candidates = TestDataFactory::makeCandidates($election['id'], 3);
        foreach ($candidates as $candidateData) {
            $this->candidateModel->create($candidateData);
        }
        
        $candidateRecords = $this->candidateModel->getCandidatesByElection($election['id']);
        
        // Cast votes: 6, 3, 1 (total 10)
        $voteCounts = [6, 3, 1];
        foreach ($candidateRecords as $index => $candidate) {
            for ($i = 0; $i < $voteCounts[$index]; $i++) {
                $userData = TestDataFactory::makeUser();
                $this->userModel->register($userData);
                $user = $this->userModel->findByEmail($userData['email']);
                $this->voteModel->castVote($election['id'], $candidate['id'], $user['id']);
            }
        }
        
        // Calculate results
        $this->resultModel->calculateResults($election['id']);
        
        $results = $this->resultModel->getElectionResults($election['id']);
        
        $this->assertEquals(60, $results[0]['percentage']); // 6/10 * 100
        $this->assertEquals(30, $results[1]['percentage']); // 3/10 * 100
        $this->assertEquals(10, $results[2]['percentage']); // 1/10 * 100
    }
}
