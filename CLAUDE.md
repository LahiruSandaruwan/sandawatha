# Sandawatha.lk - Matrimonial Web Application

## Project Overview
Sandawatha.lk is a PHP-based matrimonial web application that helps people find compatible life partners. The platform includes user profiles, contact requests, favorites, messaging, and premium membership features.

## Technology Stack
- **Backend**: PHP 8+ with custom MVC framework
- **Database**: MySQL 8+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Web Server**: Apache (XAMPP for development)
- **Architecture**: Custom MVC pattern

## Project Structure
```
sandawatha/
├── app/
│   ├── controllers/          # MVC Controllers
│   ├── models/              # Database Models (extend BaseModel)
│   └── views/               # View templates
├── config/                  # Configuration files
│   ├── config.php          # Main config (contains hardcoded secrets - needs env vars)
│   └── database.php        # Database connection
├── public/                  # Public web root
│   ├── assets/             # CSS, JS, images
│   └── index.php           # Application entry point
├── routes/
│   └── router.php          # Custom routing system
└── sql/
    └── schema.sql          # Database schema
```

## Key Features
- User registration and authentication
- Profile creation and management
- Advanced search and filtering
- Contact request system
- Favorites system
- Messaging between matched users
- Premium membership tiers
- Admin panel for user management
- AI-powered compatibility matching
- File uploads (photos, videos, documents)

## Common Development Tasks

### Testing the Application
```bash
# Option 1: Using XAMPP
# Start XAMPP services
sudo /opt/lampp/lampp start

# Or on Windows
# Start XAMPP Control Panel and start Apache + MySQL

# Access application
# http://localhost/sandawatha/
```

```bash
# Option 2: Built-in PHP Development Server (no XAMPP needed)
# From project root directory (replace with your path):
# Example for a project located at "~/Development/my personal projects/sandawatha":
cd ~/Development/my\ personal\ projects/sandawatha
php -S localhost:8000 -t public

# Access application
# http://localhost:8000

> **Note:**
> - Make sure your MySQL server is running (e.g., `sudo service mysql start` on Linux or start XAMPP's MySQL).
> - If you see `No such file or directory` for socket errors, update `config/database.php` to use `127.0.0.1` instead of `localhost` to force a TCP connection.
```

### Database Operations
```bash
# Access MySQL
mysql -u root -p sandawatha_lk

# Import schema
mysql -u root -p sandawatha_lk < sql/schema.sql
```

### Code Quality Checks
```bash
# PHP syntax check
find . -name "*.php" -exec php -l {} \;

# Check for common security issues
grep -r "SELECT.*WHERE.*=" app/models/ --include="*.php"
```

### Common File Locations

#### Controllers
- **Main Controllers**: `app/controllers/`
- **Authentication**: `AuthController.php`
- **Dashboard**: `DashboardController.php`
- **Profiles**: `ProfileController.php`
- **Admin**: `AdminController.php`

#### Models
- **Base Model**: `app/models/BaseModel.php` (all models extend this)
- **User Management**: `UserModel.php`, `ProfileModel.php`
- **Interactions**: `ContactRequestModel.php`, `FavoriteModel.php`, `MessageModel.php`
- **Premium**: `PremiumModel.php`

#### Views
- **Layouts**: `app/views/layouts/main.php`, `app/views/layouts/admin.php`
- **Authentication**: `app/views/auth/`
- **Dashboard**: `app/views/dashboard/`
- **Profiles**: `app/views/profiles/`

## Recent Issues Fixed
1. **PDO Parameter Error**: Fixed duplicate parameter binding in `ContactRequestModel::getRequestStats()` and `FavoriteModel::getFavoriteStats()`
2. **Method Conflict**: Renamed `ProfileController::view()` to `viewProfile()` to avoid conflict with `BaseController::view()`
3. **SQL Injection Protection**: Added column whitelist validation in `BaseModel::findBy()`
4. **Error Handling**: Added try-catch blocks in `DashboardController`
5. **Session Security**: Added SameSite cookie protection

## Security Considerations
- **CRITICAL**: Hardcoded credentials in `config/config.php` lines 20-21 need to be moved to environment variables
- All models use prepared statements for SQL injection protection
- CSRF tokens implemented across forms
- File upload validation needs strengthening
- Column name validation added to prevent SQL injection

## Database Schema Key Tables
- `users` - User accounts and authentication
- `user_profiles` - Detailed profile information
- `contact_requests` - Connection requests between users
- `favorites` - User favorites/bookmarks
- `messages` - Private messaging system
- `premium_memberships` - Subscription management

## Environment Setup
1. Install XAMPP with PHP 8+ and MySQL 8+
2. Create database `sandawatha_lk`
3. Import `sql/schema.sql`
4. Update database credentials in `config/database.php`
5. Set proper file permissions for `public/uploads/`
6. Ensure `config/config.php` BASE_URL and SITE_ROOT definitions are correct. The configuration now auto-detects base URL and site root for both built-in PHP server and Apache subdirectory use.

## URL Routes
- `/` - Homepage
- `/browse` - Browse profiles
- `/dashboard` - User dashboard (auth required)
- `/profile/{id}` - View specific profile
- `/login`, `/register` - Authentication
- `/admin` - Admin panel (admin role required)

## Known Issues
- Hardcoded secrets need environment variable migration
- CORS configuration allows any origin (security risk)
- File upload validation only checks MIME types
- Rate limiting uses session storage (easily bypassed)

## Development Notes
- Custom MVC framework (not Laravel/Symfony)
- No Composer dependencies - pure PHP
- Session-based authentication
- Custom routing system in `routes/router.php`
- Database operations through custom BaseModel class

## Model Architecture
All models extend `BaseModel` which provides:
- Basic CRUD operations
- Query builder methods
- Parameter binding protection
- Column name validation (recently added)

Example model usage:
```php
$userModel = new UserModel();
$user = $userModel->findByEmail('user@example.com');
$profile = $profileModel->findByUserId($userId);
```

## Debugging Common Issues
1. **PDO Parameter Errors**: Check for duplicate parameter names in SQL queries
2. **Method Not Found**: Verify controller method names match routes
3. **Database Connection**: Check credentials in `config/database.php`
4. **File Permissions**: Ensure `public/uploads/` is writable
5. **Session Issues**: Check session configuration in `config/config.php`

## Performance Considerations
- Database queries could benefit from indexing
- Image uploads need compression/resizing
- Consider implementing caching for frequently accessed data
- Profile search queries may need optimization

## Future Improvements
- Migrate to environment-based configuration
- Implement proper logging system
- Add comprehensive input validation
- Strengthen file upload security
- Add API rate limiting
- Implement proper error handling throughout