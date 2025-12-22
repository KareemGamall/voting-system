<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - Voting System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>?v=<?php echo time(); ?>">
  <style>
      /* Page specific tab styles that didn't fit into generic admin.css easily */
      .tabs {
          display: flex;
          border-bottom: 2px solid var(--border-color);
          margin-bottom: 1.5rem;
      }
      .tab {
          padding: 0.75rem 1.5rem;
          cursor: pointer;
          background: transparent;
          border: none;
          border-bottom: 2px solid transparent;
          font-size: 1rem;
          font-weight: 500;
          color: var(--text-muted);
          transition: var(--transition-fast);
      }
      .tab:hover {
          color: var(--primary);
      }
      .tab.active {
          color: var(--primary);
          border-bottom-color: var(--primary);
      }
      .tab-content {
          display: none;
      }
      .tab-content.active {
          display: block;
          animation: fadeIn 0.3s ease-out;
      }
  </style>
</head>
<body>
  <div id="app">
    <?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main id="main-content">
      <section id="manageVotersPage" class="page active">
        <div class="card form-card">
          <h2>Manage Users</h2>
          
          <!-- Tabs -->
          <div class="tabs">
            <button class="tab active" onclick="switchTab('voters', this)">Voters</button>
            <button class="tab" onclick="switchTab('admins', this)">Admins</button>
          </div>

          <!-- Voters Tab -->
          <div id="votersTab" class="tab-content active">
            <h3>Manage Voters</h3>
            <div class="voter-input-grid">
              <input type="text" id="voterName" placeholder="Full Name">
              <input type="text" id="voterEmail" placeholder="Email">
              <button onclick="addVoterAjax()" class="btn btn-primary" style="background-color: var(--success); width: 100%;">Add Voter</button>
            </div>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">
              Default password: <strong>voter123</strong> (user should change it after first login)
            </p>
            <div id="votersList">
              <?php if (!empty($data['voters'])): ?>
                <?php foreach ($data['voters'] as $voter): ?>
                  <div class="voter-item">
                    <span><?= htmlspecialchars($voter['name']) ?> (<?= htmlspecialchars($voter['email']) ?>)</span>
                    <button onclick="removeVoterAjax(<?= $voter['id'] ?>)" class="btn btn-secondary btn-small">Remove</button>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>No voters found.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Admins Tab -->
          <div id="adminsTab" class="tab-content">
            <h3>Manage Admins</h3>
            <div class="voter-input-grid" style="grid-template-columns: 1fr 1fr 1fr auto;">
              <input type="text" id="adminName" placeholder="Full Name">
              <input type="text" id="adminEmail" placeholder="Email">
              <input type="password" id="adminPassword" placeholder="Password (optional)">
              <button onclick="addAdminAjax()" class="btn btn-primary" style="background-color: var(--success); width: 100%;">Add Admin</button>
            </div>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">
              If password is not provided, default password will be: <strong>admin123</strong>
            </p>
            <div id="adminsList">
              <?php if (!empty($data['admins'])): ?>
                <?php foreach ($data['admins'] as $admin): ?>
                  <div class="admin-item">
                    <span><?= htmlspecialchars($admin['name']) ?> (<?= htmlspecialchars($admin['email']) ?>)</span>
                    <button onclick="removeAdminAjax(<?= $admin['id'] ?>)" class="btn btn-secondary btn-small">Remove</button>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>No admins found.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // ===== Configuration =====
    const BASE_URL = '<?= BASE_URL ?>';

    // ===== Tabs =====
    function switchTab(tabName, buttonElement) {
      // Update tab buttons
      document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
      });
      if (buttonElement) {
        buttonElement.classList.add('active');
      }
      
      // Update tab content
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      const targetTab = document.getElementById(tabName + 'Tab');
      if (targetTab) {
        targetTab.classList.add('active');
      }
    }

    // ===== Voters =====
    function addVoterAjax() {
      const nameEl = document.getElementById("voterName");
      const emailEl = document.getElementById("voterEmail");
      if (!nameEl || !emailEl) return;
      
      const name = nameEl.value.trim();
      const email = emailEl.value.trim();
      
      if (!name || !email) {
        alert("Enter name and email/ID.");
        return;
      }

      const formData = new FormData();
      formData.append('name', name);
      formData.append('email', email);

      fetch(BASE_URL + '/admin/add-voter', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Voter added successfully!");
          nameEl.value = "";
          emailEl.value = "";
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to add voter"));
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while adding voter");
      });
    }

    function removeVoterAjax(voterId) {
      if (!confirm("Remove this voter?")) return;

      const formData = new FormData();
      formData.append('voter_id', voterId);

      fetch(BASE_URL + '/admin/remove-voter', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Voter removed successfully!");
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to remove voter"));
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while removing voter");
      });
    }

    // ===== Admins =====
    function addAdminAjax() {
      const nameEl = document.getElementById("adminName");
      const emailEl = document.getElementById("adminEmail");
      const passwordEl = document.getElementById("adminPassword");
      if (!nameEl || !emailEl) return;
      
      const name = nameEl.value.trim();
      const email = emailEl.value.trim();
      const password = passwordEl ? passwordEl.value.trim() : '';

      if (!name || !email) {
        alert("Name and email are required.");
        return;
      }

      const formData = new FormData();
      formData.append('name', name);
      formData.append('email', email);
      if (password) {
        formData.append('password', password);
      }

      fetch(BASE_URL + '/admin/add-admin', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Admin added successfully!");
          nameEl.value = "";
          emailEl.value = "";
          if (passwordEl) passwordEl.value = "";
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to add admin"));
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while adding admin");
      });
    }

    function removeAdminAjax(adminId) {
      if (!confirm("Remove this admin? You cannot delete your own account.")) return;

      const formData = new FormData();
      formData.append('admin_id', adminId);

      fetch(BASE_URL + '/admin/remove-admin', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Admin removed successfully!");
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to remove admin"));
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while removing admin");
      });
    }
  </script>
</body>
</html>
