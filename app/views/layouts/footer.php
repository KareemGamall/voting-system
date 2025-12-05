    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Voting System. All rights reserved.</p>
    </footer>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.classList.add('fade-out');
                    setTimeout(function() {
                        message.remove();
                    }, 500); // Remove from DOM after fade-out animation
                }, 5000); // Hide after 5 seconds
            });
        });
    </script>
</body>
</html>

