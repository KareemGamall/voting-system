<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Election - Voting System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>?v=<?php echo time(); ?>">
</head>
<body>
  <div id="app">
    <?php require_once __DIR__ . '/../layouts/admin_sidebar.php'; ?>

    <main id="main-content">
      <!-- Create/Edit Election -->
      <section id="createElectionPage" class="page active">
        <div class="card form-card">
          <h2 id="form-title">Create New Election</h2>
          <form id="electionForm">
            <input type="hidden" id="electionId" value="">
            <div class="form-group">
              <label>Election Title</label>
              <input type="text" id="electionTitle" required placeholder="e.g. Student Council Election 2025">
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea id="electionDesc" rows="3" placeholder="Brief description of this election..."></textarea>
            </div>
            
            <div class="alert alert-info" style="margin-bottom: 1.5rem; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196f3; color: #1565c0;">
              <strong>Note:</strong> Election status is automatically determined based on start and end dates.
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Start Date & Time</label>
                <input type="datetime-local" id="electionStart" required>
              </div>
              <div class="form-group">
                <label>End Date & Time</label>
                <input type="datetime-local" id="electionEnd" required>
              </div>
            </div>

            <div class="candidates-section">
              <h4>Candidates</h4>
              <div id="candidatesContainer"></div>
              
              <div class="candidate-input" style="grid-template-columns: 1fr 1fr 1fr auto;">
                <input type="text" id="candidateNameInput" placeholder="Candidate Full Name" required>
                <input type="text" id="candidatePositionInput" placeholder="Position (e.g. President)" required>
                <input type="text" id="candidatePartyInput" placeholder="Party / Affiliation (optional)">
                <button type="button" onclick="addCandidateToForm()" class="btn-success">Add</button>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" onclick="saveElectionAjax()" class="btn-primary">Save Election</button>
              <button type="button" onclick="resetElectionForm()" class="btn-secondary">Reset Form</button>
            </div>
          </form>

          <h3 style="margin-top: 40px; margin-bottom: 20px;">Existing Elections</h3>
          <div id="manageElectionsList" class="elections-grid">
            <?php if (!empty($data['elections'])): ?>
              <?php foreach ($data['elections'] as $election): ?>
                <div class="election-item" data-election-id="<?= htmlspecialchars($election['id'] ?? '') ?>">
                  <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                    <div>
                      <strong style="font-size: 16px; color: #2c3e50;"><?= htmlspecialchars($election['election_name'] ?? 'Untitled Election') ?></strong>
                      <span style="display: inline-block; padding: 4px 10px; margin-left: 10px; border-radius: 4px; font-size: 12px; font-weight: bold; 
                        background: <?php 
                          $status = $election['status'] ?? 'unknown';
                          echo $status === 'active' ? '#2ecc71' : ($status === 'completed' ? '#95a5a6' : ($status === 'cancelled' ? '#e74c3c' : '#3498db'));
                        ?>; 
                        color: white;">
                        <?= strtoupper(htmlspecialchars($status)) ?>
                      </span>
                    </div>
                    <small style="color: #7f8c8d;">ID: <?= htmlspecialchars($election['election_id'] ?? 'N/A') ?></small>
                  </div>
                  <?php if (!empty($election['description'])): ?>
                    <p style="color: #555; margin: 8px 0; font-size: 14px;"><?= htmlspecialchars(substr($election['description'], 0, 100)) ?><?= strlen($election['description']) > 100 ? '...' : '' ?></p>
                  <?php endif; ?>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; font-size: 13px; color: #666;">
                    <div>
                      <strong>Start:</strong><br>
                      <?= date('M d, Y H:i', strtotime($election['start_date'] ?? 'now')) ?>
                    </div>
                    <div>
                      <strong>End:</strong><br>
                      <?= date('M d, Y H:i', strtotime($election['end_date'] ?? 'now')) ?>
                    </div>
                  </div>
                  <div style="display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                    <button onclick="editElection(<?= htmlspecialchars($election['id'] ?? '') ?>)" class="btn-primary btn-small">Edit</button>
                    <?php if ($status !== 'cancelled'): ?>
                      <button onclick="cancelElection(<?= htmlspecialchars($election['id'] ?? '') ?>, '<?= htmlspecialchars(addslashes($election['election_name'] ?? '')) ?>')" class="btn-secondary btn-small">Cancel</button>
                    <?php endif; ?>
                    <button onclick="deleteElection(<?= htmlspecialchars($election['id'] ?? '') ?>, '<?= htmlspecialchars(addslashes($election['election_name'] ?? '')) ?>')" class="btn-danger btn-small">Delete</button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="election-item" style="text-align: center; padding: 40px; color: #95a5a6;">
                <p style="font-size: 16px;">No elections found. Create your first election above!</p>
              </div>
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

    // ===== Candidates =====
    function addCandidateToForm() {
      const name = document.getElementById("candidateNameInput");
      const position = document.getElementById("candidatePositionInput");
      const party = document.getElementById("candidatePartyInput");
      
      if (!name || !position) return;
      
      const nameVal = name.value.trim();
      const positionVal = position.value.trim();
      const partyVal = party ? party.value.trim() : "";
      
      if (!nameVal || !positionVal) {
        alert("Candidate name and position are required.");
        return;
      }

      currentCandidates.push({ 
        name: nameVal, 
        position: positionVal,
        party: partyVal 
      });
      
      name.value = "";
      if (position) position.value = "";
      if (party) party.value = "";
      renderCandidates();
    }

    function removeCandidate(index) {
      currentCandidates.splice(index, 1);
      renderCandidates();
    }

    function renderCandidates() {
      const container = document.getElementById("candidatesContainer");
      if (!container) return;
      
      if (currentCandidates.length === 0) {
        container.innerHTML = '<p style="color: #95a5a6; font-style: italic; padding: 10px;">No candidates added yet. Add candidates above.</p>';
        return;
      }
      
      container.innerHTML = currentCandidates.map((c, i) =>
        `<div class="candidate-item">
          <span>
            <strong>${escapeHtml(c.name)}</strong> - ${escapeHtml(c.position)}
            ${c.party ? " (" + escapeHtml(c.party) + ")" : ""}
          </span>
          <button onclick="removeCandidate(${i})" class="btn-secondary btn-small">Remove</button>
        </div>`
      ).join("");
    }
    
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // ===== Elections =====
    function saveElectionAjax() {
      const electionId = document.getElementById("electionId")?.value || "";
      const title = document.getElementById("electionTitle")?.value.trim() || "";
      const desc = document.getElementById("electionDesc")?.value.trim() || "";
      const start = document.getElementById("electionStart")?.value || "";
      const end = document.getElementById("electionEnd")?.value || "";
      // Status is auto-calculated on server based on dates
      
      if (!title || !start || !end) {
        alert("Election title, start date, and end date are required.");
        return;
      }

      const formData = new FormData();
      if (electionId) {
        formData.append('election_id', electionId);
      }
      formData.append('title', title);
      formData.append('description', desc);
      formData.append('start_date', start);
      formData.append('end_date', end);
      // Don't send status - server calculates it from dates
      
      currentCandidates.forEach((c, i) => {
        formData.append(`candidates[${i}][name]`, c.name);
        formData.append(`candidates[${i}][position]`, c.position || 'General');
        formData.append(`candidates[${i}][party]`, c.party || '');
      });

      const url = electionId ? BASE_URL + '/admin/update-election' : BASE_URL + '/admin/save-election';
      fetch(url, {
        method: 'POST',
        body: formData
      })
      .then(async r => {
        const text = await r.text();
        console.log('Response status:', r.status);
        console.log('Response text:', text);
        
        try {
          return JSON.parse(text);
        } catch (e) {
          console.error('Failed to parse JSON:', e);
          throw new Error('Server returned invalid response: ' + text.substring(0, 100));
        }
      })
      .then(data => {
        console.log('Response data:', data);
        if (data.success) {
          alert(electionId ? "Election updated successfully!" : "Election saved successfully!");
          resetElectionForm();
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to save election"));
        }
      })
      .catch(error => {
        console.error("Error details:", error);
        alert("An error occurred while saving the election: " + error.message);
      });
    }

    function resetElectionForm() {
      const form = document.getElementById("electionForm");
      if (form) form.reset();
      document.getElementById("electionId").value = "";
      document.getElementById("form-title").textContent = "Create New Election";
      currentCandidates = [];
      renderCandidates();
      
      // Reset default dates
      const startInput = document.getElementById("electionStart");
      const endInput = document.getElementById("electionEnd");
      if (startInput && !startInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        startInput.value = now.toISOString().slice(0, 16);
      }
      if (endInput && !endInput.value) {
        const weekLater = new Date();
        weekLater.setDate(weekLater.getDate() + 7);
        weekLater.setMinutes(weekLater.getMinutes() - weekLater.getTimezoneOffset());
        endInput.value = weekLater.toISOString().slice(0, 16);
      }
    }

    // ===== Edit Election =====
    async function editElection(electionId) {
      try {
        const response = await fetch(BASE_URL + '/admin/get-election/' + electionId);
        const data = await response.json();
        
        if (!data.success) {
          alert("Error: " + (data.message || "Failed to load election data"));
          return;
        }
        
        const election = data.election;
        
        // Populate form fields
        document.getElementById("electionId").value = election.id;
        document.getElementById("electionTitle").value = election.election_name || "";
        document.getElementById("electionDesc").value = election.description || "";
        
        // Format dates for datetime-local input
        const startDate = new Date(election.start_date);
        const endDate = new Date(election.end_date);
        startDate.setMinutes(startDate.getMinutes() - startDate.getTimezoneOffset());
        endDate.setMinutes(endDate.getMinutes() - endDate.getTimezoneOffset());
        
        document.getElementById("electionStart").value = startDate.toISOString().slice(0, 16);
        document.getElementById("electionEnd").value = endDate.toISOString().slice(0, 16);
        
        // Load candidates
        currentCandidates = [];
        if (election.candidates && Array.isArray(election.candidates)) {
          election.candidates.forEach(c => {
            currentCandidates.push({
              name: c.name || "",
              position: c.position || "General",
              party: c.party || ""
            });
          });
        }
        renderCandidates();
        
        // Update form title
        document.getElementById("form-title").textContent = "Edit Election";
        
        // Scroll to form
        document.getElementById("electionForm").scrollIntoView({ behavior: 'smooth', block: 'start' });
        
      } catch (error) {
        console.error("Error loading election:", error);
        alert("An error occurred while loading the election data");
      }
    }

    // ===== Cancel Election =====
    function cancelElection(electionId, electionName) {
      if (!confirm(`Are you sure you want to cancel "${electionName}"?\n\nThis will prevent any further voting on this election.`)) {
        return;
      }
      
      fetch(BASE_URL + '/admin/cancel-election/' + electionId, {
        method: 'POST'
      })
      .then(async r => {
        const text = await r.text();
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error('Server returned invalid response: ' + text.substring(0, 100));
        }
      })
      .then(data => {
        if (data.success) {
          alert("Election cancelled successfully!");
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to cancel election"));
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while cancelling the election");
      });
    }

    // ===== Delete Election =====
    function deleteElection(electionId, electionName) {
      if (!confirm(`Are you sure you want to delete "${electionName}"?\n\nThis will also delete all associated candidates and votes. This action cannot be undone!`)) {
        return;
      }
      
      fetch(BASE_URL + '/admin/delete-election/' + electionId, {
        method: 'POST'
      })
      .then(async r => {
        const text = await r.text();
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error('Server returned invalid response: ' + text.substring(0, 100));
        }
      })
      .then(data => {
        if (data.success) {
          alert("Election deleted successfully!");
          location.reload();
        } else {
          alert("Error: " + (data.message || "Failed to delete election"));
        }
      })
      .catch(error => {
        console.error("Error deleting election:", error);
        alert("An error occurred while deleting the election: " + error.message);
      });
    }

    // ===== Initialization =====
    document.addEventListener("DOMContentLoaded", () => {
      renderCandidates();
      
      // Set default dates (start: now, end: 7 days from now)
      const startInput = document.getElementById("electionStart");
      const endInput = document.getElementById("electionEnd");
      
      if (startInput && !startInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        startInput.value = now.toISOString().slice(0, 16);
      }
      
      if (endInput && !endInput.value) {
        const weekLater = new Date();
        weekLater.setDate(weekLater.getDate() + 7);
        weekLater.setMinutes(weekLater.getMinutes() - weekLater.getTimezoneOffset());
        endInput.value = weekLater.toISOString().slice(0, 16);
      }
    });
  </script>
</body>
</html>
