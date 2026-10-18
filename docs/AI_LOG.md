# AI Usage Log — Haustor.ba (IT-2001 Web Programming)

This document tracks the usage of AI coding assistants across project milestones, including tools utilized, automated tasks, key prompts, and instances where AI output required human review, correction, or refinement.

---

## Milestone 1: Project Scaffolding, Architecture & Documentation

### 1. Tools Used
- **Antigravity CLI (Google DeepMind Agentic Coding Assistant)**
- **Model:** Gemini

### 2. Tasks Automated
- **Scaffolding Repository Layout:** Automated creation of the separated `backend/` (`config`, `dao`, `middleware`, `routes`, `services`, `logs`), `frontend/` (`css`, `js`, `static`, `views`), and `docs/` directory hierarchies, including `.gitkeep` tracking for `backend/logs/`.
- **Setting Up Configuration Templates:** Generated `.gitignore` rules to protect sensitive environment files and created `backend/config/config.example.php` defining constants and configuration structures for `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `JWT_SECRET`, and `EXTERNAL_API_KEY`.
- **Drafting Initial Architectural Guidelines:** Authored `AGENTS.md` to enforce SPA/REST separation, PDO prepared statements, service-layer modularity, JWT RBAC middleware, and logging standards, alongside the initial `README.md` project overview.

### 3. Key Prompts Used
- *"Please set up the initial directory structure, base configuration templates, and mandatory AI documentation files for Milestone 1 of the IT-2001 course project 'Haustor.ba' on the current milestone1 branch..."*
- Specified strict directory layout (`backend/`, `frontend/`, `docs/`), `.gitignore` exclusions, `config.example.php` keys, `AGENTS.md` architectural rules, and `README.md` documentation sections.

### 4. Issues, Hallucinations, or Fixes

#### Case 1: [Placeholder — To be completed with specific revision case]
- **Context / Task:** *(Describe the task or component being generated, e.g., database schema design, DAO method, or SPA routing setup)*
- **AI Output Issue / Hallucination:** *(Describe what the AI generated inaccurately or suboptimally)*
- **Human Correction / Fix Applied:** *(Explain how the code or configuration was manually revised and verified)*

#### Case 2: [Placeholder — To be completed with specific revision case]
- **Context / Task:** *(Describe the second task or component being generated)*
- **AI Output Issue / Hallucination:** *(Describe what the AI generated inaccurately or suboptimally)*
- **Human Correction / Fix Applied:** *(Explain how the code or configuration was manually revised and verified)*
