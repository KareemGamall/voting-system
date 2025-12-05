<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <h2>Login</h2>
    
    <form method="POST" action="<?php echo url('/login'); ?>">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                autocomplete="email"
                placeholder="Enter your email"
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
            >
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                autocomplete="current-password"
                placeholder="Enter your password"
            >
        </div>
        
        <button type="submit" class="btn-submit">Login</button>
    </form>
    
    <div class="auth-links">
        <p>Don't have an account? <a href="<?php echo url('/register'); ?>">Register here</a></p>
        <p><a href="<?php echo url('/'); ?>">Back to Home</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

