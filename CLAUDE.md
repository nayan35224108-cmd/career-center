# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Unisbank Career Centre (UCC) - A plain PHP web application for a career center platform. Students can browse courses, register for training, and view job listings. Admins manage courses, jobs, registrations, and users.

**Tech Stack:**
- Plain PHP (no framework)
- MySQL/PDO for database
- Session-based authentication
- PHPMailer for emails (via Composer)
- Vanilla JS, CSS (no build tools)

## Development Commands

No automated build/test/lint commands exist. Manual verification:
```bash
php -l <filename>     # Check PHP syntax
php -l *.php          # Check all PHP files in directory
```

Dependencies:
```bash
composer install      # Install PHPMailer (only dependency)
```

## Architecture

### Routing System (`index.php`)
All requests route through `index.php` using URL path segments:
- `/` or `/home` → `pages/home.php`
- `/about` → `pages/about.php`
- `/course` → `pages/course.php`
- `/course/{id}` → `pages/course-detail.php`
- `/jobs` → `pages/jobs.php`
- `/jobs/{slug}` → `pages/jobs-detail.php`
- `/login`, `/register`, `/dashboard` → respective pages
- `/admin/*` → admin pages (requires `$_SESSION['admin_user']`)
- `/logout` → logs out user, `/admin/logout` → logs out admin

Page content is captured via `ob_start()`/`ob_get_clean()` and rendered within header/footer templates (unless `$skipLayout = true` for admin pages).

### Key Files

| File | Purpose |
|------|---------|
| `config.php` | Database connection, constants (DB, SMTP, site URLs) |
| `includes/functions.php` | Helper functions: `e()`, `sanitize()`, `csrf_token()`, `send_email()`, `slugify()` |
| `includes/auth.php` | Auth functions: `is_user_logged_in()`, `login_user()`, `require_login()` |
| `includes/settings.php` | Settings CRUD: `get_setting()`, `set_setting()` |
| `templates/header.php` | Site header with nav, SEO meta tags |
| `templates/footer.php` | Site footer with configurable columns |

### Page Structure

Front-end pages (`pages/*.php`) follow this pattern:
```php
<?php
require_once __DIR__ . '/../includes/settings.php';
// Set page variables
$pageTitle = 'Page Title';
$currentPage = 'page-key';
// Handle POST requests before HTML
// HTML output
?>
```

Admin pages (`pages/admin/*.php`) use `$skipLayout = true` and render their own full HTML with sidebar.

### Database Tables

- `users` - User accounts
- `courses` - Training courses
- `course_registrations` - User-course registrations
- `jobs` - Job listings
- `settings` - Key-value site settings
- `footer_settings` - Footer configuration (section/key/value)

### Session Structure

- User session: `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['user_email']`, `$_SESSION['user_role']`
- Admin session: `$_SESSION['admin_user']` (array with id, name, email)
- CSRF token: `$_SESSION['csrf']`

## Code Conventions

### PHP Style
- 2-space indentation
- Snake_case for functions and variables
- `<?php` opening tags (never short tags `<?`)
- Use `require_once __DIR__ . '/path'` for includes
- Global `$pdo` for database access in functions

### Database Queries
Always use prepared statements:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
```

### Security
- Escape output: `<?= e($variable) ?>`
- CSRF protection: include `csrf_token()` in forms, validate with `csrf_check($_POST['csrf'])`
- Sanitize input: `sanitize($_POST['field'])`
- Passwords: `password_hash()` and `password_verify()`

### Templates
- Use alternative syntax for control structures:
```php
<?php if ($condition): ?>
  <div>Content</div>
<?php endif; ?>
```

### UI Language
All UI text is in Indonesian. Keep function/variable names in English.

## CSS/Styling

- Main styles in `assets/css/theme.css`
- Auth-related styles in `assets/css/auth.css`
- Footer has inline styles in `templates/footer.php`
- Admin pages have inline `<style>` blocks

## Email System

Emails use PHPMailer via `send_email()` or `send_email_template()`:
```php
send_email_template($to, $subject, $html_content);
```
SMTP config is in `config.php` constants.

## AI Integration

The admin panel includes AI-powered content generation:
- `/admin/generate-description` - Generate course descriptions using Gemini API
- `/admin/generate-course-image` - Generate course images

API keys are configured via environment variables (`GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_IMAGE_MODEL`).