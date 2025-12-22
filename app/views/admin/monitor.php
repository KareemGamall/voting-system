<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live Monitor - Voting System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>?v=<?php echo time(); ?>">
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
              <div id="liveActivity" class="live-activity"></div>
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
              `<div class="live-activity-item">${vote.voter_name || 'Unknown'} voted for ${vote.candidate_name || 'Unknown'} at ${vote.vote_time || 'N/A'}</div>`
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
