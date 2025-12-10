<?php

/**
 * ElectionBuilder Interface
 * 
 * Defines the contract for building Election objects
 * Implements the Builder Design Pattern
 */
interface ElectionBuilder {
    
    /**
     * Set the election ID
     * 
     * @param string $id
     * @return ElectionBuilder
     */
    public function setElectionID($id);
    
    /**
     * Set the election name
     * 
     * @param string $name
     * @return ElectionBuilder
     */
    public function setElectionName($name);
    
    /**
     * Set the election start date
     * 
     * @param DateTime $date
     * @return ElectionBuilder
     */
    public function setStartDate($date);
    
    /**
     * Set the election end date
     * 
     * @param DateTime $date
     * @return ElectionBuilder
     */
    public function setEndDate($date);
    
    /**
     * Set the election status
     * 
     * @param string $status
     * @return ElectionBuilder
     */
    public function setStatus($status);
    
    /**
     * Set the election description
     * 
     * @param string $description
     * @return ElectionBuilder
     */
    public function setDescription($description);
    
    /**
     * Add a candidate to the election
     * 
     * @param Candidate $candidate
     * @return ElectionBuilder
     */
    public function addCandidate($candidate);
    
    /**
     * Build and return the Election object
     * 
     * @return Election
     */
    public function build();
}
