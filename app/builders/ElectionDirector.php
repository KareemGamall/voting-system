<?php

require_once __DIR__ . '/ElectionBuilder.php';

/**
 * ElectionDirector Class
 * 
 * Orchestrates the construction of Election objects using the Builder pattern
 * Provides predefined construction algorithms for common election types
 */
class ElectionDirector {
    
    /**
     * Construct a simple election with minimal configuration
     * 
     * @param ElectionBuilder $builder
     * @param array $electionData Array with keys: name, description, startDate, endDate
     * @return Election
     */
    public function constructSimpleElection(ElectionBuilder $builder, $electionData) {
        $builder->setElectionName($electionData['name'] ?? 'Simple Election');
        $builder->setDescription($electionData['description'] ?? '');
        $builder->setStartDate($electionData['startDate'] ?? date('Y-m-d H:i:s'));
        $builder->setEndDate($electionData['endDate'] ?? date('Y-m-d H:i:s', strtotime('+7 days')));
        $builder->setStatus('pending');
        
        return $builder->build();
    }
    
    /**
     * Construct a full-featured election with all configurations
     * 
     * @param ElectionBuilder $builder
     * @param array $electionData Array with keys: id, name, description, startDate, endDate, status, candidates
     * @return Election
     */
    public function constructFullElection(ElectionBuilder $builder, $electionData) {
        if (isset($electionData['id'])) {
            $builder->setElectionID($electionData['id']);
        }
        
        $builder->setElectionName($electionData['name'] ?? 'Full Election');
        $builder->setDescription($electionData['description'] ?? '');
        $builder->setStartDate($electionData['startDate'] ?? date('Y-m-d H:i:s'));
        $builder->setEndDate($electionData['endDate'] ?? date('Y-m-d H:i:s', strtotime('+7 days')));
        $builder->setStatus($electionData['status'] ?? 'pending');
        
        // Add candidates if provided
        if (isset($electionData['candidates']) && is_array($electionData['candidates'])) {
            foreach ($electionData['candidates'] as $candidateData) {
                $candidate = new Candidate();
                $candidate->name = $candidateData['name'] ?? '';
                $candidate->position = $candidateData['position'] ?? '';
                $candidate->party = $candidateData['party'] ?? '';
                
                $builder->addCandidate($candidate);
            }
        }
        
        return $builder->build();
    }
}
