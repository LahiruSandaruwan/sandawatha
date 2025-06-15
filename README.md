# Sandawatha.lk

A modern matrimonial platform built for Sri Lankans. Find your perfect life partner through AI-powered matching, horoscope compatibility, and secure communication.

## Features

- AI-powered profile matching
- Horoscope compatibility checking
- Secure messaging system
- Premium membership options
- Profile verification
- Advanced search filters
- Dark mode support
- Mobile responsive design

## Tech Stack

- PHP 7.4+
- MySQL/MariaDB
- Bootstrap 5
- jQuery
- Modern JavaScript (ES6+)

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- Composer (for dependency management)
- Git

## Installation

1. Clone the repository:
```bash
git clone https://github.com/LahiruSandaruwan/sandawatha.git
cd sandawatha
```

2. Configure your web server:
   - Point your web server's document root to the project's `public` directory
   - Ensure mod_rewrite is enabled for Apache
   - Make sure .htaccess files are allowed

3. Set up the database:
   - Create a new MySQL database
   - Import the base schema:
     ```bash
     mysql -u your_user -p your_database < sql/schema.sql
     ```
   - Copy `config/database.example.php` to `config/database.php`
   - Update database credentials in `config/database.php`

4. Configure the application:
   - Copy `config/config.example.php` to `config/config.php`
   - Update site settings and API keys as needed

5. Set proper permissions:
```bash
chmod -R 755 .
chmod -R 777 public/uploads
```

## Project Structure

```
sandawatha/
├── app/                    # Application core
│   ├── controllers/       # Application controllers
│   ├── models/           # Database models
│   ├── views/            # View templates
│   └── helpers/          # Helper functions
├── config/               # Configuration files
├── public/               # Web root directory
│   ├── assets/          # Static assets
│   │   ├── css/        # Stylesheets
│   │   ├── js/         # JavaScript files
│   │   └── images/     # Image assets
│   ├── uploads/        # User uploaded content
│   └── index.php       # Front controller
├── routes/              # Route definitions
├── sql/                # Database schemas and migrations
│   ├── schema.sql     # Base database schema
│   └── migrations/    # Database migrations
└── logs/               # Application logs

```

## Development

We use a branching model for development:

- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - Feature branches

### Development Workflow

1. Create a new feature branch:
```bash
git checkout develop
git checkout -b feature/your-feature-name
```

2. Make your changes and commit:
```bash
git add .
git commit -m "Description of changes"
```

3. Push your changes and create a pull request:
```bash
git push origin feature/your-feature-name
```

## Database Migrations

To run database migrations:
```bash
php migrate.php
```

## License

Copyright © 2025 Sandawatha.lk. All rights reserved.