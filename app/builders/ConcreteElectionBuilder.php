<?php

require_once __DIR__ . '/../models/Election.php';
require_once __DIR__ . '/../models/Candidate.php';
require_once __DIR__ . '/ElectionBuilder.php';

/**
 * ConcreteElectionBuilder Class
 * 
 * Concrete implementation of ElectionBuilder interface
 * Handles the step-by-step construction of Election objects
 */
class ConcreteElectionBuilder implements ElectionBuilder {
    
    private $election;
    private $candidates = [];
    
    /**
     * Constructor - Initialize a new Election object
     */
    public function __construct() {
        $this->election = new Election();
    }
    
    /**
     * Set the election ID
     * 
     * @param string $id
     * @return ConcreteElectionBuilder
     */
    public function setElectionID($id) {
        $this->election->electionId = $id;
        return $this;
    }
    
    /**
     * Set the election name
     * 
     * @param string $name
     * @return ConcreteElectionBuilder
     */
    public function setElectionName($name) {
        $this->election->electionName = $name;
        return $this;
    }
    
    /**
     * Set the election start date
     * 
     * @param DateTime $date
     * @return ConcreteElectionBuilder
     */
    public function setStartDate($date) {
        $this->election->startDate = $date;
        return $this;
    }
    
    /**
     * Set the election end date
     * 
     * @param DateTime $date
     * @return ConcreteElectionBuilder
     */
    public function setEndDate($date) {
        $this->election->endDate = $date;
        return $this;
    }
    
    /**
     * Set the election status
     * 
     * @param string $status
     * @return ConcreteElectionBuilder
     */
    public function setStatus($status) {
        $this->election->status = $status;
        return $this;
    }
    
    /**
     * Set the election description
     * 
     * @param string $description
     * @return ConcreteElectionBuilder
     */
    public function setDescription($description) {
        $this->election->description = $description;
        return $this;
    }
    
    /**
     * Add a candidate to the election
     * 
     * @param Candidate $candidate
     * @return ConcreteElectionBuilder
     */
    public function addCandidate($candidate) {
        $this->candidates[] = $candidate;
        return $this;
    }
    
    /**
     * Build and return the Election object with all configured properties
     * 
     * @return Election
     */
    public function build() {
        $this->election->candidates = $this->candidates;
        return $this->election;
    }
}
