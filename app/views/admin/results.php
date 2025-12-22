<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Election Results - Voting System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>?v=<?php echo time(); ?>">
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
