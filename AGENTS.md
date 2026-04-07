# AGENTS.md - Developer Guidelines for UCC (Unisbank Career Centre)

## Project Overview

This is a plain PHP web application for a career center platform. It uses:
- Plain PHP (no framework)
- MySQL/PDO for database
- Session-based authentication
- No build tools or package managers

## Build/Lint/Test Commands

There are **no automated build, lint, or test commands** in this project. This is a legacy PHP application.

### Manual Verification
- Test PHP syntax: `php -l <filename>`
- Check all PHP files: `php -l *.php` (run from each directory)
- No unit tests exist

## Code Style Guidelines

### General
- Use `<?php` opening tag (never short tags `<?`)
- Use 2-space indentation (no tabs)
- Use single quotes for strings unless interpolation needed
- Always use semicolons (no shorthand PHP tags)
- Maximum line length: ~120 characters

### PHP Files
```php
<?php
// Correct: require_once with __DIR__
require_once __DIR__ . '/../includes/functions.php';

// Variables use snake_case
$user_id = 1;
$course_list = [];

// Functions use snake_case
function get_user_by_id($user_id) { }

// Use early returns
function process_data($data) {
  if (empty($data)) {
    return null;
  }
  // process...
}
```

### Database (PDO)
- Always use **prepared statements** with parameter binding
- Never concatenate user input into SQL queries
```php
// Correct
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// Incorrect - SQL injection risk!
$stmt = $pdo->query("SELECT * FROM users WHERE id = $user_id");
```

- Use global `$pdo` in functions (following existing convention)
- Fetch mode: `PDO::FETCH_ASSOC` (configured globally)

### Security
- Always escape output with `e()` function: `<?= e($variable) ?>`
- Use CSRF tokens on all forms: `csrf_token()` and `csrf_check()`
- Sanitize input with `sanitize()` function
- Use `password_verify()` for password checking
- Use `password_hash()` with `PASSWORD_DEFAULT` for storing passwords

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| Functions | snake_case | `get_user()`, `is_logged_in()` |
| Variables | snake_case | `$user_id`, `$course_list` |
| Constants | UPPER_SNAKE_CASE | `DB_HOST`, `BASE_URL` |
| Database tables | snake_case | `course_registrations`, `user_roles` |

### Error Handling
- Use try/catch for database operations
- Return error arrays: `return ['success' => false, 'error' => 'message']`
- Use `http_response_code()` for HTTP errors
- Display user-friendly error messages in Indonesian

### Template/View Files
- Include header/footer with `include __DIR__ . '/../templates/footer.php'`
- Define `$pageTitle`, `$bodyClass`, `$layoutMode` variables
- Use short echo tags `<?= e($var) ?>` for output
- Use alternative syntax for control structures in views:
```php
<?php if ($condition): ?>
  <div>Content</div>
<?php endif; ?>
```

### Functions
- Document functions with comments for complex logic
- Return type hints where beneficial: `function foo(): array { }`
- Use type casting: `(int)$id`, `(string)$name`

### Includes Structure
```
/config.php          - Database connection, constants
/includes/functions.php  - Utility functions (e(), sanitize(), etc.)
/includes/auth.php   - Authentication functions
/includes/settings.php  - Site settings
/pages/*.php         - Page controllers/views
/templates/*.php     - Reusable templates (header, footer, 404)
/assets/             - CSS, JS, images
/uploads/            - User-uploaded files
```

### Imports/Requires
- Use `require_once` for files that must be included once
- Use `__DIR__` for relative paths: `__DIR__ . '/../file.php'`
- Include config early: `require_once __DIR__ . '/../config.php'`

### CSS
- Use existing classes from `theme.css` and `auth.css`
- Follow BEM-like naming: `.block__element--modifier`
- Use flexbox and grid for layout (as per existing code)

### JavaScript
- Vanilla JS only (no frameworks)
- Use `defer` attribute for scripts
- Follow existing patterns in `main.js`

### Git Conventions
- No commit hooks configured
- Follow existing commit message style when applicable

### Indonesian Language
- UI text is in Indonesian
- Error messages should be in Indonesian
- Variable/function names in English

## Common Tasks

### Adding a New Page
1. Create file in `/pages/` directory
2. Include required files at top
3. Add authentication check if needed
4. Set page variables (`$pageTitle`, `$bodyClass`)
5. Handle form submissions before HTML output
6. Include header, content, footer

### Adding a New Database Function
1. Add to appropriate file in `/includes/`
2. Use prepared statements
3. Return data or error array
4. Add documentation comment

### Adding a New Form
1. Include CSRF token: `<input type="hidden" name="csrf" value="<?= csrf_token() ?>">`
2. Validate CSRF on POST: `csrf_check($_POST['csrf'] ?? '')`
3. Sanitize inputs: `sanitize($_POST['field'])`
4. Escape output: `e($variable)`
