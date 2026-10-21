# AGENTS.md - Haustor.ba System Architecture & Agent Rules

## Project Overview
- **Project Name:** Haustor.ba (Apartment building management and direct maintenance reporting)
- **Target Course:** IT-2001 Web Programming
- **Architecture:** Strict Single Page Application (SPA) frontend separated from a pure PHP FlightPHP REST backend. **No mixed PHP/HTML code is permitted.**

---

## Core Technical Stack & Layers

### 1. Frontend (SPA)
- Pure client-side Single Page Application (HTML5, CSS3/Bootstrap, Vanilla JS / jQuery / SPApp).
- Communicates with the backend exclusively via asynchronous RESTful JSON API calls (`fetch` / `$.ajax`).
- Dynamically loads views from `frontend/views/` without full-page reloads.

### 2. Backend (FlightPHP REST API)
- Built with **FlightPHP** following a strict layered architecture:
  - **Routes (`backend/routes/`):** Define HTTP endpoints, parse request inputs, invoke service methods, and return JSON responses with appropriate HTTP status codes. Contains OpenAPI/Swagger (`@OA\...`) annotations.
  - **Services (`backend/services/`):** Encapsulate all business logic, validation, and third-party API integrations.
  - **DAO (`backend/dao/`):** Data Access Objects responsible exclusively for database operations.
  - **Middleware (`backend/middleware/`):** Handle cross-cutting concerns including JWT verification, role-based access control, and request/error logging.
  - **Config (`backend/config/`):** Environment configuration and database connection bootstrapping.

### 3. Database Layer
- **MySQL** relational database accessed **exclusively** through **PHP PDO prepared statements** via the dedicated DAO layer (`BaseDao` and entity-specific DAOs).
- String interpolation of user input in SQL queries is strictly forbidden.

### 4. Authentication & Authorization
- **JWT-based authentication** (using `firebase/php-jwt`).
- **Role-based middleware** enforcing access permissions across endpoints for two primary roles:
  - `resident` — Can view building announcements/info, submit maintenance requests, and track their own reported issues.
  - `manager` (or `admin`) — Full administrative control over buildings, apartments, categories, users, and maintenance request lifecycle/statuses.

### 5. External API Integration
- Third-party integration (e.g., **OpenStreetMap Nominatim** for building address geocoding/validation or **Mailtrap** for maintenance status notifications) is handled strictly within a dedicated backend service (`backend/services/`).
- Must implement explicit timeout configuration and graceful error/fallback handling so external service outages never crash core application flows.

---

## Directory Layout

```text
haustor.ba/
├── backend/
│   ├── config/       # Configuration files (config.example.php, local config.php)
│   ├── dao/          # PDO Data Access Objects (BaseDao, UserDao, BuildingDao, etc.)
│   ├── middleware/   # JWT auth, role authorization, and request logging middleware
│   ├── routes/       # FlightPHP REST route definitions & Swagger annotations
│   ├── services/     # Business logic & external API integration services
│   └── logs/         # Application logs (info.log, error.log - git-ignored except .gitkeep)
├── frontend/
│   ├── css/          # Stylesheets
│   ├── js/           # SPA router, API client services, and UI controllers
│   ├── static/       # Static assets (images, icons, fonts)
│   ├── views/        # Modular HTML view templates for the SPA
│   └── index.html    # Main SPA entry shell
├── docs/             # Project documentation, database schemas, and AI_LOG.md
├── AGENTS.md         # System architecture and AI coding rules
├── README.md         # Project overview and setup instructions
└── .gitignore        # Git ignore definitions
```

---

## Coding Rules for AI Agents

1. **Prepared Statements Only:** Always use PDO prepared statements (`prepare()` and `execute()` with bound parameters) inside DAO classes. Never concatenate variables directly into SQL strings.
2. **Strict Separation of Concerns:** Keep business logic inside `backend/services/` classes. Routes should only handle HTTP input/output, and DAOs should only execute database queries. Never mix HTML into PHP files.
3. **Standardized JSON & HTTP Codes:** Always return structured JSON responses (`Flight::json(...)`) with standard HTTP status codes (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `500 Internal Server Error`).
4. **Zero Database Error Leakage:** Never expose raw PDOExceptions, SQL queries, or stack traces to the client. Catch exceptions, log detailed diagnostics internally, and return a safe, user-friendly JSON error message.
5. **Comprehensive Logging:** Log all incoming API requests to `backend/logs/info.log` and all exceptions/failures to `backend/logs/error.log` with timestamps, HTTP method, URI, and context.
6. **Configuration Hygiene:** Never hardcode credentials or API keys in source files. Always read secrets from `backend/config/config.php`.
