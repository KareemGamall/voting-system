# Admin Module - Member 2

## Overview
This module implements the admin panel for creating and managing elections and candidates, voter management, live monitoring, and viewing results. It integrates with the authentication/session layer and the existing MVC stack.

## Files Created

### Backend Classes (OOP)

1. **`app/controllers/AdminController.php`** (New)
   - `dashboard()` - Admin stats overview
   - `elections()` - Create/Edit elections
   - `saveElection()` / `updateElection()` / `deleteElection()` - Elections CRUD (AJAX)
   - `getElection()` - Fetch election with candidates (AJAX)
   - `voters()` - Manage voters page
   - `addVoter()` / `removeVoter()` - Voter CRUD (AJAX)
   - `monitor()` / `monitorData()` - Live monitor page + data (AJAX)
   - `results()` / `resultsData()` / `getResults()` - Results page + data (AJAX)

### Core Framework Files

2. **`app/core/Controller.php`** (Existing, reused)
   - Base controller: view rendering, redirects, flash messages
   - AdminController extends this

3. **`app/core/Session.php`** (Existing, reused)
   - Session/auth checks (`isLoggedIn()`, `isAdmin()`, `getUser()`)
   - Used to gate all admin routes

### Builders (Election creation)

4. **`app/builders/ElectionBuilder.php`** (Interface)
   - Contract for setting ID, name, dates, status, description, adding candidates, `build()`

5. **`app/builders/ConcreteElectionBuilder.php`** (Implementation)
   - Builds an `Election` object, accumulates candidates, returns the built election

6. **`app/builders/ElectionDirector.php`** (Director)
   - Orchestrates builds:
     - `constructFullElection()` for full elections (id/name/description/start/end/status/candidates)
     - `constructSimpleElection()` for minimal/pending elections

### Frontend Pages (Admin)

7. **`app/views/admin/dashboard.php`** (New)
   - Stats: voters, elections, votes, turnout
   - Elections list

8. **`app/views/admin/elections.php`** (New)
   - Create/Edit elections (title, description, status, dates)
   - Candidate add/remove
   - Existing elections list with Edit/Delete

9. **`app/views/admin/voters.php`** (New)
   - Add/Remove voters (AJAX)

10. **`app/views/admin/monitor.php`** (New)
   - Live vote monitor (Chart.js bar chart + recent activity)

11. **`app/views/admin/results.php`** (New)
   - Results view (Chart.js bar chart + election details)

12. **`app/views/layouts/admin_sidebar.php`** (New)
   - Admin navigation links

13. **`app/views/layouts/header.php`** (Updated)
    - Shared layout header for admin pages

## Routes

| Method | URL | Controller | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | `/admin/dashboard` | AdminController | dashboard | Admin stats |
| GET | `/admin/elections` | AdminController | elections | Manage elections |
| POST | `/admin/save-election` | AdminController | saveElection | Create election |
| POST | `/admin/update-election` | AdminController | updateElection | Update election |
| POST | `/admin/delete-election/{id}` | AdminController | deleteElection | Delete election |
| GET | `/admin/get-election/{id}` | AdminController | getElection | Fetch election (AJAX) |
| GET | `/admin/voters` | AdminController | voters | Voter management page |
| POST | `/admin/add-voter` | AdminController | addVoter | Add voter |
| POST | `/admin/remove-voter` | AdminController | removeVoter | Remove voter |
| GET | `/admin/monitor` | AdminController | monitor | Live monitor page |
| GET | `/admin/monitor-data` | AdminController | monitorData | Elections list (AJAX) |
| GET | `/admin/monitor-data/{id}` | AdminController | monitorData | Live counts (AJAX) |
| GET | `/admin/results` | AdminController | results | Results page |
| GET | `/admin/results-data` | AdminController | resultsData | Elections list (AJAX) |
| GET | `/admin/get-results/{id}` | AdminController | getResults | Results data (AJAX) |

## Features

### Security
- Admin-only access enforced via `Session::isAdmin()` in controller guard.
- Uses existing session/auth module; redirects non-admins to home.
- Server-side validation of required election fields; sanitized outputs in views.

### User Experience
- AJAX-driven forms for elections, voters, monitor, and results (JSON responses).
- Chart.js visualizations for live monitoring and results.
- Inline status badges, responsive layout, auto-refresh for monitor.

### Styling
- Admin styling is embedded within each admin view (no shared `admin.css`).

### OOP Principles
- **Controller inheritance:** AdminController extends base Controller.
- **Builder Pattern:** `ElectionDirector` + `ConcreteElectionBuilder` construct election objects before saving.
- **Separation of concerns:** Models handle data access; controller coordinates; views render; JS handles UI/AJAX.

## Setup Instructions

1. **Admin Access**
   - Ensure you have an admin user (use `is_admin = 1` in `users` table or create via existing flows).

2. **Run App**
   - Serve from `public/` (e.g., XAMPP `htdocs/voting-system`).
   - Navigate to `/admin/dashboard` after logging in as admin.

3. **Create an Election**
   - Go to `/admin/elections`, fill election fields, add candidates, click Save.

4. **Manage Voters**
   - `/admin/voters` to add/remove voters as needed.

5. **Monitor & Results**
   - `/admin/monitor` for live counts/activity (auto-refresh).
   - `/admin/results` for tallies and charts.

## Usage Examples

### Controller Guard
```php
// In any admin method
if (!Session::isAdmin()) {
    $this->redirect('/');
    return;
}
```

### Builder Pattern (inside saveElection)
```php
$builder = new ConcreteElectionBuilder();
$election = $this->electionDirector->constructFullElection($builder, [
    'name' => $title,
    'description' => $description,
    'startDate' => $startDateFormatted,
    'endDate' => $endDateFormatted,
    'status' => $dbStatus,
    'candidates' => $candidates
]);
```

## Database Schema (relevant tables)
- `elections` — election_id, name, description, start_date, end_date, status
- `candidates` — candidate_id, election_id, name, position, party, vote_count
- `users` — reused for voters/admins (is_admin, is_voter flags)
- `votes` — election_id, candidate_id, voter_id, vote_time

## Next Steps
- Extend monitor/results with filtering or export if needed.
- Add pagination/search to voters and elections lists if datasets grow.

