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
   - Import the schema from `sql/schema.sql`
   - Copy `config/database.example.php` to `config/database.php`
   - Update database credentials in `config/database.php`

4. Configure the application:
   - Copy `config/config.example.php` to `config/config.php`
   - Update site settings and API keys as needed

5. Set proper permissions:
```bash
chmod -R 755 .
chmod -R 777 public/assets/images/uploads
```

## Project Structure

```
sandawatha/
├── app/
│   ├── controllers/    # Application controllers
│   ├── models/         # Database models
│   └── views/          # View templates
├── config/            # Configuration files
├── public/            # Public assets
│   ├── assets/
│   │   ├── css/      # Stylesheets
│   │   ├── js/       # JavaScript files
│   │   └── images/   # Image assets
│   └── index.php     # Front controller
├── routes/           # Route definitions
└── sql/             # Database schemas
```

## Development

We use a branching model for development:

- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - Feature branches

To contribute:

1. Create a new feature branch from develop:
```bash
git checkout develop
git checkout -b feature/your-feature-name
```

2. Make your changes and commit:
```bash
git add .
git commit -m "Description of changes"
```

3. Push your branch and create a pull request:
```bash
git push origin feature/your-feature-name
```

## Security

- All user passwords are hashed using bcrypt
- CSRF protection is implemented
- SQL injection prevention through prepared statements
- XSS protection
- Rate limiting on sensitive endpoints

## License

This project is proprietary software. All rights reserved.

## Contact

For support or inquiries, please contact:
- Email: [your-email@example.com]
- Website: [https://sandawatha.lk]

## Acknowledgments

- Bootstrap team for the excellent UI framework
- jQuery team for the JavaScript library
- All contributors who have helped with the project 