<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Generate Results') ?> - Voting System</title>
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

    .card h2 {
      color: #2c3e50;
      margin-bottom: 20px;
      font-size: 24px;
    }

    /* ===== Form Elements ===== */
    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #555;
    }

    .select-styled {
      padding: 12px;
      border-radius: 6px;
      border: 1px solid #ddd;
      font-size: 14px;
      width: 100%;
      max-width: 500px;
      transition: border-color 0.3s ease;
    }

    .select-styled:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary {
      background: #3498db;
      color: white;
    }

    .btn-primary:hover {
      background: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
    }

    .btn-secondary {
      background: #95a5a6;
      color: white;
    }

    .btn-secondary:hover {
      background: #7f8c8d;
    }

    .btn-success {
      background: #27ae60;
      color: white;
    }

    .btn-success:hover {
      background: #229954;
    }

    /* ===== Election List ===== */
    .election-list {
      margin-top: 20px;
    }

    .election-item {
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .election-item:hover {
      background: #e9ecef;
    }

    .election-info h3 {
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .election-info p {
      color: #7f8c8d;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .election-status {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-completed {
      background: #d4edda;
      color: #155724;
    }

    .status-active {
      background: #d1ecf1;
      color: #0c5460;
    }

    .status-upcoming {
      background: #fff3cd;
      color: #856404;
    }

    /* ===== Messages ===== */
    .alert {
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert-info {
      background: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }

    /* ===== Loading Spinner ===== */
    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #3498db;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      display: inline-block;
      margin-left: 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .hidden {
      display: none;
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
    }
  </style>
</head>
<body>
  <div id="app">
    <?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main id="main-content">
      <div class="card">
        <h2>Generate Election Results</h2>
        
        <div id="alertContainer"></div>
        
        <div class="form-group">
          <label for="electionSelect">Select Election:</label>
          <select id="electionSelect" class="select-styled">
            <option value="">-- Select an Election --</option>
            <?php if (!empty($elections)): ?>
              <?php foreach ($elections as $election): ?>
                <option value="<?= $election['id'] ?>" 
                        data-status="<?= htmlspecialchars($election['status'] ?? '') ?>">
                  <?= htmlspecialchars($election['election_name'] ?? 'Untitled') ?> 
                  (<?= htmlspecialchars($election['status'] ?? 'unknown') ?>)
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div id="electionDetails" class="hidden">
          <div class="election-item">
            <div class="election-info">
              <h3 id="electionName"></h3>
              <p id="electionDescription"></p>
              <p><strong>Start:</strong> <span id="electionStart"></span></p>
              <p><strong>End:</strong> <span id="electionEnd"></span></p>
              <p><strong>Status:</strong> <span id="electionStatus" class="election-status"></span></p>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button id="generateBtn" class="btn btn-primary" onclick="generateResults()">
            Generate Results
          </button>
          <span id="loadingSpinner" class="spinner hidden"></span>
        </div>

        <div class="form-group">
          <a href="<?= BASE_URL ?>/admin/results" class="btn btn-secondary">
            View All Results
          </a>
        </div>
      </div>

      <div class="card">
        <h3>Available Elections</h3>
        <div class="election-list" id="electionList">
          <?php if (!empty($elections)): ?>
            <?php foreach ($elections as $election): ?>
              <div class="election-item">
                <div class="election-info">
                  <h3><?= htmlspecialchars($election['election_name'] ?? 'Untitled') ?></h3>
                  <p><?= htmlspecialchars($election['description'] ?? 'No description') ?></p>
                  <p>
                    <strong>Start:</strong> <?= htmlspecialchars($election['start_date'] ?? 'N/A') ?> | 
                    <strong>End:</strong> <?= htmlspecialchars($election['end_date'] ?? 'N/A') ?>
                  </p>
                  <span class="election-status status-<?= htmlspecialchars($election['status'] ?? 'unknown') ?>">
                    <?= htmlspecialchars($election['status'] ?? 'unknown') ?>
                  </span>
                </div>
                <button class="btn btn-primary" onclick="selectElection(<?= $election['id'] ?>)">
                  Select
                </button>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No elections available.</p>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <script>
    const BASE_URL = '<?= BASE_URL ?>';
    const elections = <?= json_encode($elections ?? []) ?>;

    function selectElection(electionId) {
      document.getElementById('electionSelect').value = electionId;
      loadElectionDetails(electionId);
    }

    function loadElectionDetails(electionId) {
      const election = elections.find(e => e.id == electionId);
      if (!election) return;

      document.getElementById('electionName').textContent = election.election_name || 'Untitled';
      document.getElementById('electionDescription').textContent = election.description || 'No description';
      document.getElementById('electionStart').textContent = election.start_date || 'N/A';
      document.getElementById('electionEnd').textContent = election.end_date || 'N/A';
      
      const statusEl = document.getElementById('electionStatus');
      statusEl.textContent = election.status || 'unknown';
      statusEl.className = 'election-status status-' + (election.status || 'unknown');
      
      document.getElementById('electionDetails').classList.remove('hidden');
    }

    document.getElementById('electionSelect').addEventListener('change', function() {
      const electionId = this.value;
      if (electionId) {
        loadElectionDetails(electionId);
      } else {
        document.getElementById('electionDetails').classList.add('hidden');
      }
    });

    function showAlert(message, type = 'info') {
      const container = document.getElementById('alertContainer');
      container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
      setTimeout(() => {
        container.innerHTML = '';
      }, 5000);
    }

    function generateResults() {
      const electionId = document.getElementById('electionSelect').value;
      
      if (!electionId) {
        showAlert('Please select an election first.', 'error');
        return;
      }

      const generateBtn = document.getElementById('generateBtn');
      const spinner = document.getElementById('loadingSpinner');
      
      generateBtn.disabled = true;
      spinner.classList.remove('hidden');

      fetch(`${BASE_URL}/admin/generate-results`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `election_id=${electionId}`
      })
      .then(response => response.json())
      .then(data => {
        generateBtn.disabled = false;
        spinner.classList.add('hidden');

        if (data.success) {
          showAlert('Results generated successfully! You can now view them in the Results page.', 'success');
          setTimeout(() => {
            window.location.href = `${BASE_URL}/admin/results`;
          }, 2000);
        } else {
          showAlert(data.message || 'Failed to generate results.', 'error');
        }
      })
      .catch(error => {
        generateBtn.disabled = false;
        spinner.classList.add('hidden');
        showAlert('An error occurred while generating results.', 'error');
        console.error('Error:', error);
      });
    }
  </script>
</body>
</html>

