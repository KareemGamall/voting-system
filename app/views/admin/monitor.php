<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live Monitor - Voting System</title>
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

    #liveActivity div {
      padding: 8px;
      margin-bottom: 8px;
      background: #f0f0f0;
      border-radius: 4px;
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
      <!-- Live Monitor -->
      <section id="monitorPage" class="page active">
        <div class="card">
          <div class="section-header">
            <h2>Live Monitor</h2>
          </div>
          <div class="monitor-grid">
            <div>
              <select id="monitorElectionSelect" onchange="renderMonitorCharts()" class="select-styled">
                <option value="">-- Select Election --</option>
              </select>
              <canvas id="monitorChart"></canvas>
            </div>
            <div>
              <h3>Recent Voting Activity</h3>
              <div id="liveActivity"></div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // ===== Configuration =====
    const BASE_URL = '<?= BASE_URL ?>';
    let monitorChartInstance = null;
    let monitorInterval = null;

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
              `<div>${vote.voter_name || 'Unknown'} voted for ${vote.candidate_name || 'Unknown'} at ${vote.vote_time || 'N/A'}</div>`
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

    function startMonitorRefresh() {
      if (monitorInterval) clearInterval(monitorInterval);
      monitorInterval = setInterval(() => {
        const select = document.getElementById("monitorElectionSelect");
        if (select && select.value) {
          renderMonitorCharts();
        }
      }, 5000);
    }

    function stopMonitorRefresh() {
      if (monitorInterval) {
        clearInterval(monitorInterval);
        monitorInterval = null;
      }
    }

    // ===== Initialization =====
    document.addEventListener("DOMContentLoaded", () => {
      populateMonitorDropdown();
      startMonitorRefresh();
    });

    // Cleanup on page unload
    window.addEventListener("beforeunload", () => {
      stopMonitorRefresh();
      if (monitorChartInstance) monitorChartInstance.destroy();
    });
  </script>
</body>
</html>
