<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php $user = Session::getUser(); ?>

<div class="mb-4" style="margin-top: 2rem;">
    <h1><?= $data['title'] ?></h1>
    <p>Voter: <?= htmlspecialchars($user['name'] ?? 'User') ?></p>
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

<!-- Active Elections -->
<section class="elections-section" style="margin-bottom: 2rem;">
    <div class="section-title">
        <h2>Active Elections</h2>
    </div>

    <?php if (!empty($data['activeElections'])): ?>
    <div class="elections-grid">
        <?php foreach ($data['activeElections'] as $election): ?>
            <div class="election-card">
                <div class="election-card-header">
                    <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                    <span class="status-badge status-active">Active</span>
                </div>
                <p class="election-description"><?= htmlspecialchars($election['description'] ?? '') ?></p>
                <div class="election-details">
                    <span><strong>Start:</strong> <?= date('M d, Y h:i A', strtotime($election['start_date'])) ?></span>
                    <span><strong>End:</strong> <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?></span>
                </div>
                <div class="election-actions">
                    <?php if ($election['has_voted']): ?>
                        <span class="btn btn-secondary" style="width: 100%; cursor: default;">✓ Granted</span>
                    <?php else: ?>
                        <a href="<?= url('/voter/ballot') ?>?id=<?= $election['id'] ?>" class="btn btn-primary">Cast Your Vote</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="text-center p-4">
            <p>No active elections at this time.</p>
        </div>
    <?php endif; ?>
</section>

<!-- Upcoming Elections -->
<?php if (!empty($data['upcomingElections'])): ?>
<section class="elections-section mt-5">
    <div class="section-title">
        <h2>Upcoming Elections</h2>
    </div>
    <div class="elections-grid">
        <?php foreach ($data['upcomingElections'] as $election): ?>
            <div class="election-card">
                <div class="election-card-header">
                    <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                    <span class="status-badge status-upcoming">Upcoming</span>
                </div>
                <p class="election-description"><?= htmlspecialchars($election['description'] ?? 'N/A') ?></p>
                <div class="election-details">
                    <span><strong>Start:</strong> <?= date('M d, Y h:i A', strtotime($election['start_date'])) ?></span>
                    <span><strong>End:</strong> <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Voting Guidelines -->
<div class="feature-card mt-5" style="margin-bottom: 2rem;">
    <h3>Voting Guidelines</h3>
    <ul style="list-style-position: inside; color: var(--text-muted);">
        <li class="mb-1">You can only vote <strong>once per election</strong>.</li>
        <li class="mb-1">Make sure to review your selection before submitting.</li>
        <li class="mb-1">Your vote is <strong>anonymous and secure</strong>.</li>
        <li class="mb-1">Results will be available after the election closes.</li>
        <li class="mb-1">If you face any issues, contact the election administrator.</li>
    </ul>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
