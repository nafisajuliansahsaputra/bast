# BAST — Digital Handover Management System


**Sistem Informasi Berita Acara Serah Terima**


A full-stack internal document management system for creating, managing,
finalizing, generating, and archiving **Berita Acara Serah Terima (BAST)**
through a structured and auditable digital workflow.


Built with **Laravel, React, TypeScript, Inertia.js, and MySQL**.


---


## About the Project


BAST is an internal management system designed to organize the lifecycle of
handover documents through a structured, traceable, and role-based workflow.


The application covers the process from initial document creation through
finalization, completion, archival, revision, document generation, and
administrative monitoring.


Core capabilities include:


- structured BAST creation
- multi-step document forms
- automatic document numbering
- first and second party management
- dynamic handover items
- supporting attachments
- document preview
- PDF generation
- document lifecycle management
- archive management
- role-based access control
- user management
- master data management
- activity logging
- two-factor authentication
- responsive desktop and mobile interfaces


---


## Project Background


The original concept for this system was developed during an internship at
**Diskominfo Kabupaten Cianjur in January 2024**.


The original source code is no longer available.


In 2026, the project was independently reconstructed from the original concept
and workflow as a modern full-stack portfolio project.


Rather than reproducing the previous implementation exactly, the reconstruction
focuses on improving:


- application architecture
- UI/UX consistency
- responsive design
- authorization
- document lifecycle management
- validation
- application security
- auditability
- automated testing
- developer experience


This repository contains the **2026 reconstruction**, not the original 2024
internship source code.


---


## Disclaimer


> **This project is an independent reconstruction of a system originally developed during an internship in 2024. It is presented as a portfolio project and is not an official production system of Diskominfo Kabupaten Cianjur.**


Institutional names and document terminology are retained only to preserve the
historical context of the original project.


The repository uses synthetic demo data and does not contain real operational
records or production credentials.


---


## Core Workflow


The primary BAST lifecycle is:


```text
Draft
  ↓
Finalized
  ↓
Completed
  ↓
Archived

A finalized document can also enter an administrative revision flow:

Finalized
  ↓
Reopen
  ↓
Draft / Revision
  ↓
Finalized

A finalized document may also be:

Cancelled

Invalid lifecycle transitions are rejected by backend rules.

When a previously numbered BAST is reopened for revision, the system can retain
its existing document sequence when the document is finalized again.

Document Numbering

BAST generates document numbers automatically when a draft is finalized.

Example:

001/BAST/DISKOMINFO/VIII/2026

The numbering process uses a dedicated document sequence record to help maintain
consistent sequential numbering.

BAST records also use UUID-based route identifiers rather than exposing
incremental database IDs in application URLs.

Roles & Access

The application provides three internal roles.

Capability	Super Admin	Admin	Staff
Access dashboard	✓	✓	✓
Create BAST	✓	✓	✓
View operational BAST	All	All	Own / authorized
Edit draft BAST	✓	✓	Own drafts
Finalize BAST	✓	✓	Authorized
Generate document / PDF	✓	✓	Authorized
Complete BAST	✓	✓	Authorized
Access archive	✓	✓	Authorized
Restore archived BAST	✓	✓	—
Reopen finalized BAST	✓	✓	—
Cancel finalized BAST	✓	✓	—
Manage master data	✓	✓	—
View activity logs	✓	✓	—
Manage users	✓	—	—

Authorization is enforced on the server side and does not rely only on
frontend menu visibility.

Main Features
BAST Management

Each BAST can contain structured information such as:

BAST type
department
document date
handover date
handover location
document title
description
first party
second party
one or more handover items
item categories
units
quantity
inventory information
serial information
supporting attachments

Draft documents remain editable until they are finalized.

Multi-Step Document Form

The BAST creation workflow is divided into five logical steps:

1. Informasi
2. Pihak Terkait
3. Item
4. Lampiran
5. Review

This approach keeps a relatively complex administrative form easier to
understand and complete.

Existing BAST records also use a structured editing workflow.

Document Lifecycle

Supported document states include:

Draft
Finalized
Completed
Archived
Cancelled

Supported lifecycle operations include:

finalize a draft
reopen a finalized document
cancel a finalized document
mark a finalized document as completed
archive a completed document
restore an archived document

Lifecycle transitions are validated by backend application logic.

Document Revision

Authorized Admin and Super Admin users can reopen finalized documents when a
revision is required.

The BAST returns to a draft/revision state so its content can be updated before
being finalized again.

The system maintains the existing sequence information for a previously
numbered document during this revision workflow.

Document Preview & PDF Generation

BAST includes a dedicated document preview and server-side PDF generator.

Generated documents include:

Kabupaten Cianjur institutional letterhead
document title
document number
first party information
second party information
handover date and location
handover item table
signature sections
draft or cancellation indicators when applicable
portfolio/demo identification

PDF files are generated server-side using DomPDF.

Draft records can be previewed but are not downloadable as finalized PDF
documents.

Attachments

Supporting documents can be associated with BAST records.

Attachment handling includes:

file validation
upload
download
deletion
authorization checks
relationship validation
application-managed storage

Supported files are stored through the Laravel filesystem rather than being
exposed as unmanaged public file paths.

Archive Management

Completed BAST records can be moved into a dedicated archive.

Archived documents remain available for historical reference while being
separated from the primary operational BAST list.

Authorized administrative roles can restore archived records.

Archive is treated as part of the document lifecycle rather than as a trash
area.

Dashboard

The dashboard provides database-backed information about current BAST
operations.

It presents information such as:

document totals
lifecycle status distribution
recent BAST records
draft information
revision information
operational document activity

The dashboard uses actual application data rather than static interface
placeholders.

Master Data

Admin and Super Admin users can manage reference data used throughout the
system.

Current master data includes:

BAST types
departments
item categories
units

Master records support controlled creation and management for use across BAST
documents.

User Management

User management is restricted to the Super Admin role.

Supported operations include:

create internal users
assign roles
assign departments
manage NIP
manage positions
manage phone information
update account information
activate accounts
deactivate accounts
reset passwords
reset two-factor authentication
view user details

Administrative protections are implemented for sensitive Super Admin
operations, including protection for the last active Super Admin account.

Activity Log

Important application actions are recorded in an audit trail.

Tracked activity can include:

BAST creation
BAST updates
lifecycle transitions
attachment activity
PDF generation
user administration
master data changes
authentication activity
profile changes
password changes
security operations

Activity records can contain:

acting user
action
description
related resource
previous values
new values
IP address
user agent
timestamp

This provides traceability for important administrative actions.

Authentication & Security

BAST includes multiple security controls appropriate for an internal management
application.

Implemented controls include:

authenticated application access
public registration disabled
active / inactive user enforcement
server-side role authorization
email verification
password reset
password confirmation
login rate limiting
two-factor authentication
password hashing
protected user-management operations
database-backed sessions

Application accounts are created and managed internally rather than through a
public registration flow.

Two-Factor Authentication

Users can configure two-factor authentication from the Security section.

The security flow is built on Laravel Fortify and supports authenticator-based
two-factor authentication.

Administrative users can also reset another user's two-factor configuration
when permitted by role and account-protection rules.

Responsive Interface

BAST is designed to remain usable across desktop and mobile layouts.

Responsive coverage includes:

authentication
application navigation
dashboard
BAST list
BAST detail
create workflow
edit workflow
archive
master data
user management
activity logs
profile settings
security settings

The goal is to preserve administrative usability without requiring a
desktop-only environment.

UI / UX Direction

The interface uses a restrained institutional design focused on clarity,
consistency, readability, and predictable administrative workflows.

Primary palette:

Primary        #1D5D8F
Primary Hover  #174C76
Dark Blue      #123C5E
Soft Blue      #EAF3FA


Background     #F5F7F9
Surface        #FFFFFF
Text           #17212B

The application uses its own BAST identity.

Government identity is not used as the application logo or favicon.

Institutional branding is limited to the generated BAST document context so the
portfolio reconstruction is not presented as an official government software
product.

Technology Stack
Backend
Laravel 13
PHP 8.3+
Laravel Fortify
Laravel Wayfinder
Eloquent ORM
MySQL
DomPDF
Larastan / PHPStan
Laravel Pint
Pest
Frontend
React 19
TypeScript
Inertia.js 3
Tailwind CSS 4
Radix UI
Lucide Icons
Sonner
Vite
Architecture

BAST uses a Laravel + Inertia architecture.

Browser
   │
   ▼
React + TypeScript
   │
   ▼
Inertia.js
   │
   ▼
Laravel Application
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
MySQL

This architecture provides a React-based interface while Laravel remains
responsible for routing, authentication, validation, authorization, backend
application logic, persistence, and document generation.

A separate REST API is not required for normal application navigation.

Main Data Model

Core relational entities include:

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

BAST records are related to:

creator
department
BAST type
involved parties
handover items
attachments
lifecycle actors
activity history
Local Development
Requirements

Make sure the following software is available:

PHP 8.3+
Composer
Node.js
npm
MySQL
Git

A Windows environment such as Laragon can be used, but it is not required.

1. Clone the Repository
git clone https://github.com/nafisajuliansahsaputra/bast.git
cd bast
2. Install Dependencies
composer install
npm install
3. Create Environment File

Windows PowerShell:

Copy-Item .env.example .env

Linux / macOS:

cp .env.example .env

Generate the Laravel application key:

php artisan key:generate
4. Configure MySQL

Create a local MySQL database named:

bast

Example configuration:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bast
DB_USERNAME=root
DB_PASSWORD=

Adjust these values according to your local database environment.

5. Configure BAST

Application-specific document configuration:

BAST_DOCUMENT_CODE=BAST
BAST_INSTITUTION_CODE=DISKOMINFO

Configure the initial local Super Admin account:

BAST_SUPER_ADMIN_EMAIL=admin@bast.local
BAST_SUPER_ADMIN_PASSWORD=choose-a-secure-local-password

The repository does not contain a universal Super Admin password.

Never commit local credentials into version control.

6. Prepare the Database

Run migrations and seed the local environment:

php artisan migrate --seed

The seed process prepares:

application roles
initial master data
departments
local Super Admin account
synthetic Admin accounts
synthetic Staff accounts
synthetic BAST records
multiple BAST lifecycle states
activity history
demo attachments

The generated records are intended for local development and portfolio
presentation.

To completely reset the local database:

php artisan migrate:fresh --seed

This command deletes the current local database tables before rebuilding and
reseeding them.

7. Start Development
composer run dev

The development command starts the Laravel application server, queue listener,
and Vite development server.

The application is available by default at:

http://localhost:8000
Development Commands

Format frontend resources:

npm run format

Check frontend formatting:

npm run format:check

Run frontend linting:

npm run lint:check

Run TypeScript validation:

npm run types:check

Format PHP:

composer run lint

Check PHP formatting without modifying files:

composer run lint:check

Run PHP static analysis:

composer run types:check

Run the complete project quality gate:

composer run ci:check
Quality Assurance

The project includes automated frontend and backend quality checks.

The complete quality gate validates:

ESLint
Prettier
TypeScript
Laravel configuration
Laravel Pint
PHPStan / Larastan
Pest

Test counts are intentionally not hardcoded in this README so the
documentation does not become stale as the project evolves.

Automated Testing

The test suite covers important application behavior such as:

authentication
login rate limiting
email verification
password reset
password confirmation
two-factor authentication
role authorization
active account enforcement
BAST creation
BAST validation
BAST editing
ownership restrictions
finalization
document numbering
completion
archive operations
archive restoration
finalized-document reopening
cancellation
attachment operations
document preview
PDF generation
dashboard access
master data management
user management
profile management
security settings
activity logging

Run the application test suite through:

composer run ci:check
Demo Data

The repository includes a dedicated demo data seeder intended to make the
application usable immediately after local setup.

The seeded dataset contains synthetic information only.

Demo data includes multiple examples across states such as:

Draft
Revision
Finalized
Completed
Archived
Cancelled

It also includes:

synthetic internal users
synthetic external parties
departments
handover items
document sequences
activity logs
supporting demo attachments

The purpose of this dataset is to provide realistic development and portfolio
content without requiring manual data entry.

Portfolio Status

The current reconstruction covers the complete primary application flow:

Authentication
        ↓
Dashboard
        ↓
BAST Management
        ↓
Document Lifecycle
        ↓
Document Preview / PDF
        ↓
Archive
        ↓
Administrative Management

The project currently includes:

completed core BAST workflow
role-based authorization
lifecycle management
archive management
activity auditing
user administration
master data management
authentication and security
responsive layouts
dedicated BAST branding
synthetic portfolio data
official-style BAST document output
automated quality checks

Additional screenshots and case-study presentation assets can be stored under
docs/ without changing the core application architecture.

Repository Notes

This repository is intended as a software engineering and UI/UX portfolio
project.

It should not be interpreted as:

an official Diskominfo Kabupaten Cianjur repository
an official government production deployment
the source of official government documents
the original 2024 internship repository

The project demonstrates the independent reconstruction and modernization of an
earlier internship system concept.

License

Proprietary — portfolio project.

All rights reserved.