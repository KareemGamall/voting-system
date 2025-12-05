<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="hero <?php echo (isset($isLoggedIn) && $isLoggedIn) ? 'hero-logged-in' : 'hero-guest'; ?>">
    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
        <h1>Welcome <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h1>
        <p>You have 2 active elections available for voting.</p>
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
        <div class="elections-grid">
            <!-- Demo Election Card 1 -->
            <div class="election-card">
                <div class="election-card-header">
                    <h3>Student Council President</h3>
                    <span class="status-badge status-active">Active</span>
                </div>
                <p class="election-description">Choose the next student body president</p>
                <div class="election-details">
                    <span>Ends March 15, 2024 at 11:59 PM</span>
                    <span>Candidates 3</span>
                </div>
                <div class="election-actions">
                    <a href="#" class="btn btn-primary">Vote Now</a>
                </div>
            </div>
            
            <!-- Demo Election Card 2 -->
            <div class="election-card">
                <div class="election-card-header">
                    <h3>Class Representative</h3>
                    <span class="status-badge status-active">Active</span>
                </div>
                <p class="election-description">Select your class representative</p>
                <div class="election-details">
                    <span>Ends March 20, 2024 at 11:59 PM</span>
                    <span>Candidates 4</span>
                </div>
                <div class="election-actions">
                    <a href="#" class="btn btn-primary">Vote Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Closed Elections Section -->
    <section class="elections-section">
        <h2 class="section-title">Closed Elections</h2>
        <div class="elections-grid">
            <!-- Demo Closed Election Card -->
            <div class="election-card">
                <div class="election-card-header">
                    <h3>Library Committee Election</h3>
                </div>
                <div class="election-details">
                    <span>Completed on February 28, 2024</span>
                </div>
                <div class="election-actions">
                    <a href="#" class="btn btn-secondary">View Results</a>
                </div>
            </div>
        </div>
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

