<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Voting System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>?v=<?php echo time(); ?>">
</head>
<body>
  <div id="app">
    <?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main id="main-content">
      <!-- Dashboard -->
      <section id="adminDashboard" class="page active">
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
              `<div class="live-activity-item">
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
