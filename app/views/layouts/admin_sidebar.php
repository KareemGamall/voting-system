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
</aside>

