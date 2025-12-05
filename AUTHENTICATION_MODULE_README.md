# Authentication Module - Member 1

## Overview
This module implements a complete authentication system with Object-Oriented Programming (OOP) principles, including user login, registration, logout, and home page functionality.

## Files Created

### Backend Classes (OOP)

1. **`app/models/User.php`** (Enhanced)
   - `login($email, $password)` - Authenticates user credentials
   - `register($userData)` - Creates new user account with password hashing
   - `findByEmail($email)` - Finds user by email
   - `validateEmail($email)` - Validates email format
   - `emailExists($email)` - Checks if email is already registered
   - `isAdmin($userId)` - Checks if user is admin
   - `isVoter($userId)` - Checks if user is voter

2. **`app/controllers/AuthController.php`** (New)
   - `home()` - Displays home page with different content for logged in/out users
   - `login()` - Shows login form and handles login submission
   - `register()` - Shows registration form and handles registration
   - `logout()` - Logs out user and clears session
   - Private methods for validation and form handling

### Core Framework Files

3. **`app/core/Controller.php`** (New)
   - Base controller class with view rendering, redirects, flash messages
   - All controllers extend this class

4. **`app/core/Session.php`** (New)
   - Session management for authentication
   - Methods: `setUser()`, `getUser()`, `isLoggedIn()`, `isAdmin()`, `isVoter()`, etc.

5. **`app/core/Router.php`** (New)
   - URL routing system
   - Maps URLs to controller methods

### Frontend Pages

6. **`app/views/home.php`** (New)
   - Home page with welcome message
   - Shows different content based on authentication status
   - Features section

7. **`app/views/auth/login.php`** (New)
   - Login form with email and password fields
   - Client-side and server-side validation
   - Flash message display

8. **`app/views/auth/register.php`** (New)
   - Registration form with name, email, password, and confirm password
   - Password strength validation
   - Client-side password matching

9. **`app/views/layouts/header.php`** (New)
   - Common header with navigation
   - Shows user info when logged in
   - Flash message display area

10. **`app/views/layouts/footer.php`** (New)
    - Common footer

### Entry Point

11. **`public/index.php`** (New)
    - Application entry point
    - Route definitions
    - Autoloading

12. **`public/.htaccess`** (New)
    - URL rewriting rules
    - Security headers

13. **`public/css/style.css`** (New)
    - Main stylesheet

## Routes

| Method | URL | Controller | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | `/` | AuthController | home | Home page |
| GET | `/home` | AuthController | home | Home page (alias) |
| GET | `/login` | AuthController | login | Show login form |
| POST | `/login` | AuthController | login | Process login |
| GET | `/register` | AuthController | register | Show registration form |
| POST | `/register` | AuthController | register | Process registration |
| GET/POST | `/logout` | AuthController | logout | Logout user |

## Features

### Security
- Password hashing using PHP's `password_hash()` and `password_verify()`
- Session management for authentication state
- Input validation and sanitization
- SQL injection prevention via PDO prepared statements
- XSS protection with `htmlspecialchars()`

### User Experience
- Flash messages for success/error feedback
- Form validation (client-side and server-side)
- Responsive design
- Modern, clean UI
- Auto-redirect if already logged in

### OOP Principles
- **Encapsulation**: Private methods for internal logic
- **Inheritance**: Controllers extend base Controller class
- **Abstraction**: Model class provides database abstraction
- **Single Responsibility**: Each class has a specific purpose

## Setup Instructions

1. **Database Setup**
   - Make sure MySQL is running in XAMPP
   - Create database: `voting_system`
   - Run migrations from `database/migrations/001_create_users_table.sql`

2. **Configuration**
   - Update `config/database.php` with your database credentials if needed
   - Default: `localhost`, `root`, no password

3. **Web Server**
   - Point your web server document root to the `public/` directory
   - For XAMPP: Create virtual host or place project in `htdocs/voting-system`
   - Access via: `http://localhost/voting-system/`

4. **Testing**
   - Visit `http://localhost/voting-system/` to see home page
   - Click "Register" to create a new account
   - Click "Login" to authenticate
   - After login, you'll see personalized content

## Usage Examples

### In Other Controllers
```php
// Check if user is logged in
if (Session::isLoggedIn()) {
    $user = Session::getUser();
    // Access user data
}

// Check if user is admin
if (Session::isAdmin()) {
    // Admin-only code
}
```

### Redirect After Login
```php
// In AuthController, users are redirected based on role:
if (Session::isAdmin()) {
    $this->redirect('/admin/dashboard');
} else {
    $this->redirect('/');
}
```

## Database Schema

The `users` table structure:
- `id` - Primary key
- `user_id` - Unique user identifier (auto-generated)
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `is_admin` - Boolean (0 or 1)
- `is_voter` - Boolean (0 or 1)
- `created_at` - Timestamp
- `updated_at` - Timestamp

## Next Steps

This authentication module is complete and ready for integration with other modules:
- Voting Module (Member 2)
- Admin Module (Member 3)
- Results Module (Member 4)

The session system and user authentication are fully functional and can be used by other team members.

