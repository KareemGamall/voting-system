# PHP Voting System - MVC Project Structure

## Complete Folder Structure

```
voting-system/
│
├── config/
│   ├── database.php          # Database configuration and connection settings
│   └── config.php             # General application configuration (site name, timezone, etc.)
│
├── public/                    # Publicly accessible folder (document root)
│   ├── index.php              # Main entry point - all requests go through here
│   ├── .htaccess              # Apache URL rewriting rules for clean URLs
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # JavaScript file
│   └── images/                # Public images (logo, icons, etc.)
│
├── app/                       # Application core files
│   │
│   ├── controllers/           # Controllers handle user requests
│   │   ├── AuthController.php        # Login, logout, registration
│   │   ├── VoteController.php        # Cast votes, view ballot
│   │   ├── CandidateController.php   # Candidate information
│   │   ├── ElectionController.php    # Election management
│   │   ├── AdminController.php       # Admin panel operations
│   │   └── DashboardController.php   # Results and statistics
│   │
│   ├── models/                # Models interact with database
│   │   ├── User.php           # System users (admins, voters)
│   │   ├── Voter.php          # Voter-specific data (voter ID, eligibility)
│   │   ├── Candidate.php      # Candidate information and photos
│   │   ├── Election.php       # Election details (start/end dates, status)
│   │   ├── Vote.php           # Vote records (encrypted for privacy)
│   │   └── Position.php       # Positions being voted for (President, VP, etc.)
│   │
│   ├── views/                 # View files (HTML templates)
│   │   ├── layouts/
│   │   │   ├── header.php     # Common header (navigation, meta tags)
│   │   │   ├── footer.php     # Common footer
│   │   │   └── sidebar.php    # Admin sidebar navigation
│   │   │
│   │   ├── auth/              # Authentication views
│   │   │   ├── login.php      # Login form
│   │   │   └── register.php   # Voter registration form
│   │   │
│   │   ├── vote/              # Voting interface views
│   │   │   ├── ballot.php     # Voting ballot page
│   │   │   ├── confirmation.php  # Vote confirmation before submission
│   │   │   └── success.php    # Success message after voting
│   │   │
│   │   ├── admin/             # Admin panel views
│   │   │   ├── dashboard.php        # Admin dashboard with statistics
│   │   │   ├── manage-candidates.php  # Add/edit/delete candidates
│   │   │   ├── manage-elections.php   # Create/manage elections
│   │   │   ├── manage-voters.php      # Manage voter accounts
│   │   │   └── results.php            # Election results and reports
│   │   │
│   │   └── errors/            # Error pages
│   │       ├── 404.php        # Page not found
│   │       └── 403.php        # Access denied
│   │
│   └── core/                  # Core system files (MVC framework)
│       ├── App.php            # Application initialization and routing
│       ├── Controller.php     # Base controller class (extended by all controllers)
│       ├── Model.php          # Base model class (extended by all models)
│       ├── Database.php       # Database connection and queries (PDO)
│       ├── Router.php         # Routes URLs to correct controllers
│       ├── Session.php        # Session management (login state, flash messages)
│       └── Validator.php      # Input validation and sanitization
│
├── helpers/                   # Helper functions
│   ├── functions.php          # General helper functions (redirect, sanitize, etc.)
│   ├── auth_helper.php        # Authentication helpers (isLoggedIn, isAdmin)
│   └── vote_helper.php        # Voting-related helpers (hasVoted, canVote)
│
├── middleware/                # Middleware for route protection
│   ├── AuthMiddleware.php     # Check if user is authenticated
│   ├── AdminMiddleware.php    # Check if user is admin
│   └── VoteMiddleware.php     # Check if user can vote (eligibility, hasn't voted)
│
├── database/                  # Database files
│   ├── migrations/            # SQL files to create tables
│   │   ├── create_users_table.sql
│   │   ├── create_voters_table.sql
│   │   ├── create_candidates_table.sql
│   │   ├── create_elections_table.sql
│   │   ├── create_positions_table.sql
│   │   └── create_votes_table.sql
│   │
│   ├── seeds/                 # Sample data for testing
│   │   └── sample_data.sql
│   │
│   └── voting_system.sql      # Complete database export (for submission)
│
├── storage/                   # Files generated by the application
│   ├── logs/                  # Application logs
│   │   └── app.log            # Error and activity logs
│   │
│   └── uploads/               # User uploaded files
│       └── candidate_photos/  # Candidate profile photos
│
├── .htaccess                  # Root .htaccess (redirects to public/)
├── .gitignore                 # Files to ignore in Git (config files, logs)
└── README.md                  # Project documentation and setup instructions
```


**write this command in your terminal to apply the structure:**

New-Item -ItemType Directory -Path config, public, public\css, public\js, public\images, app, app\controllers, app\models, app\views, app\views\layouts, app\views\auth, app\views\vote, app\views\admin, app\views\errors, app\core, helpers, middleware, database, database\migrations, database\seeds, storage, storage\logs, storage\uploads, storage\uploads\candidate_photos

**Good luck with your university project! 🎓**