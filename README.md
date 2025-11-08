# DIGISKUL – Smart School Workflow System

A comprehensive, multi-tenant SaaS education management platform built with Laravel 11 backend and Vue.js 3 PWA frontend.

## 🎯 System Overview

DIGISKUL is a unified, cloud-based academic management platform that connects administrators, teachers, accounts officers, and parents under one digital ecosystem. The system supports offline-first functionality for low-connectivity areas and multi-school tenancy.

## ✨ Key Features

- **Multi-Tenant Architecture**: Each school operates as an isolated tenant
- **Offline-First PWA**: Works seamlessly offline with automatic sync
- **Role-Based Access Control**: Admin, Teacher, Account Officer, Parent roles
- **Comprehensive Modules**: 
  - Student Management
  - Attendance Tracking
  - Grade Entry & Management
  - Fee Management & Payments
  - Report Generation
  - Archive Management
- **Multi-Language Support**: English, Hausa, Arabic, French
- **AWS Cloud Hosting**: Scalable and reliable infrastructure

## 🏗️ Architecture

### Backend
- **Framework**: Laravel 11
- **Database**: MySQL/PostgreSQL (AWS RDS)
- **Authentication**: Laravel Sanctum
- **Multi-Tenancy**: stancl/tenancy
- **Permissions**: Spatie Laravel Permission
- **Cache/Queue**: Redis + Laravel Horizon

### Frontend
- **Framework**: Vue.js 3 + Vite
- **State Management**: Pinia
- **Styling**: TailwindCSS
- **Offline Storage**: IndexedDB (Dexie.js)
- **PWA**: Service Worker + Workbox

## 📦 Project Structure

```
digiskul/
├── frontend/          # Vue.js 3 PWA application
├── backend/           # Laravel 11 API
└── README.md          # This file
```

## 🚀 Quick Start

See [SETUP.md](SETUP.md) for complete setup instructions.

## 📚 Documentation

- [Backend README](backend/README.md)
- [Frontend README](frontend/README.md)
- [Setup Guide](SETUP.md)

## 📄 License

MIT License

