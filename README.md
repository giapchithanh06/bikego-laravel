# 🚲 BIKEGO-LARAVEL - Public Bicycle Rental Website

## 📌 Description

BIKEGO is a public bicycle rental website developed to give users a convenient way to find bike stations, rent bikes, return them, and review the service.

The system allows users to browse stations by ward, view available bikes and free docks, rent a bike by its plate number, return it at a station with a free dock, get the fee calculated automatically by the minute, and look up their rental history.

The project was built using Laravel 8, Blade, MySQL, and JavaScript (AJAX), focusing on two-sided validation (client and server), rental business logic, and a clean MVC code structure.

The website also includes an Admin Area that supports bike management and station management with soft delete, recycle bin, and restore.

## 🌐 Repository

GitHub Repository
[https://github.com/giapchithanh06/bikego-laravel]

## 🚀 Features

### 👤 Customer Features

Authentication
- User Registration (name, phone, Gmail, password validated on both client and server)
- User Login with phone number or email
- Logout

Station Browsing
- Browse stations and filter by ward
- View station details: available bikes, free docks, rating
- View station reviews (loaded with AJAX)

Rental & Return
- Rent a bike: choose ward → choose station → enter bike plate number
- Return a bike at a station that has a free dock
- Automatic fee calculation: minutes (rounded up, minimum 1) × 500 VND per minute
- Only one active rental per user

Reviews & History
- Review the station and the bike after returning
- Rental history, newest first
- Monthly Top Bikers ranking

### 👨‍💼 Admin Features

Bike Management (`/admin/bikes`)
- Paginated list (10 rows per page)
- View bike details with all reviews
- Add, edit, and soft-delete bikes

Station Management (`/admin/stations`)
- Paginated list with free docks, status, and rating
- View station details with all reviews
- Add, edit, and soft-delete stations

Recycle Bin
- Restore or permanently delete selected items
- Restore all / delete all (with confirmation dialog)

## 📋 Business Rules

- A bike can be rented only when it is at a station and its condition is *Normal*.
- Stations under maintenance or soft-deleted are hidden from renting.
- A bike can be returned only to a station that has a free dock.
- Top Bikers are ranked by total minutes (desc) → number of rentals (desc) → older account → random.
- Monthly results are stored in the `monthly_top_bikers` table.

## 🛠️ Technologies Used

- PHP 7.4+
- Laravel 8
- MySQL / MariaDB
- Blade Template Engine
- HTML5, CSS3, JavaScript (ES6), AJAX
- Bootstrap Icons
- PHPUnit (Feature tests)

## ✨ Modern Techniques Applied

### 🏗️ MVC Architecture
- Separation of Model, View (Blade), and Controller
- Service classes: `TopBikerService`, `WardImporter`

### 🗄️ Eloquent ORM
- Relationships between stations, bikes, rentals, and reviews
- `SoftDeletes` for recycle bin and restore

### ✅ Validation
- Form Request validation on the server
- Mirrored regex validation on the client

### 🔒 Middleware
- `admin` middleware protects the admin area

### ⏰ Task Scheduling
- `bikego:summarize-monthly` runs automatically at 00:00 on the 1st of every month

### 🌱 Database Seeding
- Demo stations, bikes, users, rentals, and reviews

### ⚡ AJAX
- Load stations, details, and reviews without reloading the page

## 📂 Project Structure

```
bikego-laravel/
│
├── Phan_01/                     # Part 1: static HTML/CSS/JS interface
│   ├── index.html
│   ├── stations.html
│   ├── bike-rental.html
│   ├── bike-return.html
│   ├── css/style.css
│   ├── js/effects.js
│   └── img/
│
├── app/                         # Part 2: Laravel
│   ├── Console/                 # Kernel, bikego:summarize-monthly command
│   ├── Http/
│   │   ├── Controllers/         # Auth, Home, Station, Rental, Review
│   │   │   └── Admin/           # Auth, Bike, Station
│   │   ├── Middleware/
│   │   └── Requests/            # RegisterRequest
│   ├── Models/                  # User, Station, Bicycle, Rental, Ward, ...
│   └── Services/                # TopBikerService, WardImporter
│
├── config/bikego.php            # Project-specific configuration
├── database/
│   ├── migrations/
│   └── seeders/
├── public/assets/               # Shared CSS, JS, images
├── resources/views/             # Blade: layouts, auth, rental, admin, ...
├── routes/web.php
└── tests/Feature/BikeGoTest.php
```

## 🎨 User Interface

The website interface was designed with:

- Clean public bike-sharing style
- Blue and teal theme
- Responsive layout
- Hero banner with CSS animation
- Color and icon indicators for free docks, status, and ratings
- Simple and user-friendly navigation
- Dedicated admin area

## ⚙️ System Architecture

```
Browser (HTML + CSS + JavaScript / AJAX)
   ↓
Laravel Routes → Middleware
   ↓
Controllers → Form Requests / Services
   ↓
Eloquent Models
   ↓
MySQL
```

## ▶️ How to Run

**Requirements:** PHP ≥ 7.4, Composer, MySQL running (for example XAMPP).

### 1. Clone the Repository

```bash
git clone https://github.com/giapchithanh06/bikego-laravel.git
cd bikego-laravel
```

### 2. Install Dependencies and Configure

```bash
composer install
cp .env.example .env         
php artisan key:generate
```

Open `.env` and set the database connection (default XAMPP values):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bikego
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create the Database and Seed Demo Data

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS bikego CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate:fresh --seed
```

### 4. Run the Website

```bash
php artisan serve
```

Open: **http://127.0.0.1:8000**

To view the static Part 1 interface, open `Phan_01/index.html` directly.

## 🔑 Accounts

| Role | URL | Credentials |
|---|---|---|
| **Admin** | `/admin` (redirects to `/admin/login`) | Username: `admin` / Password: `12345` |
| Demo user | `/login` | Phone `0901000001` or email `demo.user1@gmail.com` / Password: `Test@1234` |

> The `/login` page accepts only a phone number or an email. The admin must sign in at `/admin/login`.

Demo bike plates look like `BG-0001`, `BG-0002`, … and can be listed in the admin page `/admin/bikes`.

## ⏰ Monthly Top Bikers

The summary command runs automatically at 00:00 on the 1st of each month (defined in `app/Console/Kernel.php`):

```bash
php artisan schedule:work                                
php artisan bikego:summarize-monthly --month=2026-09     
```

## 🧪 Testing

Tests run on a separate database `bikego_test` (configured in `phpunit.xml`):

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS bikego_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

## 📦 Submission Structure

```
2001240441_GiapChiThanh_05/
├── Phan_01/     # HTML/CSS/JS
└── Phan_02/     # Laravel project (with database/bikego.sql)
```

## 🎯 Purpose

This project was developed for educational purposes to practice:

- Web Development with Laravel and the MVC pattern
- Relational Database Design with MySQL and Eloquent
- Client-side and Server-side Validation
- Rental and Fee Calculation Business Logic
- Admin Area with Soft Delete, Recycle Bin, and Restore
- Automated Testing
- Version Control with Git and GitHub

## 🚀 Future Improvements

- Online Payment Gateway Integration
- Map View of Bike Stations
- Rental Time Notifications
- Revenue Statistics Dashboard for Admins
- Multiple Admin Accounts and Role-based Permissions
- RESTful API for a Mobile App

## 👨‍💻 Authors

Giáp Chí Thanh

Student Project – BIKEGO Public Bicycle Rental Website

## 📄 License

This project is intended for educational and learning purposes only.
