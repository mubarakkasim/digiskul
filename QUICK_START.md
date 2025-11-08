# DIGISKUL - Quick Start Guide

## 🚀 Starting the Development Servers

### Option 1: Manual Start (Two Terminals)

**Terminal 1 - Backend:**
```bash
cd backend
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm install
npm run dev
```

### Option 2: Using Scripts (If Available)

**Start Backend:**
```bash
cd backend
composer install
php artisan serve
```

**Start Frontend:**
```bash
cd frontend
npm install
npm run dev
```

## 📍 Important Notes

- **Backend** runs on: `http://localhost:8000`
- **Frontend** runs on: `http://localhost:3000`
- Make sure you're in the correct directory before running commands
- Run `npm` commands from the `frontend` directory
- Run `composer` and `php artisan` commands from the `backend` directory

## 🔧 First Time Setup

### Backend Setup:
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Edit .env with your database credentials
php artisan migrate
php artisan db:seed
```

### Frontend Setup:
```bash
cd frontend
npm install
cp .env.example .env
# Edit .env with your API URL
npm run dev
```

## ✅ Verify Setup

1. Backend should show: "Laravel development server started"
2. Frontend should show: "Local: http://localhost:3000"
3. Open browser to: `http://localhost:3000`
4. Check health endpoint: `http://localhost:8000/health`

