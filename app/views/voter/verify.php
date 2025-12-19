<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Success Animation -->
<div class="row mb-4">
    <div class="col-12 text-center">
        <div class="success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
        <h2 class="text-success mt-3">Vote Successfully Recorded!</h2>
        <p class="lead text-muted">Thank you for participating in this election.</p>
    </div>
</div>

            <!-- Vote Details -->
            <div class="row mb-4">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-lg border-success">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-receipt"></i> Vote Confirmation Receipt</h4>
                        </div>
                        <div class="card-body p-4">
                            <!-- Election Details -->
                            <div class="mb-4">
                                <h5 class="text-success border-bottom pb-2">
                                    <i class="fas fa-vote-yea"></i> Election Details
                                </h5>
                                <div class="row mt-3">
                                    <div class="col-sm-4 text-muted">
                                        <strong>Election:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?= htmlspecialchars($data['voteData']['election']['election_name']) ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-4 text-muted">
                                        <strong>Description:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?= htmlspecialchars($data['voteData']['election']['description'] ?? 'N/A') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Candidate Details -->
                            <div class="mb-4">
                                <h5 class="text-success border-bottom pb-2">
                                    <i class="fas fa-user-check"></i> Your Selection<?= !empty($data['voteData']['candidates']) && count($data['voteData']['candidates']) > 1 ? 's' : '' ?>
                                </h5>
                                
                                <?php 
                                // Handle both single and multiple candidates
                                $candidates = $data['voteData']['candidates'] ?? [$data['voteData']['candidate']];
                                foreach ($candidates as $candidate): 
                                ?>
                                    <div class="row mt-3 align-items-center" style="padding: 1rem; background-color: #f8f9fa; border-radius: 0.5rem; margin-bottom: 1rem;">
                                        <div class="col-auto">
                                            <?php if (!empty($candidate['photo'])): ?>
                                                <img src="<?= htmlspecialchars($candidate['photo']) ?>" 
                                                     alt="<?= htmlspecialchars($candidate['name']) ?>" 
                                                     class="img-fluid rounded-circle"
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 80px; height: 80px; background-color: #e9ecef;">
                                                    <i class="fas fa-user fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <h4 class="mb-1 text-primary">
                                                <?= htmlspecialchars($candidate['name']) ?>
                                            </h4>
                                            <p class="mb-0 text-muted">
                                                <i class="fas fa-briefcase"></i> 
                                                <strong>Position:</strong> <?= htmlspecialchars($candidate['position'] ?? 'N/A') ?>
                                            </p>
                                            <?php if (!empty($candidate['party'])): ?>
                                                <p class="mb-0 text-muted">
                                                    <i class="fas fa-flag"></i> 
                                                    <strong>Party:</strong> <?= htmlspecialchars($candidate['party']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Timestamp -->
                            <div class="mb-3">
                                <h5 class="text-success border-bottom pb-2">
                                    <i class="fas fa-clock"></i> Submission Time
                                </h5>
                                <div class="row mt-3">
                                    <div class="col-sm-4 text-muted">
                                        <strong>Recorded At:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <?= date('F d, Y h:i:s A', strtotime($data['voteData']['vote_time'])) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Notice -->
                            <div class="alert alert-info mt-4" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-shield-alt"></i> Security & Privacy Notice
                                </h6>
                                <ul class="mb-0 small">
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
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <a href="<?= url('/voter/dashboard') ?>" class="btn btn-primary btn-lg px-5">
                        Return to Dashboard
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-lg px-5 ms-3">
                        Print Receipt
                    </button>
                </div>
            </div>

            <!-- Important Reminders -->
            <div class="row mb-4">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Important Reminders
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li><strong>One Vote Only:</strong> You have already voted in this election and cannot vote again.</li>
                                <li><strong>Results:</strong> Election results will be available after the voting period ends.</li>
                                <li><strong>Questions:</strong> If you have any concerns, please contact the election administrator.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

<!-- Custom Styles for Success Animation -->
<style>
    .success-animation {
        display: inline-block;
        margin: 20px 0;
    }

    .checkmark {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #28a745;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #28a745;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }

    .checkmark-circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #28a745;
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        stroke: #28a745;
        stroke-width: 1.5;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {
        0%, 100% {
            transform: none;
        }
        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #28a745;
        }
    }

    /* Print styles */
    @media print {
        .sidebar, .btn, .alert-info {
            display: none !important;
        }
        
        .card {
            border: 2px solid #000 !important;
            box-shadow: none !important;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
