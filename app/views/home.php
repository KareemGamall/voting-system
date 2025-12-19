<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="hero <?php echo (isset($isLoggedIn) && $isLoggedIn) ? 'hero-logged-in' : 'hero-guest'; ?>">
    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
        <h1>Welcome <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h1>
        <?php if ($isVoter): ?>
            <p>You have <?php echo count($activeElections ?? []); ?> active elections available for voting.</p>
            <div class="cta-buttons">
                <a href="<?php echo url('/voter/dashboard'); ?>" class="btn btn-primary">Go to Voter Dashboard</a>
            </div>
        <?php elseif ($isAdmin): ?>
            <p>Manage elections, candidates, voters and monitor voting activities.</p>
        <?php endif; ?>
    <?php else: ?>
        <h1>Welcome to Voting System</h1>
        <p>Secure, Transparent, and Easy-to-Use Online Voting Platform</p>
        <div class="cta-buttons">
            <a href="<?php echo url('/login'); ?>" class="btn btn-primary">Get Started</a>
            <a href="<?php echo url('/register'); ?>" class="btn btn-secondary">Create Account</a>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($isLoggedIn) && $isLoggedIn): ?>
    <!-- Available Elections Section -->
    <section class="elections-section">
        <h2 class="section-title">Available Elections</h2>
        <?php if (!empty($activeElections)): ?>
            <div class="elections-grid">
                <?php foreach ($activeElections as $election): ?>
                    <div class="election-card">
                        <div class="election-card-header">
                            <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                            <span class="status-badge status-active">Active</span>
                        </div>
                        <p class="election-description"><?= htmlspecialchars($election['description'] ?? 'No description') ?></p>
                        <div class="election-details">
                            <span>Ends <?= date('F d, Y \\a\\t g:i A', strtotime($election['end_date'])) ?></span>
                        </div>
                        <?php if ($isVoter): ?>
                            <div class="election-actions">
                                <?php if (in_array($election['id'], $votedElectionIds ?? [])): ?>
                                    <span class="btn btn-secondary" style="cursor: default;">✓ Already Voted</span>
                                <?php else: ?>
                                    <a href="<?= url('/voter/ballot') ?>?id=<?= $election['id'] ?>" class="btn btn-primary">Vote Now</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No active elections available at this time.</p>
        <?php endif; ?>
    </section>
<?php else: ?>
    <!-- Features Section - Only shown when not logged in -->
    <div class="features">
        <div class="feature-card">
            <h3>🔒 Secure</h3>
            <p>Your votes are encrypted and protected with industry-standard security measures.</p>
        </div>
        <div class="feature-card">
            <h3>✅ Transparent</h3>
            <p>Real-time results and transparent voting process ensure trust and integrity.</p>
        </div>
        <div class="feature-card">
            <h3>📱 Easy to Use</h3>
            <p>Simple and intuitive interface makes voting accessible to everyone.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

