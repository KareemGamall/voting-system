<?php

namespace Tests\Unit\Models;

use Tests\Helpers\TestCase;
use Tests\Helpers\TestDataFactory;
require_once BASE_PATH . '/app/models/Election.php';

use Election;

/**
 * Election Model Tests
 */
class ElectionTest extends TestCase
{
    private $electionModel;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->electionModel = new Election();
    }
    
    /** @test */
    public function it_can_create_an_election()
    {
        $data = TestDataFactory::makeElection();
        
        $result = $this->electionModel->createElection($data);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('elections', [
            'election_name' => $data['election_name']
        ]);
    }
    
    /** @test */
    public function it_updates_status_to_active_when_start_date_is_reached()
    {
        // Create upcoming election
        $data = TestDataFactory::makeElection([
            'start_date' => date('Y-m-d H:i:s', strtotime('-1 minute')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'status' => 'upcoming'
        ]);
        
        $this->electionModel->createElection($data);
        
        // Trigger status update
        $this->electionModel->updateElectionStatuses();
        
        // Verify status changed to active
        $this->assertDatabaseHas('elections', [
            'election_name' => $data['election_name'],
            'status' => 'active'
        ]);
    }
    
    /** @test */
    public function it_updates_status_to_completed_when_end_date_is_passed()
    {
        // Create active election that has ended
        $data = TestDataFactory::makeElection([
            'start_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('-1 minute')),
            'status' => 'active'
        ]);
        
        $this->electionModel->createElection($data);
        
        // Trigger status update
        $this->electionModel->updateElectionStatuses();
        
        // Verify status changed to completed
        $this->assertDatabaseHas('elections', [
            'election_name' => $data['election_name'],
            'status' => 'completed'
        ]);
    }
    
    /** @test */
    public function it_retrieves_only_active_elections()
    {
        // Create various elections
        $upcoming = TestDataFactory::makeElection(['status' => 'upcoming']);
        $active = TestDataFactory::makeActiveElection();
        $completed = TestDataFactory::makeCompletedElection();
        
        $this->electionModel->createElection($upcoming);
        $this->electionModel->createElection($active);
        $this->electionModel->createElection($completed);
        
        $activeElections = $this->electionModel->getActiveElections();
        
        $this->assertCount(1, $activeElections);
        $this->assertEquals('active', $activeElections[0]['status']);
    }
    
    /** @test */
    public function it_can_check_if_election_is_active()
    {
        $activeData = TestDataFactory::makeActiveElection();
        $this->electionModel->createElection($activeData);
        
        $election = $this->electionModel->findWhere('election_name = :name', [
            'name' => $activeData['election_name']
        ]);
        
        $isActive = $this->electionModel->isActive($election['id']);
        
        $this->assertTrue($isActive);
    }
    
    /** @test */
    public function it_generates_unique_election_id()
    {
        $data1 = TestDataFactory::makeElection(['election_id' => null]);
        $data2 = TestDataFactory::makeElection(['election_id' => null]);
        
        unset($data1['election_id']);
        unset($data2['election_id']);
        
        $this->electionModel->createElection($data1);
        $this->electionModel->createElection($data2);
        
        $elections = $this->electionModel->getAll();
        $ids = array_column($elections, 'election_id');
        
        $this->assertEquals(count($ids), count(array_unique($ids)));
    }
}
