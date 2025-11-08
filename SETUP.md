# DIGISKUL Complete System Setup Guide

## 📋 Prerequisites

Before starting, ensure you have:

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+ or PostgreSQL 15+
- Redis
- Git

## 🚀 Complete Setup Instructions

### Step 1: Clone Repository

```bash
git clone <repository-url> digiskul
cd digiskul
```

### Step 2: Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=digiskul
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Run migrations:
```bash
php artisan migrate
php artisan db:seed
```

Publish package configs:
```bash
php artisan vendor:publish --tag=sanctum-config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan tenants:install
```

Start backend server:
```bash
php artisan serve
```

### Step 3: Frontend Setup

Open a new terminal:

```bash
cd frontend
npm install
cp .env.example .env
```

Edit `.env`:
```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

Start frontend dev server:
```bash
npm run dev
```

### Step 4: Access Application

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000
- Login credentials:
  - Super Admin: admin@digiskul.app / password
  - School Admin: admin@nurlight.digiskul.app / password
  - Teacher: teacher@nurlight.digiskul.app / password

## 🔧 Development Commands

### Backend

```bash
cd backend
php artisan serve          # Start dev server
php artisan migrate        # Run migrations
php artisan db:seed        # Seed database
php artisan test           # Run tests
```

### Frontend

```bash
cd frontend
npm run dev        # Start dev server
npm run build      # Build for production
npm run lint       # Lint code
```

## 📞 Support

For issues or questions, please create an issue in the repository.

