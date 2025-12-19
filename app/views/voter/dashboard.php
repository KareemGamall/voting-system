<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php $user = Session::getUser(); ?>

<div style="margin-bottom: 2rem;">
    <h1 style="margin-bottom: 0.5rem;"><?= $data['title'] ?></h1>
    <p style="color: #666; margin-top: 0;">Voter: <?= htmlspecialchars($user['name'] ?? 'User') ?></p>
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
<section style="margin-bottom: 3rem;">
    <h2 style="margin-bottom: 1.5rem;">Active Elections</h2>

    <?php if (!empty($data['activeElections'])): ?>
    <div class="elections-grid">
        <?php foreach ($data['activeElections'] as $election): ?>
            <div class="election-card">
                <div class="election-card-header">
                    <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                </div>
                <p class="election-description"><?= htmlspecialchars($election['description'] ?? '') ?></p>
                <div class="election-details">
                    <p><strong>Start:</strong> <?= date('M d, Y h:i A', strtotime($election['start_date'])) ?></p>
                    <p><strong>End:</strong> <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?></p>
                </div>
                <div class="election-actions">
                    <?php if ($election['has_voted']): ?>
                        <span class="btn btn-secondary" style="cursor: default;">✓ Already Voted</span>
                    <?php else: ?>
                        <a href="<?= url('/voter/ballot') ?>?id=<?= $election['id'] ?>" class="btn btn-primary">Cast Your Vote</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p>No active elections at this time. Check back later!</p>
    <?php endif; ?>
</section>

<!-- Upcoming Elections -->
<?php if (!empty($data['upcomingElections'])): ?>
<section style="margin-bottom: 3rem;">
    <h2 style="margin-bottom: 1.5rem;">Upcoming Elections</h2>
    <div class="elections-grid">
        <?php foreach ($data['upcomingElections'] as $election): ?>
            <div class="election-card">
                <div class="election-card-header">
                    <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                    <span class="status-badge">Upcoming</span>
                </div>
                <p class="election-description"><?= htmlspecialchars($election['description'] ?? 'N/A') ?></p>
                <div class="election-details">
                    <p><strong>Start:</strong> <?= date('M d, Y h:i A', strtotime($election['start_date'])) ?></p>
                    <p><strong>End:</strong> <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Voting Guidelines -->
<div class="feature-card" style="margin-top: 3rem;">
    <h3>Voting Guidelines</h3>
    <ul>
        <li>You can only vote <strong>once per election</strong>.</li>
        <li>Make sure to review your selection before submitting.</li>
        <li>Your vote is <strong>anonymous and secure</strong>.</li>
        <li>Results will be available after the election closes.</li>
        <li>If you face any issues, contact the election administrator.</li>
    </ul>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
