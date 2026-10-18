# Haustor.ba — Apartment Building Management & Maintenance Reporting

**Course:** IT-2001 Web Programming  
**Author:** Tarik Sorguč  

---

## 1. Project Overview & Mission

**Haustor.ba** is a modern web application designed to streamline communication and maintenance workflows within residential apartment buildings across Bosnia and Herzegovina. Traditional building management often suffers from fragmented communication channels (bulletin boards, messaging groups, phone calls) and a lack of transparency regarding reported repairs.

**Our Mission:** To provide residents and building managers (*upravitelji* / HOA representatives) with a centralized, transparent, and easy-to-use platform for reporting maintenance issues, tracking repair statuses in real time, and managing residential building units efficiently.

---

## 2. Core Entities

The system is built around five core relational entities:

1. **Users (`users`)** — Registered individuals in the system, storing profile details, credentials (bcrypt password hashes), and assigned access roles (`resident` or `manager`).
2. **Buildings (`buildings`)** — Residential apartment buildings managed on the platform, including street address, city, postal code, geocoded coordinates, and assigned manager metadata.
3. **Apartments (`apartments`)** — Individual residential units linked to a specific building and associated with resident users (apartment number, floor, square footage).
4. **Categories (`categories`)** — Classification types for building issues (e.g., *Plumbing & Water*, *Electrical & Lighting*, *Elevator*, *Common Area Cleaning*, *Heating/HVAC*, *Structural & Roof*) with priority indicators.
5. **Maintenance Requests (`maintenance_requests`)** — Issue reports submitted by residents tied to a building, apartment (or common area), and category, tracking description, priority, status (`pending`, `in_progress`, `resolved`, `rejected`), timestamps, and manager resolution notes.

---

## 3. Target User Roles

| Role | Description & Key Capabilities |
| :--- | :--- |
| **Resident (`resident`)** | • View assigned building and apartment details<br>• Submit new maintenance requests with category and urgency<br>• Track status updates and resolution notes on reported issues<br>• Browse building-wide maintenance activity |
| **Manager (`manager` / `admin`)** | • Manage buildings, apartments, residents, and maintenance categories (Full CRUD)<br>• Review, triage, and update the status of all maintenance requests<br>• Add resolution notes and monitor building maintenance metrics |

---

## 4. Architecture Summary

- **Frontend:** Strict Single Page Application (SPA) located in `frontend/` using HTML5, CSS3, and JavaScript. Communicates asynchronously with the backend REST API.
- **Backend:** Pure PHP REST API powered by **FlightPHP** in `backend/`, structured into `routes/`, `services/`, `dao/`, `middleware/`, and `config/`.
- **Database:** MySQL accessed exclusively via PHP PDO prepared statements.
- **Authentication:** Stateless JWT authentication (`firebase/php-jwt`) and role-based authorization middleware.

---

## 5. Local Setup & Installation Instructions

### Prerequisites
- PHP 8.0+ (with `pdo_mysql` and `mbstring` extensions enabled)
- MySQL 8.0+ / MariaDB (e.g., via XAMPP)
- Composer (for PHP backend dependencies)

### Step 1: Clone the Repository
```bash
git clone <repository-url> C:/xampp/htdocs/haustor.ba
cd C:/xampp/htdocs/haustor.ba
```

### Step 2: Import the Database Schema
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) or your preferred MySQL client.
2. Create a new database named `haustor_db` (UTF-8 / `utf8mb4_unicode_ci`).
3. Import the SQL dump file from the `docs/` or `backend/` directory:
   ```bash
   mysql -u root -p haustor_db < docs/haustor_db.sql
   ```

### Step 3: Configure Backend Environment
1. Copy the example configuration template to create your local configuration file:
   ```bash
   cp backend/config/config.example.php backend/config/config.php
   ```
2. Open `backend/config/config.php` and update the credentials to match your local setup:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
   - `JWT_SECRET` (set a strong random secret string)
   - `EXTERNAL_API_KEY` (set your third-party service API key)

### Step 4: Install Backend Dependencies & Run
```bash
cd backend
composer install
```
Start Apache and MySQL via XAMPP Control Panel and navigate to `http://localhost/haustor.ba/frontend/` in your browser.

---

## 6. API Documentation & Live Deployment

- **Swagger / OpenAPI Documentation:** `[TBD — http://localhost/haustor.ba/backend/docs/]`
- **Live Deployment URL:** `[TBD — Placeholder for Production URL]`
- **AI Usage Log:** See [`docs/AI_LOG.md`](docs/AI_LOG.md)
- **System Architecture & Agent Rules:** See [`AGENTS.md`](AGENTS.md)
