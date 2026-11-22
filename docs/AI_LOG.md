# AI Usage Log — Haustor.ba (IT-2001 Web Programming)

**Course:** IT-2001 Web Programming
**Author:** Tarik Sorguč

This document tracks the usage of AI coding assistants across project milestones, including tools utilized, developer oversight principles, automated tasks, key prompts, and concrete instances where AI output required human review, correction, or refinement.

---

## Milestone 1: Project Scaffolding, Architecture, UI Design & Documentation

### 1. Tools Used & Roles

- **Gemini (Thought Partner & Architectural Advisor):** Used for brainstorming brand identity (_"Brza prijava, siguran dom"_), establishing visual guidelines (color palette, typography, tone), planning the 5-entity relational database schema and ERD structure, and designing the responsive dashboard layout concept.
- **Antigravity CLI (Implementation Assistant):** Used to scaffold the repository directory structure, generate base configuration and documentation templates, and execute HTML/CSS/JS code changes directly within VS Code based on strict developer instructions.

---

### 2. Workflow & Decision-Making Principle (Human-in-the-Loop)

Throughout Milestone 1, **I (the developer) maintained full architectural and design authority**, directing all technical and product decisions:

- **Layout Architecture:** Opted for a dedicated left-sidebar dashboard layout on desktop with a collapsible offcanvas drawer on mobile instead of a generic top navigation bar.
- **Brand & Visual Identity:** Selected the primary Navy (`#1E3A8A`), secondary Blue (`#3B82F6`), and safety Urgent Orange (`#EA580C`) color scheme paired with `Plus Jakarta Sans` and `Inter` typography.
- **Localization:** Directed full Bosnian localization (`lang="bs"`) across all views, forms, status badges, and Sarajevo-specific address examples to match the target market in Bosnia and Herzegovina.
- **Data Modeling:** Defined the 5 core relational entities (`users`, `buildings`, `apartments`, `categories`, `maintenance_requests`), composite uniqueness rules, and referential `ON DELETE` actions.

The AI tools acted strictly as **execution partners**—generating boilerplate markup, formatting Markdown/Mermaid diagrams, and writing CSS/JS code according to my exact specifications.

---

### 3. Key Tasks Automated

1. **Initial Project Directory Scaffolding & Configuration:** Automated creation of the separated `backend/` (`config`, `dao`, `middleware`, `routes`, `services`, `logs`), `frontend/` (`css`, `js`, `static`, `views`), and `docs/` directory hierarchies, `.gitignore` rules, and `backend/config/config.example.php`.
2. **Architectural & Database Schema Documentation:** Drafted `AGENTS.md` (enforcing SPA/REST separation, PDO prepared statements, service-layer modularity, JWT RBAC middleware, and logging standards), `docs/DATABASE_SCHEMA.md`, and the Mermaid `erDiagram` in `README.md`.
3. **Static SPA Templates & Bosnian Localization:** Generated and localized all five modular SPA views (`login.html`, `register.html`, `resident-dashboard.html`, `manager-dashboard.html`, `buildings-map.html`) and applied branded CSS utility classes (`.card-haustor`, `.badge-status-*`, `.badge-priority-*`).
4. **Responsive Sidebar & Offcanvas Refactoring:** Refactored `frontend/index.html`, `frontend/css/style.css`, and `frontend/js/app.js` to implement a fixed 260px desktop left sidebar and a 280px mobile Bootstrap offcanvas drawer with automatic close-on-navigation behavior.

---

### 4. Key Prompts & Refinement Case Studies

#### Key Prompts Used

- _"Please set up the initial directory structure, base configuration templates, and mandatory AI documentation files for Milestone 1 of the IT-2001 course project 'Haustor.ba' on the current milestone1 branch..."_
- _"Please update the repository documentation to include the draft Entity-Relationship Diagram (ERD) and entity specifications for Milestone 1 in README.md and docs/DATABASE_SCHEMA.md..."_
- _"Please localize the entire frontend of Haustor.ba into the Bosnian language across all views, templates, and navigation components on the milestone1 branch..."_
- _"Please apply the official brand identity, color scheme, and typography for Haustor.ba across the milestone1 branch..."_
- _"Please refactor the frontend of Haustor.ba into a modern, mobile-responsive layout featuring a left sidebar navigation on desktop and a collapsible offcanvas drawer on mobile, localized in Bosnian..."_

---

#### Case 1: Full Bosnian UI Localization & Domain Terminology

- **Context / Task:** Generating the initial static SPA templates (`frontend/index.html` and `frontend/views/*.html`) for authentication, resident ticket submission, manager triage, and the buildings map.
- **AI Output Issue / Refinement Needed:** The initial view templates were drafted in English (e.g., _"Sign In"_, _"Submit Maintenance Ticket"_, _"Pending / In Progress / Resolved"_, _"Property Manager"_) with mixed language strings and an empty `buildings-map.html` template. This did not reflect the local Bosnian terminology used by residents (_stanari_) and building managers (_upravitelji_) in Sarajevo.
- **Human Correction / Fix Applied:** Directed a complete localization pass across `frontend/index.html`, `frontend/js/app.js`, and all five view files in `frontend/views/`. Set `<html lang="bs">`, translated all headings, form labels, placeholders, role selectors (`Stanar` / `Upravitelj zgrade`), maintenance categories (_Vodoinstalacije_, _Elektroinstalacije_, _Lift_, _Krov i zajedničke prostorije_, _Grijanje_), priority descriptions, and status badges (_Na čekanju_, _U toku_, _Riješeno_, _Odbijeno_), and populated `buildings-map.html` with Sarajevo building examples.

#### Case 2: Layout Architecture & Mobile Offcanvas UX

- **Context / Task:** Designing the main application shell (`frontend/index.html`), navigation styling (`frontend/css/style.css`), and hash-based SPA router (`frontend/js/app.js`).
- **AI Output Issue / Refinement Needed:** The initial layout used a standard horizontal top navbar (`navbar-expand-lg`). While functional for simple landing pages, a top navbar is suboptimal for a data-dense property management dashboard and can remain expanded after clicking hash links in a Single Page Application on mobile devices. Additionally, combining Bootstrap's `.offcanvas-lg` with `.d-flex` on `<aside>` requires explicit responsive handling so the offcanvas stays hidden on viewports `< 992px` when closed.
- **Human Correction / Fix Applied:** Directed a full layout refactor to replace the top navbar with a persistent 260px left sidebar (`aside.sidebar-container`) on desktop (`>= 992px`) and a sticky mobile top header triggering a 280px Bootstrap `#sidebarOffcanvas` drawer on screens `< 992px`. Added CSS rules for `.nav-pills` active/hover states and responsive visibility, and updated `navigate()` in `frontend/js/app.js` to programmatically invoke `offcanvasInstance.hide()` when `window.innerWidth < 992` so the mobile drawer closes automatically after selecting a route.

---

## Milestone 2: Database Schema (`haustor.sql`) & PDO Data Access Object (DAO) Layer

### 1. Tools Used & Roles

- **Antigravity CLI (Implementation Assistant):** Used to generate the MySQL relational schema and Bosnian seed data script (`backend/haustor.sql`), configure local database credentials (`backend/config/config.php`), and implement the PDO-based Data Access Object hierarchy (`BaseDao` and the 5 entity DAO classes in `backend/dao/`).

---

### 2. Key Tasks Automated

1. **Relational Database DDL & Seed Script (`backend/haustor.sql`):** Generated `CREATE TABLE` statements for all 5 entities (`users`, `buildings`, `apartments`, `categories`, and `maintenance_requests`) using `InnoDB` and `utf8mb4_unicode_ci`, including `ENUM` columns (`role`, `default_priority`, `priority`, `status`), composite uniqueness constraints, foreign key `ON DELETE` / `ON UPDATE` actions, and localized Bosnian `INSERT INTO` seed records for Sarajevo residential buildings.
2. **Base PDO Data Access Layer (`backend/dao/BaseDao.php`):** Implemented a centralized PDO connection class reading credentials from `backend/config/config.php` with `PDO::ATTR_EMULATE_PREPARES => false` and `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, alongside reusable prepared-statement helper methods (`execute()`, `query()`, `queryUnique()`, `insert()`, `update()`, `delete()`).
3. **Entity-Specific DAO Classes (`backend/dao/`):** Created `UserDao.php`, `BuildingDao.php`, `ApartmentDao.php`, `CategoryDao.php`, and `MaintenanceRequestDao.php` extending `BaseDao`, each providing full CRUD operations (`add($data)`, `get_all()`, `get_by_id($id)`, `update($id, $data)`, `delete($id)`) and relational `JOIN` lookups via prepared statements.

---

### 3. Key Prompts Used

- _"Please implement the backend Database schema and DAO (Data Access Object) layer for Milestone 2 of the Haustor.ba project on the current milestone2 branch..."_

---

### 4. Issues & Fixes (Human Review & Corrections of AI Output)

#### Case 1: Foreign Key Constraint Fix (`backend/haustor.sql`)

- **Context / Task:** Executing the `DROP TABLE IF EXISTS` and `CREATE TABLE` statements in `backend/haustor.sql` to initialize and re-seed the relational schema.
- **AI Output Issue / Refinement Needed:** The initial SQL script attempted to drop tables in the wrong order, causing a foreign key constraint failure when re-importing the schema over an existing database.
- **Human Correction / Fix Applied:** Directed the AI to add `SET FOREIGN_KEY_CHECKS = 0;` at the top of `haustor.sql` and `SET FOREIGN_KEY_CHECKS = 1;` at the bottom so that tables with foreign key dependencies can be cleanly dropped, recreated, and seeded without referential constraint errors.

#### Case 2: PDO Fetch Mode Adjustment (`backend/dao/BaseDao.php`)

- **Context / Task:** Configuring the PDO database connection inside `BaseDao::__construct()` (`backend/dao/BaseDao.php`).
- **AI Output Issue / Refinement Needed:** The generated `BaseDao` initially lacked the default fetch mode configuration, causing PDO queries to default to `PDO::FETCH_BOTH` (returning both associative keys and duplicate numeric-indexed keys for every column).
- **Human Correction / Fix Applied:** Directed the AI to explicitly set `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` in the PDO connection options so that the DAO layer returns clean associative arrays for the frontend API instead of duplicate indexed arrays.

---

## Milestone 3: Service Layer, Middleware, FlightPHP REST API & OpenAPI Documentation

### 1. Tools Used & Roles

- **Antigravity CLI (Implementation Assistant):** Used to audit Milestone 2 readiness, implement the 5 entity service classes, build request logging and JSON error-handling middleware, configure Composer and FlightPHP v3 REST CRUD endpoints, draft the OpenAPI 3.0.3 specification, and run automated syntax and HTTP smoke tests across the backend.

---

### 2. Milestone 2 Readiness Verification

Before starting Milestone 3 on the new `milestone3` branch, a full audit of the Milestone 2 codebase was performed across `backend/config/config.example.php`, `backend/haustor.sql`, `docs/DATABASE_SCHEMA.md`, `backend/dao/BaseDao.php`, and all five entity DAOs (`UserDao`, `BuildingDao`, `ApartmentDao`, `CategoryDao`, `MaintenanceRequestDao`):

- Confirmed all 5 database tables (`users`, `buildings`, `apartments`, `categories`, `maintenance_requests`), foreign keys, and `ENUM` constraints aligned with the DAO classes.
- Verified consistent CRUD method signatures (`get_all()`, `get_by_id($id)`, `add($data)`, `update($id, $data)`, `delete($id)`) and domain lookup helpers (`get_by_email`, `get_by_role`, `get_by_manager`, `get_by_building`, `get_by_resident`, `get_by_name`, `get_by_user`, `get_by_status`).
- Confirmed `UserDao` omits `password_hash` from standard read queries, all 6 DAO files pass `php -l` with zero syntax errors, and no Milestone 2 schema or DAO fixes were required prior to starting Milestone 3.

---

### 3. Workflow, 5-Commit Structure & Human-in-the-Loop Oversight

To keep the implementation modular, auditable, and easy to review, **I (the developer) directed a strict 5-commit milestone workflow** on the `milestone3` branch while enforcing the architectural rules in `AGENTS.md` (clean SPA/REST separation, thin routes, business logic isolated in services, standardized JSON status codes, and zero database error leakage):

1. **Commit 1 — Service Layer (`feat(milestone3): add service layer for core entities`):** Added `UserService`, `BuildingService`, `ApartmentService`, `CategoryService`, and `MaintenanceRequestService` in `backend/services/` with required-field/type/enum validation, column whitelisting, and clean exception signaling.
2. **Commit 2 — Middleware (`feat(milestone3): add request logging and JSON error middleware`):** Added `LoggingMiddleware.php` (writing timestamped method/URI/status/outcome lines to `backend/logs/info.log`) and `ErrorMiddleware.php` (logging full diagnostics to `backend/logs/error.log` while returning client-safe JSON error payloads).
3. **Commit 3 — FlightPHP REST API (`feat(milestone3): implement FlightPHP REST CRUD endpoints`):** Configured `composer.json` (`flightphp/core` `^3.0` with classmap autoloading), created `backend/index.php` and `backend/.htaccess`, and added thin CRUD route files in `backend/routes/` for `/users`, `/buildings`, `/apartments`, `/categories`, and `/maintenance-requests`.
4. **Commit 4 — API Documentation (`docs(milestone3): add OpenAPI specification for REST API`):** Created `docs/openapi.yaml` (OpenAPI 3.0.3) documenting all 11 paths, query parameters, request/response schemas, and standard `200`/`201`/`400`/`404`/`500` JSON responses.
5. **Commit 5 — AI Documentation (`docs(milestone3): record Milestone 3 workflow and refinements in AI_LOG`):** Documented the Milestone 2 readiness check, 5-commit execution structure, oversight rules, and concrete technical refinements below.

---

### 4. Concrete Refinements & Issues Resolved During Implementation

#### Case 1: FlightPHP v3 Response Buffering in `ErrorMiddleware` & Standardized JSON `405` Handling

- **Context / Task:** Integrating `LoggingMiddleware` and `ErrorMiddleware` with `flightphp/core` v3.19.5 inside `backend/index.php` and verifying live HTTP error responses (`400`, `404`, `405`, `500`) against PHP's built-in development server.
- **Issue Identified:** Inspecting FlightPHP v3's `Engine::_json()` revealed that Flight v3 defaults to `v2_output_buffering = false`, so `Flight::json()` writes to the response object without immediately flushing output—relying on `Engine::_stop()` at the end of normal `start()` execution. When an exception interrupts `start()` and invokes `Flight::map('error', ...)`, `_stop()` does not run, risking an unsent JSON body. Additionally, Flight v3's default `methodNotFound` handler emitted plain text instead of JSON for unsupported HTTP verbs (e.g., `PATCH /users`).
- **Refinement Applied:** Updated `ErrorMiddleware::sendJsonError()` to explicitly clear any partial response body, invoke `Flight::json($payload, $statusCode)`, and call `Flight::response()->send()` if not yet sent. Also mapped `Flight::map('methodNotFound', ...)` to emit a clean `405` JSON payload and log the request once via `LoggingMiddleware`'s per-request guard, then verified `200`, `400`, `404`, `405`, and safe `500` JSON responses via live HTTP smoke testing.

#### Case 2: Pre-Query Domain Validation, Safe `500` Masking & Lazy Service Registration

- **Context / Task:** Designing the service layer (`backend/services/`) and wiring service instantiation in `backend/index.php` on top of the existing Milestone 2 DAO layer.
- **Issue Identified:** Because `BaseDao::execute()` intentionally catches `PDOException` and throws a generic `RuntimeException` to prevent SQL error leakage, relying on MySQL unique constraints alone for duplicate user emails, category names, or `(building_id, apartment_number)` pairs would return opaque `500` errors instead of actionable `400 Bad Request` validation messages. Furthermore, eagerly instantiating all 5 services on every request in `backend/index.php` would open unnecessary PDO connections even on `GET /`, `404`, or malformed-JSON `400` requests.
- **Refinement Applied:** Implemented explicit uniqueness checks (`get_by_email`, `get_by_name`, and `get_by_building` apartment-number checks), strict column whitelisting, bcrypt password hashing in `UserService`, and automatic `resolved_at` timestamping in `MaintenanceRequestService`. Registered all 5 services lazily via `Flight::register(...)` in `backend/index.php`, and verified both full CRUD/validation behavior across all 5 services and zero-leakage `500` masking when the database is unavailable.

