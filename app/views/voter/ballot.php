<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="margin-bottom: 0.75rem;"><?= htmlspecialchars($data['election']['election_name']) ?></h1>
    <p style="color: #666; margin-bottom: 0.5rem;"><?= htmlspecialchars($data['election']['description'] ?? 'No description available.') ?></p>
    <p style="color: #666; font-size: 0.9rem;"><strong>Start:</strong> <?= date('M d, Y h:i A', strtotime($data['election']['start_date'])) ?> | <strong>End:</strong> <?= date('M d, Y h:i A', strtotime($data['election']['end_date'])) ?></p>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="flash-message flash-<?= $_SESSION['flash_type'] ?? 'info' ?>">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

<!-- Voting Instructions -->
<div class="feature-card" style="margin-bottom: 2rem;">
    <h3>Important Instructions</h3>
    <ul>
        <li>Review all candidates carefully before making your selection.</li>
        <li>You can only vote <strong>once</strong> in this election.</li>
        <li>Once submitted, your vote <strong>cannot be changed</strong>.</li>
        <li>Your vote is anonymous and will be kept confidential.</li>
    </ul>
</div>

<!-- Ballot Form -->
<form id="ballotForm" method="POST" action="<?= url('/voter/cast-vote') ?>">
    <input type="hidden" name="election_id" value="<?= $data['election']['id'] ?>">
    
    <?php if (!empty($data['candidatesByPosition'])): ?>
        <?php foreach ($data['candidatesByPosition'] as $position => $candidates): ?>
            <div style="margin-bottom: 2.5rem;">
                <h2 style="margin-bottom: 1.25rem;">Position: <?= htmlspecialchars($position) ?></h2>
                <div class="elections-grid">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="election-card candidate-option">
                        <div class="candidate-info">
                            <?php if (!empty($candidate['photo'])): ?>
                                <img src="<?= htmlspecialchars($candidate['photo']) ?>" 
                                     alt="<?= htmlspecialchars($candidate['name']) ?>" 
                                     class="candidate-photo"
                                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($candidate['name']) ?></h3>
                            
                            <?php if (!empty($candidate['party'])): ?>
                                <p><strong>Party:</strong> <?= htmlspecialchars($candidate['party']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="election-actions">
                            <label class="candidate-select">
                                <input type="radio" 
                                       name="candidates[<?= htmlspecialchars($position) ?>]" 
                                       value="<?= $candidate['id'] ?>" 
                                       required>
                                <span class="btn btn-primary">Select This Candidate</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Submit Button -->
        <div style="text-align: center; margin: 2rem 0;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Submit Your Vote</button>
            <a href="<?= url('/') ?>" class="btn btn-secondary" style="padding: 1rem 3rem; font-size: 1.1rem; margin-left: 1rem;">Cancel</a>
        </div>
        
    <?php else: ?>
        <p>No candidates available for this election yet.</p>
    <?php endif; ?>
</form>

<style>
.candidate-option {
    text-align: center;
}

.candidate-info {
    margin-bottom: 1rem;
}

.candidate-select {
    cursor: pointer;
}

.candidate-select input[type="radio"] {
    display: none;
}

.candidate-select input[type="radio"]:checked + .btn {
    background-color: #28a745;
    border-color: #28a745;
}

.candidate-option:has(input[type="radio"]:checked) {
    border-color: #28a745;
    border-width: 2px;
    background-color: #f0fff4;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
