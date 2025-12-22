<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="hero <?php echo (isset($isLoggedIn) && $isLoggedIn) ? 'hero-logged-in' : 'hero-guest'; ?>">
    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
        <h1>Welcome <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h1>
        <?php if ($isVoter): ?>
            <?php 
            $activeCount = 0; 
            if (!empty($activeElections)) {
                foreach ($activeElections as $ae) {
                    if (($ae['status'] ?? '') === 'active') {
                        $activeCount++;
                    }
                }
            }
            ?>
            <p>You have <?php echo $activeCount; ?> active elections available for voting.</p>
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
    <section class="elections-section" style="margin-bottom: 2rem;">
        <h2 class="section-title">Available Elections</h2>
        <?php if (!empty($activeElections)): ?>
            <div class="elections-grid">
                <?php foreach ($activeElections as $election): ?>
                    <?php 
                        $status = strtolower($election['status'] ?? 'upcoming');
                        $badgeClass = 'status-' . ($status === 'active' ? 'active' : ($status === 'completed' ? 'completed' : 'upcoming'));
                        $badgeLabel = ucfirst($status);
                    ?>
                    <div class="election-card">
                        <div class="election-card-header">
                            <h3><?= htmlspecialchars($election['election_name']) ?></h3>
                            <span class="status-badge <?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        </div>
                        <p class="election-description"><?= htmlspecialchars($election['description'] ?? 'No description') ?></p>
                        <div class="election-details">
                            <span>Ends <?= date('F d, Y \\a\\t g:i A', strtotime($election['end_date'])) ?></span>
                            <?php if ($status === 'upcoming'): ?>
                                <span>Starts <?= date('F d, Y \\a\\t g:i A', strtotime($election['start_date'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isVoter): ?>
                            <div class="election-actions">
                                <?php if ($status !== 'active'): ?>
                                    <span class="btn btn-secondary" style="cursor: default;"><?= $status === 'upcoming' ? 'Upcoming' : 'Unavailable'; ?></span>
                                <?php elseif (in_array($election['id'], $votedElectionIds ?? [])): ?>
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
    <!-- How It Works Section - Only shown when not logged in -->
    <div class="how-it-works">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create Account</h3>
                <p>Register with your email and create a secure password to get started.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Browse Elections</h3>
                <p>View all active elections and see detailed candidate information.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Cast Your Vote</h3>
                <p>Submit your vote securely and view results when the election closes.</p>
            </div>
        </div>
        <div class="cta-section-bottom">
            <p>Ready to participate in democratic voting?</p>
            <div class="cta-buttons">
                <a href="<?= url('/register') ?>" class="btn btn-primary">Get Started</a>
                <a href="<?= url('/login') ?>" class="btn btn-secondary">Already have an account?</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

