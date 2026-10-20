# AI Usage Log — Haustor.ba (IT-2001 Web Programming)

**Course:** IT-2001 Web Programming  
**Author:** Tarik Sorguč  

This document tracks the usage of AI coding assistants across project milestones, including tools utilized, developer oversight principles, automated tasks, key prompts, and concrete instances where AI output required human review, correction, or refinement.

---

## Milestone 1: Project Scaffolding, Architecture, UI Design & Documentation

### 1. Tools Used & Roles
- **Gemini (Thought Partner & Architectural Advisor):** Used for brainstorming brand identity (*"Brza prijava, siguran dom"*), establishing visual guidelines (color palette, typography, tone), planning the 5-entity relational database schema and ERD structure, and designing the responsive dashboard layout concept.
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
- *"Please set up the initial directory structure, base configuration templates, and mandatory AI documentation files for Milestone 1 of the IT-2001 course project 'Haustor.ba' on the current milestone1 branch..."*
- *"Please update the repository documentation to include the draft Entity-Relationship Diagram (ERD) and entity specifications for Milestone 1 in README.md and docs/DATABASE_SCHEMA.md..."*
- *"Please localize the entire frontend of Haustor.ba into the Bosnian language across all views, templates, and navigation components on the milestone1 branch..."*
- *"Please apply the official brand identity, color scheme, and typography for Haustor.ba across the milestone1 branch..."*
- *"Please refactor the frontend of Haustor.ba into a modern, mobile-responsive layout featuring a left sidebar navigation on desktop and a collapsible offcanvas drawer on mobile, localized in Bosnian..."*

---

#### Case 1: Full Bosnian UI Localization & Domain Terminology
- **Context / Task:** Generating the initial static SPA templates (`frontend/index.html` and `frontend/views/*.html`) for authentication, resident ticket submission, manager triage, and the buildings map.
- **AI Output Issue / Refinement Needed:** The initial view templates were drafted in English (e.g., *"Sign In"*, *"Submit Maintenance Ticket"*, *"Pending / In Progress / Resolved"*, *"Property Manager"*) with mixed language strings and an empty `buildings-map.html` template. This did not reflect the local Bosnian terminology used by residents (*stanari*) and building managers (*upravitelji*) in Sarajevo.
- **Human Correction / Fix Applied:** Directed a complete localization pass across `frontend/index.html`, `frontend/js/app.js`, and all five view files in `frontend/views/`. Set `<html lang="bs">`, translated all headings, form labels, placeholders, role selectors (`Stanar` / `Upravitelj zgrade`), maintenance categories (*Vodoinstalacije*, *Elektroinstalacije*, *Lift*, *Krov i zajedničke prostorije*, *Grijanje*), priority descriptions, and status badges (*Na čekanju*, *U toku*, *Riješeno*, *Odbijeno*), and populated `buildings-map.html` with Sarajevo building examples.

#### Case 2: Layout Architecture & Mobile Offcanvas UX
- **Context / Task:** Designing the main application shell (`frontend/index.html`), navigation styling (`frontend/css/style.css`), and hash-based SPA router (`frontend/js/app.js`).
- **AI Output Issue / Refinement Needed:** The initial layout used a standard horizontal top navbar (`navbar-expand-lg`). While functional for simple landing pages, a top navbar is suboptimal for a data-dense property management dashboard and can remain expanded after clicking hash links in a Single Page Application on mobile devices. Additionally, combining Bootstrap's `.offcanvas-lg` with `.d-flex` on `<aside>` requires explicit responsive handling so the offcanvas stays hidden on viewports `< 992px` when closed.
- **Human Correction / Fix Applied:** Directed a full layout refactor to replace the top navbar with a persistent 260px left sidebar (`aside.sidebar-container`) on desktop (`>= 992px`) and a sticky mobile top header triggering a 280px Bootstrap `#sidebarOffcanvas` drawer on screens `< 992px`. Added CSS rules for `.nav-pills` active/hover states and responsive visibility, and updated `navigate()` in `frontend/js/app.js` to programmatically invoke `offcanvasInstance.hide()` when `window.innerWidth < 992` so the mobile drawer closes automatically after selecting a route.
