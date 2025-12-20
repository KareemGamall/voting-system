-- Update votes table to support multi-position voting
-- This allows voters to vote for multiple positions in the same election

-- Drop old unique constraint (one vote per election)
ALTER TABLE votes DROP INDEX unique_vote;

-- Add new unique constraint (one vote per candidate per election)
-- This prevents duplicate votes for the same candidate while allowing votes for different positions
ALTER TABLE votes ADD UNIQUE KEY unique_vote (election_id, voter_id, candidate_id);
