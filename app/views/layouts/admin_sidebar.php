<aside id="sidebar">
  <div id="sidebar-header">Admin Panel</div>
  <nav id="sidebar-nav">
    <a href="<?= BASE_URL ?>/admin/dashboard" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') !== false) ? 'class="active"' : '' ?>>Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/elections" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/elections') !== false) ? 'class="active"' : '' ?>>Create Election</a>
    <a href="<?= BASE_URL ?>/admin/voters" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/voters') !== false) ? 'class="active"' : '' ?>>Manage Voters</a>
    <a href="<?= BASE_URL ?>/admin/monitor" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/monitor') !== false) ? 'class="active"' : '' ?>>Live Monitor</a>
    <a href="<?= BASE_URL ?>/admin/results" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/results') !== false) ? 'class="active"' : '' ?>>Results</a>
    <a href="<?= BASE_URL ?>/" id="back-home-link">← Back to Home</a>
  </nav>
  </nav>
</aside>

<!-- Admin Dark Mode Toggle -->
<button id="admin-dark-mode-toggle" class="btn btn-secondary" style="position: fixed; bottom: 20px; right: 20px; z-index: 10000; border-radius: 50%; width: 50px; height: 50px; padding: 0; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-xl);">
    <span id="admin-dark-mode-icon">◐</span>
</button>
<script>
    (function() {
        const toggleBtn = document.getElementById('admin-dark-mode-toggle');
        const icon = document.getElementById('admin-dark-mode-icon');
        const body = document.body;
        
        // Initial Check
        if (localStorage.getItem('darkMode') === 'true') {
            body.classList.add('dark-mode');
            icon.innerText = '○';
        }
        
        toggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            icon.innerText = isDark ? '○' : '◐';
        });
    })();
</script>

