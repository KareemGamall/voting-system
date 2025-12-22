<?php 
$title = $title ?? 'Election Results';
require_once __DIR__ . '/../layouts/header.php'; 
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* ===== Results Page Styles ===== */
    .results-page {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }

    .page-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .page-header h1 {
      color: #2c3e50;
      font-size: 32px;
      margin-bottom: 10px;
    }

    .page-header p {
      color: #7f8c8d;
      font-size: 16px;
    }

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

    .card h3 {
      color: #34495e;
      margin-bottom: 15px;
      font-size: 20px;
    }

    .select-styled {
      padding: 12px;
      border-radius: 6px;
      border: 1px solid #ddd;
      font-size: 14px;
      width: 100%;
      max-width: 500px;
      transition: border-color 0.3s ease;
      margin-bottom: 20px;
    }

    .select-styled:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .results-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-top: 20px;
    }

    .results-details {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
    }

    .results-details h3 {
      color: #2c3e50;
      margin-bottom: 15px;
    }

    .results-details p {
      margin-bottom: 10px;
      color: #555;
    }

    .results-details strong {
      color: #2c3e50;
    }

    .results-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    .results-table th,
    .results-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #e9ecef;
    }

    .results-table th {
      background: #f8f9fa;
      font-weight: 600;
      color: #2c3e50;
    }

    .results-table tr:hover {
      background: #f8f9fa;
    }

    .winner-badge {
      background: #d4edda;
      color: #155724;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 10px;
    }

    .position-group {
      margin-bottom: 30px;
    }

    .position-group h4 {
      color: #34495e;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #3498db;
    }

    .chart-container {
      position: relative;
      height: 400px;
      margin-top: 20px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
    }

    .stat-card h4 {
      font-size: 32px;
      margin-bottom: 5px;
    }

    .stat-card p {
      font-size: 14px;
      opacity: 0.9;
    }

    .winners-section {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
      padding: 25px;
      border-radius: 8px;
      margin-top: 20px;
    }

    .winners-section h3 {
      color: white;
      margin-bottom: 20px;
    }

    .winner-item {
      background: rgba(255, 255, 255, 0.2);
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 8px;
      backdrop-filter: blur(10px);
    }

    .no-results {
      text-align: center;
      padding: 40px;
      color: #7f8c8d;
    }

    .no-results-icon {
      font-size: 64px;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .results-grid {
        grid-template-columns: 1fr;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../layouts/header.php'; ?>

  <div class="results-page">
    <div class="page-header">
      <h1>Election Results</h1>
      <p>View results for completed elections</p>
    </div>

    <div class="card">
      <h2>Select Election</h2>
      <select id="electionSelect" class="select-styled" onchange="loadResults()">
        <option value="">-- Select Completed Election --</option>
        <?php if (!empty($elections)): ?>
          <?php foreach ($elections as $election): ?>
            <option value="<?= $election['id'] ?>">
              <?= htmlspecialchars($election['election_name'] ?? 'Untitled') ?>
              (Completed: <?= htmlspecialchars(date('M d, Y', strtotime($election['end_date'] ?? ''))) ?>)
            </option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>

      <div id="resultsContainer" style="display: none;">
        <!-- Statistics -->
        <div class="stats-grid" id="statsGrid"></div>

        <!-- Results Grid -->
        <div class="results-grid">
          <div>
            <div class="results-details" id="resultsDetails"></div>
          </div>
          <div>
            <div class="chart-container">
              <canvas id="resultsChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Results by Position -->
        <div id="resultsByPosition"></div>

        <!-- Winners Section -->
        <div class="winners-section" id="winnersSection" style="display: none;">
          <h3>🏆 Winners</h3>
          <div id="winnersList"></div>
        </div>
      </div>

      <div id="noResults" class="no-results" style="display: none;">
        <div class="no-results-icon">📊</div>
        <h3>No Results Available</h3>
        <p>Please select a completed election to view results.</p>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

  <script>
    const BASE_URL = '<?= BASE_URL ?>';
    let resultsChartInstance = null;

    function loadResults() {
      const electionId = document.getElementById('electionSelect').value;
      
      if (!electionId) {
        document.getElementById('resultsContainer').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        if (resultsChartInstance) {
          resultsChartInstance.destroy();
          resultsChartInstance = null;
        }
        return;
      }

      document.getElementById('noResults').style.display = 'none';

      fetch(`${BASE_URL}/voter/results-data?election_id=${electionId}`)
        .then(response => response.json())
        .then(data => {
          if (!data.success) {
            alert('Error loading results: ' + (data.message || 'Unknown error'));
            return;
          }

          displayResults(data);
        })
        .catch(error => {
          console.error('Error loading results:', error);
          alert('Error loading results. Please try again.');
        });
    }

    function displayResults(data) {
      const election = data.election;
      const results = data.results || [];
      const resultsByPosition = data.resultsByPosition || {};
      const winners = data.winners || [];
      const totalVotes = data.totalVotes || 0;

      // Display statistics
      const statsGrid = document.getElementById('statsGrid');
      statsGrid.innerHTML = `
        <div class="stat-card">
          <h4>${totalVotes}</h4>
          <p>Total Votes</p>
        </div>
        <div class="stat-card">
          <h4>${results.length}</h4>
          <p>Candidates</p>
        </div>
        <div class="stat-card">
          <h4>${Object.keys(resultsByPosition).length}</h4>
          <p>Positions</p>
        </div>
        <div class="stat-card">
          <h4>${winners.length}</h4>
          <p>Winners</p>
        </div>
      `;

      // Display election details
      const detailsDiv = document.getElementById('resultsDetails');
      detailsDiv.innerHTML = `
        <h3>${election.election_name || 'Untitled Election'}</h3>
        <p><strong>Description:</strong> ${election.description || 'No description'}</p>
        <p><strong>Start Date:</strong> ${election.start_date || 'N/A'}</p>
        <p><strong>End Date:</strong> ${election.end_date || 'N/A'}</p>
        <p><strong>Total Votes:</strong> ${totalVotes}</p>
        
        <table class="results-table">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Position</th>
              <th>Votes</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            ${results.map(r => `
              <tr>
                <td>${r.candidate_name || 'N/A'}</td>
                <td>${r.position || 'N/A'}</td>
                <td>${r.vote_count || 0}</td>
                <td>${r.percentage || 0}%</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;

      // Display chart
      const chartCanvas = document.getElementById('resultsChart');
      const ctx = chartCanvas.getContext('2d');
      
      if (resultsChartInstance) {
        resultsChartInstance.destroy();
      }

      const colors = [
        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
        '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#d35400'
      ];

      resultsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: results.map(r => r.candidate_name || 'Unknown'),
          datasets: [{
            label: 'Votes',
            data: results.map(r => r.vote_count || 0),
            backgroundColor: results.map((_, i) => colors[i % colors.length]),
            borderColor: results.map((_, i) => colors[i % colors.length]),
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            title: {
              display: true,
              text: 'Election Results - Vote Distribution',
              font: { size: 16 }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const result = results[context.dataIndex];
                  return `Votes: ${result.vote_count} (${result.percentage}%)`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });

      // Display results by position
      const positionDiv = document.getElementById('resultsByPosition');
      let positionHTML = '<div class="card"><h3>Results by Position</h3>';
      
      for (const [position, positionResults] of Object.entries(resultsByPosition)) {
        positionHTML += `
          <div class="position-group">
            <h4>${position}</h4>
            <table class="results-table">
              <thead>
                <tr>
                  <th>Candidate</th>
                  <th>Party</th>
                  <th>Votes</th>
                  <th>Percentage</th>
                </tr>
              </thead>
              <tbody>
                ${positionResults.map(r => `
                  <tr>
                    <td>
                      ${r.candidate_name || 'N/A'}
                      ${r.vote_count === Math.max(...positionResults.map(pr => pr.vote_count)) ? '<span class="winner-badge">Winner</span>' : ''}
                    </td>
                    <td>${r.party || 'Independent'}</td>
                    <td>${r.vote_count || 0}</td>
                    <td>${r.percentage || 0}%</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `;
      }
      
      positionHTML += '</div>';
      positionDiv.innerHTML = positionHTML;

      // Display winners
      if (winners.length > 0) {
        const winnersSection = document.getElementById('winnersSection');
        const winnersList = document.getElementById('winnersList');
        
        winnersList.innerHTML = winners.map(w => `
          <div class="winner-item">
            <strong>${w.position}:</strong> ${w.candidate_name || 'N/A'} 
            (${w.party || 'Independent'}) - ${w.vote_count || 0} votes (${w.percentage || 0}%)
          </div>
        `).join('');
        
        winnersSection.style.display = 'block';
      }

      document.getElementById('resultsContainer').style.display = 'block';
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      const electionSelect = document.getElementById('electionSelect');
      if (electionSelect.value === '') {
        document.getElementById('noResults').style.display = 'block';
      }
    });
  </script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

