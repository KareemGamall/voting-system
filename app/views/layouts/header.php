<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Voting System'; ?></title>
    <link rel="stylesheet" href="<?php echo url('/css/style.css'); ?>">
    <?php if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0): ?>
        <link rel="stylesheet" href="<?php echo url('/css/admin.css'); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/admin') !== 0): ?>
    <nav>
        <div class="container">
            <a href="<?php echo url('/'); ?>" class="logo">Voting System</a>
            <ul class="nav-links">
                <?php 
                Session::start();
                $isLoggedIn = Session::isLoggedIn();
                $user = Session::getUser();
                ?>
                <?php if ($isLoggedIn): ?>
                    <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
                        <li><a href="<?php echo url('/admin/dashboard'); ?>">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <?php if (isset($user['is_voter']) && $user['is_voter'] == 1): ?>
                        <?php if (strpos($_SERVER['REQUEST_URI'], '/voter/dashboard') !== false): ?>
                            <li><a href="<?php echo url('/'); ?>">Home</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo url('/voter/dashboard'); ?>">Voter Dashboard</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li><a href="<?php echo url('/logout'); ?>" class="btn btn-secondary">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo url('/login'); ?>" class="btn btn-primary">Login</a></li>
                    <li><a href="<?php echo url('/register'); ?>" class="btn btn-secondary">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container">
        <?php if (isset($flash) && $flash): ?>
            <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

