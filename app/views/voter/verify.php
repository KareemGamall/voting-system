<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Success Animation -->
<div class="container text-center mb-4">
    <div class="success-animation">
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
    </div>
    <h2 style="color: var(--success);">Vote Successfully Recorded!</h2>
    <p style="color: var(--text-muted); font-size: 1.1rem;">Thank you for participating in this election.</p>
</div>

<!-- Vote Details -->
<div class="container">
    <div class="card receipt-card" style="max-width: 1000px; margin: 0 auto; box-shadow: var(--shadow-lg, 0 10px 15px -3px rgba(0, 0, 0, 0.1));">
        <div class="receipt-body" style="padding: 3rem;">
            <!-- Election Details -->
            <div style="margin-bottom: 4.5rem;">
                <h3 style="color: var(--primary); border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
                    Election Details
                </h3>
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1.5rem 2rem; align-items: baseline;">
                    <div style="color: var(--text-muted); font-weight: 600;">Election</div>
                    <div style="font-size: 1.1rem; font-weight: 500;"><?= htmlspecialchars($data['voteData']['election']['election_name']) ?></div>
                    
                    <div style="color: var(--text-muted); font-weight: 600;">Description</div>
                    <div style="color: var(--text-main);"><?= htmlspecialchars($data['voteData']['election']['description'] ?? 'N/A') ?></div>
                </div>
            </div>

            <!-- Candidate Details -->
            <div style="margin-bottom: 4.5rem;">
                <h3 style="color: var(--primary); border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
                    Your Selection<?= !empty($data['voteData']['candidates']) && count($data['voteData']['candidates']) > 1 ? 's' : '' ?>
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php 
                // Handle both single and multiple candidates
                $candidates = $data['voteData']['candidates'] ?? [$data['voteData']['candidate']];
                foreach ($candidates as $candidate): 
                ?>
                    <div style="display: flex; align-items: center; gap: 1.5rem; padding: 1.25rem; background-color: var(--bg-body); border-radius: var(--radius-md); border-left: 5px solid var(--primary); box-shadow: var(--shadow-sm);">
                        <div>
                            <?php if (!empty($candidate['photo'])): ?>
                                <img src="<?= htmlspecialchars($candidate['photo']) ?>" 
                                     alt="<?= htmlspecialchars($candidate['name']) ?>" 
                                     style="width: 70px; height: 70px; object-fit: cover; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                <div style="width: 70px; height: 70px; border-radius: 50%; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #adb5bd;">
                                    ?
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.25rem; color: var(--text-main); font-size: 1.1rem;"><?= htmlspecialchars($candidate['name']) ?></h4>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; font-size: 0.9rem;">
                                <span>
                                    <strong style="color: var(--text-muted);">Position:</strong> <?= htmlspecialchars($candidate['position'] ?? 'N/A') ?>
                                </span>
                                <?php if (!empty($candidate['party'])): ?>
                                    <span>
                                        <strong style="color: var(--text-muted);">Party:</strong> <?= htmlspecialchars($candidate['party']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Timestamp -->
            <div class="mb-4">
                <h3 style="color: var(--primary); border-bottom: 2px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
                    Submission Time
                </h3>
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1.5rem 2rem; align-items: baseline;">
                    <div style="color: var(--text-muted); font-weight: 600;">Recorded At</div>
                    <div style="font-family: monospace; font-size: 1.1rem;"><?= date('F d, Y · h:i:s A', strtotime($data['voteData']['vote_time'])) ?></div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="flash-message flash-info" style="margin-top: 2rem;">
                <div>
                   <strong>Security & Privacy Notice</strong>
                   <ul style="margin: 0.5rem 0 0 1.2rem; font-size: 0.9rem;">
                        <li>Your vote has been securely recorded and encrypted.</li>
                        <li>Your identity is kept anonymous and separate from your vote choice.</li>
                        <li>This confirmation is for your records only.</li>
                        <li>Results will be published after the election closes.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="container text-center mb-5 no-print" style="margin-top: 3rem;">
    <a href="<?= url('/voter/dashboard') ?>" class="btn btn-primary" style="padding: 1rem 2rem;">Return to Dashboard</a>
    <button onclick="window.print()" class="btn btn-secondary" style="padding: 1rem 2rem; margin-left: 1rem;">Print Receipt</button>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
