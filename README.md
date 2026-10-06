# BAST — Digital Handover Management System

**Sistem Informasi Berita Acara Serah Terima**

A full-stack internal document management system for creating, managing, finalizing, generating, and archiving **Berita Acara Serah Terima (BAST)** through a structured, role-based, and auditable digital workflow.

Built with **Laravel, React, TypeScript, Inertia.js, Tailwind CSS, and relational database technologies**.

## Live Demo

**Live Application:**  
https://bast-production-6c5d.up.railway.app

The public deployment is intended exclusively as a portfolio demonstration and uses synthetic demo data.

Demo administrator account:

```text
Email: admin@bast.local
```

The demo password is intentionally not stored in this repository.

---

## About the Project

BAST is an internal document management application designed to organize the complete lifecycle of handover documents.

The system covers:

- structured BAST creation
- multi-step document forms
- automatic document numbering
- first and second party management
- dynamic handover items
- supporting attachments
- document preview
- server-side PDF generation
- document lifecycle management
- revision workflow
- archive management
- role-based access control
- user management
- master data management
- activity logging
- two-factor authentication
- responsive desktop and mobile interfaces

---

## Project Background

BAST was developed as a **2024 project** during an internship at **Diskominfo Kabupaten Cianjur**.

The project focuses on digitizing the Berita Acara Serah Terima workflow through a structured full-stack application covering document creation, lifecycle management, access control, PDF generation, archival, and administrative operations.

This repository is maintained as the portfolio and demonstration source for the project.

---

## Disclaimer

> **BAST is presented as a portfolio project based on work developed during the 2024 internship period. It is not an official production system or official repository of Diskominfo Kabupaten Cianjur.**

Institutional names and document terminology are retained only to preserve the project context.

The application uses synthetic demo data and does not contain real operational records or production credentials.

---

## Core Workflow

The primary document lifecycle is:

```text
Draft
  ↓
Finalized
  ↓
Completed
  ↓
Archived
```

Administrative users can also reopen finalized documents for revision:

```text
Finalized
  ↓
Reopen
  ↓
Draft / Revision
  ↓
Finalized
```

A finalized BAST can also be cancelled when required.

Lifecycle transitions are validated by backend application logic.

---

## Document Numbering

Document numbers are generated automatically when a BAST is finalized.

Example:

```text
001/BAST/DISKOMINFO/VIII/2024
```

The application uses dedicated document sequence records to maintain consistent numbering.

BAST records use **UUID route identifiers**, avoiding exposure of incremental database IDs in application URLs.

---

## Roles & Access

| Capability | Super Admin | Admin | Staff |
| --- | :---: | :---: | :---: |
| Dashboard | ✓ | ✓ | ✓ |
| Create BAST | ✓ | ✓ | ✓ |
| View all operational BAST | ✓ | ✓ | — |
| Manage own drafts | ✓ | ✓ | ✓ |
| Finalize BAST | ✓ | ✓ | Authorized |
| Generate PDF | ✓ | ✓ | Authorized |
| Complete BAST | ✓ | ✓ | Authorized |
| Access archive | ✓ | ✓ | Authorized |
| Restore archived BAST | ✓ | ✓ | — |
| Reopen finalized BAST | ✓ | ✓ | — |
| Cancel finalized BAST | ✓ | ✓ | — |
| Manage master data | ✓ | ✓ | — |
| View activity logs | ✓ | ✓ | — |
| Manage users | ✓ | — | — |

Authorization is enforced on the server side and does not rely only on frontend menu visibility.

---

## Main Features

### BAST Management

Each BAST can contain:

- BAST type
- department
- document date
- handover date
- handover location
- document title
- description
- first party
- second party
- one or more handover items
- quantity and units
- inventory or serial information
- supporting attachments

Draft documents remain editable until finalization.

### Multi-Step Form

BAST creation is divided into five steps:

1. Informasi
2. Pihak Terkait
3. Item
4. Lampiran
5. Review

This keeps complex administrative input easier to understand and manage.

### Document Lifecycle

Supported states include:

- Draft
- Finalized
- Completed
- Archived
- Cancelled

Supported operations include finalization, reopening, cancellation, completion, archival, and restoration.

### Document Revision

Authorized Admin and Super Admin users can reopen finalized BAST records.

The document returns to a revision state, can be updated, and may later be finalized again while retaining its existing sequence information.

### Document Preview & PDF

The application includes dedicated document preview and server-side PDF generation.

Generated documents contain:

- Kabupaten Cianjur institutional letterhead
- document title
- document number
- first party
- second party
- handover date and location
- item table
- signature sections
- document status indicators
- portfolio/demo identification

PDF generation uses **DomPDF**.

### Attachments

Supporting files can be attached to BAST records with:

- validation
- upload
- download
- deletion
- authorization
- relationship validation

Files are managed through the Laravel filesystem.

### Archive

Completed BAST records can be moved into a dedicated archive.

Archive is treated as part of the document lifecycle rather than as a trash area.

Authorized administrative users can restore archived records.

### Dashboard

The dashboard provides database-backed operational information including:

- document totals
- lifecycle status distribution
- monthly activity
- recent BAST records
- revision information
- recent activity

### Master Data

Admin and Super Admin users can manage:

- BAST types
- departments
- item categories
- units

### User Management

User management is restricted to Super Admin.

Supported operations include:

- create users
- assign roles
- assign departments
- manage NIP
- manage positions
- activate accounts
- deactivate accounts
- reset passwords
- reset two-factor authentication

Sensitive administrative protections are implemented for Super Admin operations.

### Activity Log

Important actions are recorded in an audit trail.

Activity records can include:

- user
- action
- description
- related resource
- previous values
- new values
- IP address
- user agent
- timestamp

### Authentication & Security

Security features include:

- authenticated application access
- disabled public registration
- active/inactive user enforcement
- server-side role authorization
- email verification
- password reset
- password confirmation
- login rate limiting
- password hashing
- two-factor authentication
- database-backed sessions
- protected administrative actions

---

## Responsive Interface

BAST is designed for both desktop and mobile use.

Responsive coverage includes:

- authentication
- dashboard
- navigation
- BAST list
- BAST detail
- create workflow
- edit workflow
- archive
- master data
- user management
- activity logs
- profile
- security settings

---

## UI / UX Direction

The interface uses a restrained institutional visual system focused on clarity, readability, consistency, and predictable administrative workflows.

Primary palette:

```text
Primary        #1D5D8F
Primary Hover  #174C76
Dark Blue      #123C5E
Soft Blue      #EAF3FA

Background     #F5F7F9
Surface        #FFFFFF
Text           #17212B
```

The web application uses its own **BAST identity**.

Government identity is not used as the application logo or favicon.

Institutional visual elements are limited to generated document context so the portfolio project is not presented as an official government software product.

---

## Technology Stack

### Backend

- Laravel 13
- PHP 8.3+
- Laravel Fortify
- Laravel Wayfinder
- Eloquent ORM
- DomPDF
- Pest
- Laravel Pint
- Larastan / PHPStan

### Frontend

- React 19
- TypeScript
- Inertia.js 3
- Tailwind CSS 4
- Radix UI
- Lucide Icons
- Sonner
- Vite

### Data

Primary local development uses **MySQL**.

The lightweight public portfolio deployment uses **SQLite with synthetic demo data** to keep the hosted demonstration self-contained.

---

## Architecture

```text
Browser
   │
   ▼
React + TypeScript
   │
   ▼
Inertia.js
   │
   ▼
Laravel
   │
   ├── Authentication
   ├── Middleware
   ├── Authorization
   ├── Validation
   ├── Controllers
   └── Services
   │
   ▼
Eloquent ORM
   │
   ▼
Relational Database
```

Laravel remains responsible for routing, authentication, validation, authorization, backend business logic, persistence, and document generation.

A separate REST API is not required for normal application navigation.

---

## Main Data Model

Core entities include:

```text
users
roles
departments

basts
bast_types
bast_parties
bast_items
bast_attachments

item_categories
units

activity_logs
document_sequences
```

---

## Local Development

### Requirements

- PHP 8.3+
- Composer
- Node.js
- npm
- MySQL
- Git

### Clone

```bash
git clone https://github.com/nafisajuliansahsaputra/bast.git
cd bast
```

### Install Dependencies

```bash
composer install
npm install
```

### Environment

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

### Database

Example local MySQL configuration:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bast
DB_USERNAME=root
DB_PASSWORD=
```

### BAST Configuration

```dotenv
BAST_DOCUMENT_CODE=BAST
BAST_INSTITUTION_CODE=DISKOMINFO

BAST_SUPER_ADMIN_EMAIL=admin@bast.local
BAST_SUPER_ADMIN_PASSWORD=choose-a-secure-local-password
```

The repository does not contain a universal Super Admin password.

Never commit credentials into version control.

### Prepare Database

```bash
php artisan migrate --seed
```

To rebuild local demo data:

```bash
php artisan migrate:fresh --seed
```

### Start Development

```bash
composer run dev
```

The application is available locally at:

```text
http://localhost:8000
```

---

## Demo Data

The project includes a dedicated synthetic demo dataset for development and portfolio presentation.

Demo records cover states including:

- Draft
- Revision
- Finalized
- Completed
- Archived
- Cancelled

The seed data also provides synthetic users, departments, external parties, BAST items, document sequences, activity logs, and attachments.

No real operational records are included.

---

## Quality Assurance

Run frontend formatting:

```bash
npm run format
```

Run the complete quality gate:

```bash
composer run ci:check
```

The quality gate validates:

- ESLint
- Prettier
- TypeScript
- Laravel configuration
- Laravel Pint
- PHPStan / Larastan
- Pest

Test counts are intentionally not hardcoded so documentation does not become stale.

---

## Deployment

The public portfolio demo is containerized with Docker and deployed through **Railway**.

Deployment-specific configuration includes:

- production HTTPS
- trusted reverse proxy handling
- Apache
- PHP 8.3
- built React/Vite assets
- SQLite demo persistence
- automated migrations and demo seeding

Production secrets are configured through Railway environment variables and are not committed to this repository.

---

## Portfolio Status

The project covers the complete primary application workflow:

```text
Authentication
      ↓
Dashboard
      ↓
BAST Management
      ↓
Document Lifecycle
      ↓
Preview / PDF
      ↓
Archive
      ↓
Administrative Management
```

The project is ready to be presented as a **full-stack development and UI/UX portfolio case study**.

---

## Repository Notes

This repository should not be interpreted as:

- an official Diskominfo Kabupaten Cianjur repository
- an official government production deployment
- the source of official government documents

It is maintained as the portfolio and demonstration repository for the **2024 BAST project**.

---

## License

**Proprietary — portfolio project.**

All rights reserved.