<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Voting System</title>
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

    #clear-data {
      color: #e74c3c;
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

    /* ===== Stat Grid ===== */
    #stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
      margin: 20px 0;
    }

    .stat-card {
      border-radius: 12px;
      padding: 20px;
      color: #fff;
      font-weight: bold;
      font-size: 18px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: center;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
    }

    .stat-card > div:first-child {
      font-size: 28px;
      margin-bottom: 8px;
    }

    /* Stat Colors */
    .purple { background: linear-gradient(135deg, #8e44ad, #9b59b6); }
    .green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .blue { background: linear-gradient(135deg, #3498db, #5dade2); }
    .indigo { background: linear-gradient(135deg, #3b5998, #4a69bd); }

    /* ===== Elections Grid ===== */
    .elections-grid {
      display: grid;
      gap: 20px;
    }

    .election-item {
      background: #fdfdfd;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 15px;
      transition: box-shadow 0.3s ease;
    }

    .election-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

    .form-group {
      margin-bottom: 15px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
    }

    .form-actions {
      display: flex;
      gap: 15px;
      margin-top: 20px;
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

    /* ===== Candidate & Voter Items ===== */
    .candidate-item,
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

    .candidate-item:hover,
    .voter-item:hover {
      background: #eef2f7;
    }

    /* ===== Monitor & Results ===== */
    .monitor-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-top: 20px;
    }

    .select-styled {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      width: 100%;
      transition: border-color 0.3s ease;
      margin-bottom: 20px;
    }

    .select-styled:focus {
      border-color: #3498db;
      outline: none;
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .section-header h2 {
      margin: 0;
    }

    .candidates-section {
      margin: 20px 0;
    }

    .candidates-section h4 {
      margin-bottom: 15px;
    }

    .candidate-input {
      display: grid;
      grid-template-columns: 1fr 1fr auto;
      gap: 10px;
      margin-top: 10px;
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

      .monitor-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div id="app">
    <?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main id="main-content">
      <!-- Dashboard -->
      <section id="adminDashboard" class="page active">
        <div class="card">
          <h1>Admin Dashboard</h1>
          <div id="stats-grid">
            <div class="stat-card purple">
              <div id="statTotalVoters"><?= $data['stats']['totalVoters'] ?? 0 ?></div>
              <div>Total Voters</div>
            </div>
            <div class="stat-card green">
              <div id="statActiveElections"><?= $data['stats']['activeElections'] ?? 0 ?></div>
              <div>Active Elections</div>
            </div>
            <div class="stat-card blue">
              <div id="statTotalVotes"><?= $data['stats']['totalVotes'] ?? 0 ?></div>
              <div>Total Votes Cast</div>
            </div>
            <div class="stat-card indigo">
              <div id="statTurnout"><?= $data['stats']['turnout'] ?? 0 ?>%</div>
              <div>Participation Rate</div>
            </div>
          </div>
          <h3>All Elections</h3>
          <div id="electionsList" class="elections-grid">
            <?php if (!empty($data['elections'])): ?>
            <?php foreach ($data['elections'] as $election): ?>
              <div class="election-item">
                  <strong><?= htmlspecialchars($election['election_name'] ?? 'Untitled Election') ?></strong> (<?= htmlspecialchars($election['status'] ?? 'unknown') ?>)
                  <br>Start: <?= htmlspecialchars($election['start_date'] ?? 'N/A') ?>
                  <br>End: <?= htmlspecialchars($election['end_date'] ?? 'N/A') ?>
              </div>
            <?php endforeach; ?>
            <?php else: ?>
              <p>No elections found.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // ===== Configuration =====
    const BASE_URL = '<?= BASE_URL ?>';
    
    // ===== Global Variables =====
    let currentCandidates = [];
    let monitorChartInstance = null;
    let resultsChartInstance = null;

    // ===== Stats Update (from PHP data) =====
    function updateStatsFromPHP() {
      // Stats are already loaded from PHP, but we can update them dynamically if needed
      const statTotalVoters = document.getElementById("statTotalVoters");
      const statActiveElections = document.getElementById("statActiveElections");
      const statTotalVotes = document.getElementById("statTotalVotes");
      const statTurnout = document.getElementById("statTurnout");
      
      // Stats are already populated from PHP, this function is for future dynamic updates
    }

    // ===== Navigation =====
    function showPage(pageId) {
      document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
      const page = document.getElementById(pageId);
      if (page) {
        page.classList.add("active");
      }
    }

    // ===== Candidates =====
    function addCandidateToForm() {
      const name = document.getElementById("candidateNameInput");
      const affil = document.getElementById("candidateAffilInput");
      if (!name || !affil) return;
      
      const nameVal = name.value.trim();
      const affilVal = affil.value.trim();
      if (!nameVal) {
        alert("Candidate name required.");
        return;
      }

      currentCandidates.push({ name: nameVal, affil: affilVal });
      name.value = "";
      affil.value = "";
      renderCandidates();
    }

    function removeCandidate(index) {
      currentCandidates.splice(index, 1);
      renderCandidates();
    }

    function renderCandidates() {
      const container = document.getElementById("candidatesContainer");
      if (!container) return;
      
      container.innerHTML = currentCandidates.map((c, i) =>
        `<div class="candidate-item"><span><strong>${c.name}</strong>${c.affil ? " - " + c.affil : ""}</span>
         <button onclick="removeCandidate(${i})" class="btn-secondary btn-small">Remove Candidate</button></div>`
      ).join("");
    }

    // ===== Elections =====
    function saveElectionAjax(event) {
      if (event) event.preventDefault();
      
      const title = document.getElementById("electionTitle")?.value.trim() || "";
      const status = document.getElementById("electionStatus")?.value || "upcoming";
      const desc = document.getElementById("electionDesc")?.value.trim() || "";
      const start = document.getElementById("electionStart")?.value || "";
      const end = document.getElementById("electionEnd")?.value || "";
      
      if (!title || !start || !end) {
        alert("Election title, start date, and end date are required.");
        return;
      }

      const formData = new FormData();
      formData.append('title', title);
      formData.append('status', status);
      formData.append('description', desc);
      formData.append('start_date', start);
      formData.append('end_date', end);
      
      currentCandidates.forEach((c, i) => {
        formData.append(`candidates[${i}][name]`, c.name);
        formData.append(`candidates[${i}][affiliation]`, c.affil || '');
      });

      fetch(BASE_URL + '/admin/save-election', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Election saved successfully!");
          resetElectionForm();
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to save election"));
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving the election");
      });
    }

    function resetElectionForm() {
      const form = document.getElementById("electionForm");
      if (form) form.reset();
      currentCandidates = [];
      renderCandidates();
    }

    function renderElections() {
      const electionsList = document.getElementById("electionsList");
      const manageElectionsList = document.getElementById("manageElectionsList");
      
      // Elections are already rendered from PHP, this is for dynamic updates
      if (electionsList && manageElectionsList) {
        // Can be used for future AJAX updates
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

    // ===== Monitor Functions =====
    function populateMonitorDropdown() {
      const select = document.getElementById("monitorElectionSelect");
      if (!select) return;

      fetch(BASE_URL + '/admin/monitor-data')
        .then(r => r.json())
        .then(data => {
          select.innerHTML = '<option value="">-- Select Election --</option>';
          if (data.elections) {
            data.elections.forEach(election => {
              const option = document.createElement("option");
              option.value = election.id;
              option.textContent = election.election_name || election.title || 'Untitled';
              select.appendChild(option);
            });
          }
        })
        .catch(error => {
          console.error("Error loading elections:", error);
        });
    }

    function renderMonitorCharts() {
      const select = document.getElementById("monitorElectionSelect");
      const chartCanvas = document.getElementById("monitorChart");
      const activityDiv = document.getElementById("liveActivity");
      
      if (!select || !chartCanvas) return;
      
      const electionId = select.value;
      if (!electionId) {
        if (monitorChartInstance) {
          monitorChartInstance.destroy();
          monitorChartInstance = null;
        }
        if (activityDiv) activityDiv.innerHTML = '';
        return;
      }

      fetch(`${BASE_URL}/admin/monitor-data/${electionId}`)
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            alert("Error loading monitor data");
            return;
          }

          if (activityDiv && data.recentVotes) {
            activityDiv.innerHTML = data.recentVotes.map(vote => 
              `<div style="padding: 8px; margin-bottom: 8px; background: #f0f0f0; border-radius: 4px;">
                ${vote.voter_name || 'Unknown'} voted for ${vote.candidate_name || 'Unknown'} at ${vote.vote_time || 'N/A'}
              </div>`
            ).join('');
          }

          const ctx = chartCanvas.getContext("2d");
          if (monitorChartInstance) {
            monitorChartInstance.destroy();
          }

          monitorChartInstance = new Chart(ctx, {
            type: "bar",
            data: {
              labels: data.candidates.map(c => c.name),
              datasets: [{
                label: "Votes",
                data: data.voteCounts,
                backgroundColor: "#3498db"
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { display: false },
                title: { display: true, text: "Live Vote Count" }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  precision: 0
                }
              }
            }
          });
        })
        .catch(error => {
          console.error("Error loading monitor data:", error);
        });
    }

    // ===== Results Functions =====
    function populateResultsDropdown() {
      const select = document.getElementById("resultsElectionSelect");
      if (!select) return;

      fetch(BASE_URL + '/admin/results-data')
        .then(r => r.json())
        .then(data => {
          select.innerHTML = '<option value="">-- Select Election --</option>';
          if (data.elections) {
            data.elections.forEach(election => {
              const option = document.createElement("option");
              option.value = election.id;
              option.textContent = election.election_name || election.title || 'Untitled';
              select.appendChild(option);
            });
          }
        })
        .catch(error => {
          console.error("Error loading elections:", error);
        });
    }

    function renderResults() {
      const select = document.getElementById("resultsElectionSelect");
      const details = document.getElementById("resultsDetails");
      const chartCanvas = document.getElementById("resultsChart");
      
      if (!select || !details || !chartCanvas) return;
      
      const electionId = select.value;
      if (!electionId) {
        details.innerHTML = "<p>Please select an election.</p>";
        if (resultsChartInstance) {
          resultsChartInstance.destroy();
          resultsChartInstance = null;
        }
        return;
      }

      fetch(`${BASE_URL}/admin/get-results/${electionId}`)
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            details.innerHTML = "<p>Error loading results.</p>";
            return;
          }

          const election = data.election;
          const candidates = data.candidates || [];
          const totalVotes = data.totalVotes || 0;

          details.innerHTML = `
            <h3>${election.election_name || election.title || 'Election'}</h3>
            <br>
            <p><strong>Status:</strong> ${election.status || 'N/A'}</p>
            <br>
            <p><strong>Description:</strong> ${election.description || 'No description'}</p>
            <br>
            <p><strong>Start:</strong> ${election.start_date || 'N/A'}</p>
            <br>
            <p><strong>End:</strong> ${election.end_date || 'N/A'}</p>
            <br>
            <p><strong>Total Votes:</strong> ${totalVotes}</p>
            <br>
            <p><strong>Candidates:</strong></p>
            <ul>
              ${candidates.map(c => `<li>${c.name}${c.affiliation ? ' - ' + c.affiliation : ''} (${c.voteCount || 0} votes)</li>`).join('')}
            </ul>
          `;

          const ctx = chartCanvas.getContext("2d");
          if (resultsChartInstance) {
            resultsChartInstance.destroy();
          }

          resultsChartInstance = new Chart(ctx, {
            type: "bar",
            data: {
              labels: candidates.map(c => c.name),
              datasets: [{
                label: "Votes",
                data: candidates.map(c => c.voteCount || 0),
                backgroundColor: "#3498db"
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { display: false },
                title: { display: true, text: "Election Results" }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  precision: 0
                }
              }
            }
          });
        })
        .catch(error => {
          console.error("Error loading results:", error);
          details.innerHTML = "<p>Error loading results.</p>";
        });
    }

    // ===== Initialization =====
    document.addEventListener("DOMContentLoaded", () => {
      updateStatsFromPHP();
      renderElections();
      
      // Initialize candidates if on elections page
      if (document.getElementById("candidatesContainer")) {
        renderCandidates();
      }

      // Initialize monitor if on monitor page
      if (document.getElementById("monitorElectionSelect")) {
        populateMonitorDropdown();
      }

      // Initialize results if on results page
      if (document.getElementById("resultsElectionSelect")) {
        populateResultsDropdown();
      }
    });

    // Cleanup on page unload
    window.addEventListener("beforeunload", () => {
      if (monitorChartInstance) monitorChartInstance.destroy();
      if (resultsChartInstance) resultsChartInstance.destroy();
    });
  </script>
</body>
</html>
