<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php $user = Session::getUser(); ?>

<style>
.results-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.results-table thead tr {
    background: var(--primary-subtle);
}

.results-table th,
.results-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
}

.results-table th {
    font-weight: 600;
    text-align: left;
}

.results-table tbody tr:hover {
    background: var(--hover-bg, rgba(99, 102, 241, 0.05));
}

.winner-badge {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
    text-transform: uppercase;
}

.tied-badge {
    background: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
    text-transform: uppercase;
}

body.dark-mode .tied-badge {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

/* Dark mode support */
body.dark-mode .results-table tbody tr:hover {
    background: rgba(99, 102, 241, 0.1);
}
</style>

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

<!-- Completed Elections with Results -->
<?php if (!empty($data['completedElections'])): ?>
<section class="elections-section mt-5" style="margin-top: 2rem;">
    <div class="section-title">
        <h2>Completed Elections - Results</h2>
    </div>
    
    <?php foreach ($data['completedElections'] as $election): ?>
        <div class="election-card" style="margin-bottom: 2rem;">
            <div class="election-card-header">
                <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                <span class="status-badge status-completed">Completed</span>
            </div>
            <p class="election-description"><?= htmlspecialchars($election['description'] ?? 'N/A') ?></p>
            <div class="election-details" style="margin-bottom: 1.5rem;">
                <span><strong>Start:</strong> <?= date('M d, Y h:i A', strtotime($election['start_date'])) ?></span>
                <span><strong>End:</strong> <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?></span>
            </div>
            
            <?php if (!empty($election['results'])): ?>
                <!-- Winners Section -->
                <?php if (!empty($election['winners'])): ?>
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                        <h4 style="color: white; margin-bottom: 1rem; font-size: 1.2rem;">🏆 Winners</h4>
                        <?php foreach ($election['winners'] as $winner): ?>
                            <div style="background: rgba(255,255,255,0.15); padding: 12px; margin-bottom: 8px; border-radius: 8px; backdrop-filter: blur(10px);">
                                <strong><?= htmlspecialchars($winner['position']) ?>:</strong>
                                <?= htmlspecialchars($winner['candidate_name']) ?>
                                <?php if ($winner['party']): ?>
                                    (<?= htmlspecialchars($winner['party']) ?>)
                                <?php endif; ?>
                                - <?= htmlspecialchars($winner['vote_count']) ?> votes
                                (<?= number_format($winner['percentage'], 1) ?>%)
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Results Table -->
                <div style="overflow-x: auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Candidate</th>
                                <th>Party</th>
                                <th style="text-align: center;">Votes</th>
                                <th style="text-align: center;">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($election['results'] as $result): ?>
                                <tr>
                                    <td><?= htmlspecialchars($result['position']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($result['candidate_name']) ?>
                                        <?php if (!empty($result['is_tied'])): ?>
                                            <span class="tied-badge">Tied</span>
                                        <?php elseif (!empty($result['is_winner'])): ?>
                                            <span class="winner-badge">Winner</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($result['party'] ?? 'Independent') ?></td>
                                    <td style="text-align: center; font-weight: 600;"><?= htmlspecialchars($result['vote_count']) ?></td>
                                    <td style="text-align: center;"><?= number_format($result['percentage'], 1) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; background: var(--bg-subtle); border-radius: 8px; color: var(--text-secondary);">
                    <p>Results not yet generated for this election.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- Voting Guidelines & Results -->
<div class="feature-card mt-5" style="margin-bottom: 2rem;">
    <h3>Voting Guidelines</h3>
    <ul style="list-style-position: inside; color: var(--text-muted);">
        <li class="mb-1">You can only vote <strong>once per election</strong>.</li>
        <li class="mb-1">Make sure to review your selection before submitting.</li>
        <li class="mb-1">Your vote is <strong>anonymous and secure</strong>.</li>
        <li class="mb-1">Results will be available after the election closes.</li>
        <li class="mb-1">If you face any issues, contact the election administrator.</li>
    </ul>
    
    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
        <a href="<?= url('/voter/results') ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
            View All Election Results
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
