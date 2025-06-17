# Sandawatha.lk

A modern matrimonial platform built for Sri Lankans. Find your perfect life partner through AI-powered matching, horoscope compatibility, and secure communication.

## Current Features

### User Management
- User registration and authentication
- Profile creation and management
- Profile verification system
- Password reset functionality
- User preferences and settings

### Profile Features
- Detailed profile creation
- Photo upload and management
- Basic profile search
- Profile favorites system
- Profile blocking functionality

### Communication
- Basic messaging system
- Chat functionality
- Message notifications
- Contact form for support

### Premium Features
- Premium membership system
- Enhanced profile visibility
- Advanced search filters
- Priority messaging

### Admin Features
- User management dashboard
- Content moderation
- Report handling
- Basic analytics

## Upcoming Features

### Enhanced Matching
- Advanced AI-powered matching algorithm
- Horoscope compatibility checking
- Personality matching system
- Location-based matching
- Interest-based matching

### Communication Improvements
- Video chat integration
- Voice messages
- Message encryption
- Read receipts
- Message scheduling

### User Experience
- Dark mode implementation
- Mobile app development
- Progressive Web App (PWA) support
- Multi-language support
- Accessibility improvements

### Security & Privacy
- Two-factor authentication
- Enhanced privacy controls
- Profile verification badges
- Secure file sharing
- Activity logging

### Premium Features
- Virtual gifts system
- Profile highlighting
- Priority customer support
- Advanced analytics
- Custom profile themes

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

2. Install dependencies:
```bash
composer install
```

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

## Quick Start (Development)

### Recommended: Using Composer Scripts (Cross-platform)

```bash
# Start both web server and WebSocket server
composer run dev
# or
composer run start

# Or start services separately:
composer run serve      # Web application only (http://localhost:8000)
composer run websocket  # WebSocket server only (ws://localhost:8080)
```

### Alternative: Direct PHP Commands

```bash
# Start complete development environment
php bin/dev-server.php

# Or start services separately:
php -S localhost:8000 -t public        # Web server only
php bin/websocket_server.php           # WebSocket server only
```

### Legacy: Shell Scripts (Linux/Mac/Windows)
Shell scripts are still available for compatibility:
```bash
./start-dev.sh      # Linux/Mac
start-dev.bat       # Windows
```

### Available Composer Commands:
- `composer run dev` - Start both servers (recommended)
- `composer run start` - Alias for dev command
- `composer run serve` - Web application only
- `composer run websocket` - WebSocket server only

### Important Notes:
- 💬 **Real-time messaging requires both servers** to be running
- 🌐 Web app will be available at: `http://localhost:8000`
- 💬 WebSocket server runs on: `ws://localhost:8080`
- 📋 See `DEV-SETUP.md` for detailed development instructions

### Features Requiring WebSocket Server:
- Real-time messaging
- Online user status
- Live chat notifications
- Message status updates
- Typing indicators

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