<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Election Results - Voting System</title>
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

    #resultsDetails ul {
      list-style-type: disc;
      padding-left: 20px;
      margin-top: 10px;
    }

    #resultsDetails li {
      margin-bottom: 5px;
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
      <!-- Results -->
      <section id="resultsPage" class="page active">
        <div class="card">
          <h2>Election Results</h2>
          <div class="monitor-grid">
            <div>
              <select id="resultsElectionSelect" onchange="renderResults()" class="select-styled">
                <option value="">-- Select Election --</option>
              </select>
              <div id="resultsDetails"></div>
            </div>
            <div>
              <canvas id="resultsChart"></canvas>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // ===== Configuration =====
    const BASE_URL = '<?= BASE_URL ?>';
    let resultsChartInstance = null;

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
      populateResultsDropdown();
    });

    // Cleanup on page unload
    window.addEventListener("beforeunload", () => {
      if (resultsChartInstance) resultsChartInstance.destroy();
    });
  </script>
</body>
</html>
