# 🚀 DIGISKUL - XAMPP Setup Guide

Complete step-by-step guide to run DIGISKUL on your XAMPP server.

---

## ✅ Prerequisites

Before starting, ensure you have:
- **XAMPP** installed (with Apache and MySQL)
- **PHP 7.3+** (preferably PHP 8.0+) - Already included in XAMPP
- **Composer** - [Download here](https://getcomposer.org/download/)
- **Node.js & npm** - [Download here](https://nodejs.org/)

---

## 📋 Step-by-Step Setup Instructions

### **STEP 1: Start XAMPP Services**

1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**
4. Verify both services show "Running" status

---

### **STEP 2: Create Database**

1. Open your browser
2. Navigate to: `http://localhost/phpmyadmin`
3. Click **"New"** in the left sidebar
4. Database name: `digiskul`
5. Collation: `utf8mb4_unicode_ci`
6. Click **"Create"**

---

### **STEP 3: Backend Setup (Laravel)**

#### 3.1 Install Backend Dependencies

Open **Command Prompt** or **PowerShell** and navigate to the backend folder:

```bash
cd C:\xampp\htdocs\digiskul\backend
composer install
```

Wait for all packages to install (this may take a few minutes).

#### 3.2 Configure Environment

The `.env` file has already been created and configured with:
- Database name: `digiskul`
- Database user: `root`
- Database password: (empty - default XAMPP)
- Application key: Generated

#### 3.3 Run Database Migrations

```bash
php artisan migrate:fresh
```

This will create all necessary database tables.

#### 3.4 Seed Database (Optional)

To populate the database with sample data:

```bash
php artisan db:seed
```

**Note:** If you get a "permission already exists" error, it means the seeder has already run successfully.

---

### **STEP 4: Frontend Setup (Vue.js)**

#### 4.1 Install Frontend Dependencies

Open a **new Command Prompt/PowerShell** window and navigate to the frontend folder:

```bash
cd C:\xampp\htdocs\digiskul\frontend
npm install
```

Wait for all packages to install (this may take several minutes).

#### 4.2 Configure Frontend Environment

The `.env` file has been created with:
- API URL: `http://127.0.0.1:8000/api`

---

### **STEP 5: Running the Application**

You need **TWO terminal windows** open simultaneously:

#### Terminal 1: Backend (Laravel API)

```bash
cd C:\xampp\htdocs\digiskul\backend
php artisan serve
```

You should see:
```
Starting Laravel development server: http://127.0.0.1:8000
```

**Keep this terminal running!**

#### Terminal 2: Frontend (Vue.js)

```bash
cd C:\xampp\htdocs\digiskul\frontend
npm run dev
```

You should see something like:
```
VITE v5.1.0  ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
```

**Keep this terminal running too!**

---

### **STEP 6: Access the Application**

Open your browser and navigate to:

**Frontend (Main Application):**
```
http://localhost:5173
```

**Backend API:**
```
http://127.0.0.1:8000/api
```

**phpMyAdmin (Database Management):**
```
http://localhost/phpmyadmin
```

---

## 🎯 Quick Start Commands

### Start Development Servers

**Backend:**
```bash
cd C:\xampp\htdocs\digiskul\backend
php artisan serve
```

**Frontend:**
```bash
cd C:\xampp\htdocs\digiskul\frontend
npm run dev
```

---

## 🔧 Common Issues & Solutions

### Issue 1: "Port 8000 already in use"

**Solution:** Kill the process using port 8000 or use a different port:
```bash
php artisan serve --port=8001
```

Then update frontend `.env`:
```
VITE_API_BASE_URL=http://127.0.0.1:8001/api
```

### Issue 2: "Port 5173 already in use"

**Solution:** The frontend will automatically try the next available port (5174, 5175, etc.)

### Issue 3: Database Connection Error

**Solution:** 
1. Ensure MySQL is running in XAMPP
2. Check database credentials in `backend/.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=digiskul
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Issue 4: "Class not found" errors

**Solution:** Clear Laravel cache and regenerate autoload:
```bash
cd C:\xampp\htdocs\digiskul\backend
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

### Issue 5: Frontend shows blank page

**Solution:** 
1. Check browser console for errors (F12)
2. Ensure backend API is running
3. Verify `.env` file in frontend has correct API URL

---

## 📦 Project Structure

```
digiskul/
├── backend/              # Laravel 8 API
│   ├── app/             # Application code
│   ├── database/        # Migrations & seeders
│   ├── routes/          # API routes
│   └── .env             # Backend configuration
│
├── frontend/            # Vue.js 3 PWA
│   ├── src/            # Vue components & pages
│   ├── public/         # Static assets
│   └── .env            # Frontend configuration
│
└── XAMPP_SETUP_GUIDE.md # This file
```

---

## 🎨 Default Features

- **Multi-Tenant Architecture**: Each school operates independently
- **Offline-First PWA**: Works without internet connection
- **Role-Based Access**: Admin, Teacher, Account Officer, Parent
- **Modules**:
  - Student Management
  - Attendance Tracking
  - Grade Entry & Management
  - Fee Management & Payments
  - Report Generation
  - Archive Management
- **Multi-Language**: English, Hausa, Arabic, French

---

## 🔐 Default Login Credentials

After seeding the database, you can use these credentials (if seeders created them):

**Admin:**
- Email: `admin@example.com`
- Password: `password`

**Note:** Check `backend/database/seeders/DatabaseSeeder.php` for actual credentials.

---

## 📝 Development Workflow

1. **Make Backend Changes:**
   - Edit files in `backend/app/`
   - Changes are auto-reloaded by Laravel

2. **Make Frontend Changes:**
   - Edit files in `frontend/src/`
   - Vite hot-reloads changes automatically

3. **Database Changes:**
   - Create migration: `php artisan make:migration migration_name`
   - Run migration: `php artisan migrate`

4. **Add New API Endpoint:**
   - Add route in `backend/routes/api.php`
   - Create controller: `php artisan make:controller ControllerName`

---

## 🛠️ Useful Commands

### Backend (Laravel)

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate new application key
php artisan key:generate

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Create new controller
php artisan make:controller ControllerName

# Create new model
php artisan make:model ModelName -m
```

### Frontend (Vue.js)

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run linter
npm run lint
```

---

## 📞 Support

If you encounter any issues:

1. Check the error messages in both terminal windows
2. Review browser console (F12) for frontend errors
3. Check Laravel logs: `backend/storage/logs/laravel.log`
4. Ensure all services (Apache, MySQL) are running in XAMPP

---

## 🎉 Success!

If you see the DIGISKUL interface at `http://localhost:5173`, congratulations! Your setup is complete.

**Happy Coding! 🚀**
