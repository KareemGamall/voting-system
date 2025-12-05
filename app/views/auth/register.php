<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <h2>Create Account</h2>
    
    <form method="POST" action="<?php echo url('/register'); ?>">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                required 
                autocomplete="name"
                placeholder="Enter your full name"
                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
            >
        </div>
        
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
                autocomplete="new-password"
                placeholder="Create a password"
                minlength="6"
            >
            <div class="password-hint">Password must be at least 6 characters long</div>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required 
                autocomplete="new-password"
                placeholder="Confirm your password"
                minlength="6"
            >
        </div>
        
        <button type="submit" class="btn-submit">Register</button>
    </form>
    
    <div class="auth-links">
        <p>Already have an account? <a href="<?php echo url('/login'); ?>">Login here</a></p>
        <p><a href="<?php echo url('/'); ?>">Back to Home</a></p>
    </div>
</div>

<script>
    // Client-side password confirmation validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

