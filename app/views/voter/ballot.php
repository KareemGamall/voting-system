<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-4">
    <h1><?= htmlspecialchars($data['election']['election_name']) ?></h1>
    <p class="text-muted"><?= htmlspecialchars($data['election']['description'] ?? 'No description available.') ?></p>
    
    <div class="election-details mt-3" style="display: inline-grid; grid-template-columns: auto auto; gap: 2rem; background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md);">
        <div>
            <strong class="text-muted small d-block">Start Date</strong>
            <span style="font-size: 1.1rem;"><?= date('M d, Y', strtotime($data['election']['start_date'])) ?></span>
            <span class="text-muted small"><?= date('h:i A', strtotime($data['election']['start_date'])) ?></span>
        </div>
        <div>
            <strong class="text-muted small d-block">End Date</strong>
            <span style="font-size: 1.1rem;"><?= date('M d, Y', strtotime($data['election']['end_date'])) ?></span>
            <span class="text-muted small"><?= date('h:i A', strtotime($data['election']['end_date'])) ?></span>
        </div>
    </div>
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
<div class="feature-card mb-4">
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
            <div class="mb-5">
                <h2 class="mb-4">Position: <?= htmlspecialchars($position) ?></h2>
                <div class="elections-grid">
                <?php foreach ($candidates as $candidate): ?>
                    <label class="election-card candidate-option" style="cursor: pointer; position: relative; display: block; overflow: hidden; height: 100%;">
                        <!-- Visual Selection Indicator -->
                        <div class="selection-indicator" style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--primary); opacity: 0; transition: opacity 0.2s;"></div>
                        
                        <div class="candidate-info text-center" style="padding-bottom: 4rem;">
                            <?php if (!empty($candidate['photo'])): ?>
                                <img src="<?= htmlspecialchars($candidate['photo']) ?>" 
                                     alt="<?= htmlspecialchars($candidate['name']) ?>" 
                                     class="candidate-photo mb-3"
                                     style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--bg-body); box-shadow: var(--shadow-sm);">
                            <?php else: ?>
                                <div class="candidate-photo mb-3" style="width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--bg-body); margin: 0 auto; box-shadow: var(--shadow-sm);">
                                    <span style="font-size: 3rem; color: var(--text-light); opacity: 0.5;">?</span>
                                </div>
                            <?php endif; ?>
                            
                            <h3 class="mb-1"><?= htmlspecialchars($candidate['name']) ?></h3>
                            
                            <?php if (!empty($candidate['party'])): ?>
                                <span class="badge" style="background: var(--bg-body); color: var(--text-muted); font-weight: normal; padding: 0.25rem 0.75rem; border-radius: 1rem;">
                                    <?= htmlspecialchars($candidate['party']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="election-actions" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem; background: linear-gradient(to top, var(--bg-card) 80%, transparent 100%);">
                            <div class="candidate-select-wrapper text-center">
                                <input type="radio" 
                                       name="candidates[<?= htmlspecialchars($position) ?>]" 
                                       value="<?= $candidate['id'] ?>" 
                                       class="candidate-radio"
                                       required
                                       style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                                <div class="mt-2 text-primary font-weight-bold selection-text" style="display: none; font-size: 0.9rem;">
                                    <i class="fas fa-check-circle"></i> Selected
                                </div>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Submit Button -->
        <div class="text-center mt-5 mb-5">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Submit Your Vote</button>
            <a href="<?= url('/') ?>" class="btn btn-secondary" style="padding: 1rem 3rem; font-size: 1.1rem; margin-left: 1rem;">Cancel</a>
        </div>
        
    <?php else: ?>
        <p>No candidates available for this election yet.</p>
    <?php endif; ?>
</form>

<style>
/* Improved candidate selection styling */
.candidate-option {
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    border: 2px solid transparent;
}
.candidate-option:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}
.candidate-option.selected {
    border-color: var(--primary);
    background-color: rgba(var(--primary-rgb), 0.02);
}
.candidate-option.selected .selection-indicator {
    opacity: 1;
}
.candidate-option.selected .selection-text {
    display: block !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ballotForm = document.getElementById('ballotForm');
    
    // Visual selection logic
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Find parent form group (position)
            const positionGroup = this.closest('.elections-grid');
            
            // Remove selected class from all cards in this group
            positionGroup.querySelectorAll('.candidate-option').forEach(card => {
                card.classList.remove('selected');
                const text = card.querySelector('.selection-text');
                if(text) text.style.display = 'none';
            });
            
            // Add selected class to current card
            const currentCard = this.closest('.candidate-option');
            if(currentCard) {
                currentCard.classList.add('selected');
                const text = currentCard.querySelector('.selection-text');
                if(text) text.style.display = 'block';
            }
        });
    });

    // Form submission logic
    let isSubmitting = false;
    
    if (ballotForm) {
        ballotForm.addEventListener('submit', function(e) {
            // Prevent double submission
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            // Mark as submitting
            isSubmitting = true;
            
            // Disable submit button
            const submitButton = ballotForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                const originalText = submitButton.textContent;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
                submitButton.style.opacity = '0.8';
                submitButton.style.cursor = 'not-allowed';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
