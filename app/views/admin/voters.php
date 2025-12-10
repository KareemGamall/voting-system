<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Voters - Voting System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* ===== Reset & Base ===== */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Roboto, sans-serif;
      background: #f4f6f8;
      color: #333;
      min-height: 100vh;
      display: flex;
    }

    /* ===== Layout ===== */
    #app {
      display: flex;
      width: 100%;
    }

    #sidebar {
      width: 240px;
      background: #2c3e50;
      color: #ecf0f1;
      display: flex;
      flex-direction: column;
      transition: width 0.3s ease;
    }

    #sidebar:hover {
      width: 260px;
    }

    #sidebar-header {
      padding: 20px;
      font-size: 20px;
      font-weight: bold;
      background: #1a252f;
      text-align: center;
    }

    #sidebar-nav {
      display: flex;
      flex-direction: column;
      padding: 10px;
    }

    #sidebar-nav a {
      color: #ecf0f1;
      text-decoration: none;
      padding: 12px 18px;
      margin: 6px 0;
      border-radius: 8px;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    #sidebar-nav a:hover {
      background: #34495e;
      transform: translateX(5px);
    }

    #sidebar-nav a.active {
      background: #3498db;
      font-weight: bold;
    }

    #back-home-link {
      margin-top: 20px;
      border-top: 1px solid #34495e;
      padding-top: 12px;
      color: #ecf0f1;
    }

    #back-home-link:hover {
      color: #3498db;
    }

    #main-content {
      flex: 1;
      padding: 30px;
      background: #f9fafc;
      overflow-y: auto;
    }

    /* ===== Page Transitions ===== */
    .page {
      display: none;
      animation: fadeIn 0.4s ease;
    }

    .page.active {
      display: block;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ===== Cards ===== */
    .card {
      background: #fff;
      border-radius: 14px;
      padding: 25px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
      margin-bottom: 30px;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    /* ===== Form Styles ===== */
    .form-card input,
    .form-card select,
    .form-card textarea {
      width: 100%;
      padding: 12px;
      margin-top: 6px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-card input:focus,
    .form-card select:focus,
    .form-card textarea:focus {
      border-color: #3498db;
      box-shadow: 0 0 6px rgba(52, 152, 219, 0.4);
      outline: none;
    }

    /* ===== Buttons ===== */
    button {
      cursor: pointer;
      border: none;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .btn-primary {
      background: #3498db;
      color: #fff;
      padding: 10px 18px;
      border-radius: 6px;
    }

    .btn-primary:hover {
      background: #2980b9;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: #bdc3c7;
      color: #333;
      padding: 10px 18px;
      border-radius: 6px;
    }

    .btn-secondary:hover {
      background: #95a5a6;
      transform: translateY(-2px);
    }

    .btn-success {
      background: #2ecc71;
      color: #fff;
      padding: 8px 14px;
      border-radius: 6px;
    }

    .btn-success:hover {
      background: #27ae60;
      transform: translateY(-2px);
    }

    .btn-small {
      padding: 6px 10px;
      font-size: 12px;
    }

    /* ===== Voter Items ===== */
    .voter-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f9f9f9;
      padding: 8px 12px;
      border-radius: 6px;
      margin-bottom: 8px;
      transition: background 0.3s ease;
    }

    .voter-item:hover {
      background: #eef2f7;
    }

    .voter-input-grid {
      display: grid;
      grid-template-columns: 1fr 1fr auto;
      gap: 10px;
      margin-bottom: 20px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
      #app {
        flex-direction: column;
      }

      #sidebar {
        width: 100%;
        flex-direction: row;
        overflow-x: auto;
      }

      #sidebar-nav {
        flex-direction: row;
        justify-content: space-around;
      }

      .voter-input-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div id="app">
    <?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main id="main-content">
      <section id="manageVotersPage" class="page active">
        <div class="card form-card">
          <h2>Manage Voters</h2>
          <div class="voter-input-grid">
            <input type="text" id="voterName" placeholder="Full Name">
            <input type="text" id="voterEmail" placeholder="Email or Student ID">
            <button onclick="addVoterAjax()" class="btn-success">Add Voter</button>
          </div>
          <div id="votersList">
            <?php if (!empty($data['voters'])): ?>
              <?php foreach ($data['voters'] as $voter): ?>
                <div class="voter-item">
                  <?= htmlspecialchars($voter['name']) ?> (<?= htmlspecialchars($voter['email']) ?>)
                  <button onclick="removeVoterAjax(<?= $voter['id'] ?>)" class="btn-secondary btn-small">Remove</button>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No voters found.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // ===== Configuration =====
    const BASE_URL = '<?= BASE_URL ?>';

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
  </script>
</body>
</html>
