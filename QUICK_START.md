# Sandawatha.lk - Quick Start Guide

## 🚀 Getting Started

### 1. Setup Google OAuth (for social login)
Create a `.env` file in the project root with your Google credentials:
```
GOOGLE_CLIENT_ID=345740611184-p044aq3dv421cupbnujeh5ldg4kmuj.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GQCSPX-IVI7Mhv9Aq-mmt3HyFQSB5_uRUk6
```

### 2. Start the Application
```bash
./start-dev.sh
```

This will start:
- Web application on http://localhost:8000
- WebSocket server on ws://localhost:8080

### 3. Access the Application
- **Website**: http://localhost:8000
- **Google Login**: Available on login/register pages

## 📁 Clean Project Structure

```
sandawatha/
├── app/                 # Application code
├── bin/                 # Executable scripts
├── config/              # Configuration files
├── public/              # Web accessible files
├── routes/              # Route definitions
├── sql/                 # Database files
├── vendor/              # Composer dependencies
├── logs/                # Application logs
├── start-dev.sh         # Development server startup
├── composer.json        # PHP dependencies
└── README.md           # Full documentation
```

## 🎯 Features
- ✅ Google OAuth login
- ✅ Clean project structure
- ✅ WebSocket support
- ✅ Modern PHP architecture

## 🛠 Development
- Start: `./start-dev.sh`
- Logs: `tail -f logs/app.log`
- Stop: `Ctrl+C`

For detailed documentation, see `README.md` 