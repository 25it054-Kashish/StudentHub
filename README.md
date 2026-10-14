# StudentHub Portal — Complete Full-Stack Web Application

**Faculty of Technology and Engineering (FTE) &bull; CHARUSAT University**  
**Subject:** Web Development Frameworks (ITUE203) &bull; **Semester:** 3rd, 2026-27 (ODD)  
**Academic Target:** B.Tech Computer Engineering / Information Technology / AI &amp; ML  

---

## 📌 Executive Summary

**StudentHub** is an enterprise-grade, accessibility-ready, and responsive digital campus portal engineered to unify academic workflows, student onboarding, technical event reservations, continuous internal evaluations (CIE), and administrative moderation.

Developed strictly in alignment with the official **15-Practical Syllabus** of CHARUSAT FTE, the project features a decoupled dual-mode architecture:
1. **Frontend Showcase Mode:** Pure HTML5, modern CSS3 (Flexbox/Grid), and vanilla JavaScript ES6+ leveraging Fetch API, client-side caching, canvas CAPTCHA, and DOM interactivity. Runs directly in any web browser without server dependencies.
2. **Dynamic Full-Stack Mode:** PHP 8 backend with MySQL database integration (PDO &amp; MySQLi prepared statements), secure Bcrypt password hashing, session fixation defense, RBAC access control, multipart poster uploads, RESTful JSON APIs, and audit logging. Runs seamlessly on XAMPP/Apache.

---

## 📋 Comprehensive Practical Syllabus Mapping (1 to 15)

| Practical No. | Practical Title | Core Concepts | Implementation Location |
|---|---|---|---|
| **Prac 1** | Project Initiation, Requirements, Sitemap, Wireframe | Requirements, Folder Hierarchy, Git | `README.md`, `docs/`, `docs/wireframes/` |
| **Prac 2** | Semantic HTML5 Pages with Accessibility Structure | Semantic Tags (`header`, `nav`, `main`, `section`, `article`, `aside`, `footer`), ARIA, Skip-link, Breadcrumbs | `pages/*.html` (10+ Complete Pages) |
| **Prac 3** | Responsive UI Design using CSS3, Grid &amp; Flexbox | CSS Variables, Dark/Light Themes, Mobile-First, Media Queries | `css/style.css` |
| **Prac 4** | JavaScript DOM Manipulation &amp; UI Interactivity | Digital Clock, Greeting, Theme Switcher with `localStorage`, Event Slider, Accordion, Modal Dialogs, Toast Notifications | `js/script.js` |
| **Prac 5** | Form with Real-Time Validation &amp; Error Handling | Regex Validation, Password Strength Meter, Custom HTML5 Canvas CAPTCHA | `pages/register.html`, `js/validation.js` |
| **Prac 6** | Rendering External JSON Data using Fetch API | Fetch API, `filter()`, `sort()`, `map()`, `slice()`, Pagination, Search with Debounce, `localStorage` Cache | `pages/events.html`, `js/render-data.js`, `data/*.json` |
| **Prac 7** | PHP Form Processing &amp; CSV/JSON File Storage | POST Sanitization, File Writing (`registrations.csv`, `contacts.csv`, `submissions.json`), Webpage Viewer | `php/process_register.php`, `php/process_contact.php`, `pages/submissions.html` |
| **Prac 8** | MySQL Schema Design, ER Model, PDO Connectivity | 3NF Schema, Primary/Foreign Keys, Seed Data, PDO &amp; MySQLi Dual Connection Class | `database/studenthub.sql`, `database/er_diagram_schema.md`, `php/db.php` |
| **Prac 9** | Secure User Registration with Password Hashing | `password_hash()` (Bcrypt), Duplicate Email Checks, DB Insertions | `php/process_register.php` |
| **Prac 10** | Secure Login/Logout with Sessions, RBAC &amp; Timeout | `password_verify()`, `session_regenerate_id()`, 15-Min Inactivity Timeout, Remember-Me Tokens | `php/login_action.php`, `php/logout.php`, `php/auth_check.php` |
| **Prac 11** | Student Management CRUD with Search &amp; Filter | MySQLi Prepared Statements, Search, Dept Filter, Soft-Delete Toggle, CSV Roster Exporter | `manage_students.php`, `php/export_students.php` |
| **Prac 12** | Event Management CRUD with Poster Upload Validation | Multipart Uploads, MIME Validation (`finfo`), 2MB Size Limit, Safe Renaming, Uploads Directory Protection | `manage_events.php`, `php/event_crud.php`, `uploads/.htaccess` |
| **Prac 13** | PHP JSON API &amp; AJAX CRUD without Page Reload | RESTful Endpoints (`GET`, `POST`, `PUT`, `DELETE`), HTTP Status Codes, Async Fetch Updates | `api/events.php`, `api/students.php`, `js/ajax-crud.js` |
| **Prac 14** | Admin Dashboard with RBAC, Dynamic Menu &amp; Audit Log | Real-Time DB Aggregates, Dynamic Menu, Audit Trail (`audit_logs`), HTML5 Canvas Trend Chart | `admin_dashboard.php`, `api/stats.php` |
| **Prac 15** | Final Integration, Testing, Documentation &amp; Deployment | Holistic Integration, Dual-Mode Compatibility, Viva Guide | Entire Project &bull; `index.php` &bull; `index.html` |

---

## 📂 Project Directory Structure

```text
StudentHub/
├── index.html                   # Root Static Entry Redirect
├── index.php                    # Root Dynamic PHP Application Gateway
├── admin_dashboard.php          # Admin Analytics Dashboard (Prac 14)
├── student_dashboard.php        # Authenticated Student Dashboard (Prac 10)
├── manage_students.php          # Student CRUD with MySQLi (Prac 11)
├── manage_events.php            # Event CRUD with Poster Upload (Prac 12)
├── README.md                    # Project Documentation & Viva Guide
│
├── api/                         # RESTful PHP JSON APIs (Prac 13)
│   ├── events.php               # Async Event Endpoints (GET, POST, DELETE)
│   ├── students.php             # Async Student Endpoints (GET, POST, DELETE)
│   └── stats.php                # Dashboard Metrics Aggregator (Prac 14)
│
├── css/
│   └── style.css                # Master Responsive CSS3 Design System & Dark/Light Themes
│
├── data/                        # JSON Datasets & CSV File Stores (Prac 6 & 7)
│   ├── events.json              # 16 Comprehensive College Events
│   ├── students.json            # 20 Seed Student Roster Records
│   ├── faqs.json                # 16 Categorized FAQs
│   ├── notices.json             # Live Campus Circulars
│   ├── registrations.csv        # Student Registration CSV Store
│   ├── contacts.csv             # Contact Inquiry CSV Store
│   ├── feedback.csv             # Campus Feedback CSV Store
│   └── submissions.json         # Consolidated JSON Submissions
│
├── database/                    # Database Architecture (Prac 8)
│   ├── studenthub.sql           # Complete Normalized MySQL Schema & Seed Data
│   └── er_diagram_schema.md     # Mermaid ER Diagram & Data Dictionary
│
├── docs/                        # Practical 1 Artefacts
│   ├── sitemap.png              # Information Architecture Sitemap
│   └── wireframes/              # UI Wireframes (Home, Login, Dashboard, Events)
│
├── images/                      # Media & Graphic Assets
│   ├── campus.png               # CHARUSAT Campus Panoramic Showcase Image
│   ├── StudentHubLogo.png       # Official Brand Logo
│   ├── favStudentIcon.png       # Browser Favicon
│   ├── avatar.svg               # Default User Avatar
│   ├── event-web.svg            # Web Bootcamp Poster
│   ├── event-ai.svg             # AI Summit Poster
│   ├── event-hackathon.svg      # 36-Hr Hackathon Poster
│   ├── event-cyber.svg          # Cybersecurity CTF Poster
│   └── event-robotics.svg       # Robotics Expo Poster
│
├── js/                          # Client-Side Modules (Prac 4, 5, 6, 13)
│   ├── script.js                # Core Interactivity (Clock, Greeting, Carousel, Themes, Toasts)
│   ├── validation.js            # Regex Validation, Password Meter & Canvas CAPTCHA (Prac 5)
│   ├── render-data.js           # Fetch API Renderer, Debounced Search, Pagination (Prac 6)
│   └── ajax-crud.js             # Async CRUD Operations without Page Reload (Prac 13)
│
├── pages/                       # Semantic HTML5 Static Pages (Prac 2)
│   ├── index.html               # Home Page with Campus Photo & Inspirational Thought
│   ├── about.html               # About FTE, Faculty Leadership & Mentors
│   ├── register.html            # Registration Form with Real-Time Validation
│   ├── login.html               # Secure Login with Role-Selector & Demo Fillers
│   ├── dashboard.html           # Student Dashboard Overview
│   ├── events.html              # Dynamic Events Catalog & Registration Modals
│   ├── profile.html             # Student Profile & Verified Certificates
│   ├── contact.html             # Contact Form & Campus Directory
│   ├── faq.html                 # Collapsible Accordion FAQs
│   ├── feedback.html            # Star Rating Feedback Form
│   ├── admin.html               # Admin Console (Static AJAX View)
│   └── submissions.html         # CSV / JSON File Storage Log Viewer (Prac 7)
│
├── php/                         # Backend Security & Business Logic (Prac 7-12)
│   ├── config.php               # Database Constants & CSRF Helpers
│   ├── db.php                   # Dual PDO & MySQLi Database Connector (Prac 8)
│   ├── auth_check.php           # Session Timeout & RBAC Middleware (Prac 10)
│   ├── process_register.php     # Server-Side Registration & Password Hashing (Prac 9)
│   ├── process_contact.php      # Server-Side Contact Handler (Prac 7)
│   ├── process_feedback.php     # Server-Side Feedback Handler (Prac 7)
│   ├── login_action.php         # Authentication & Remember-Me Token Handler (Prac 10)
│   ├── logout.php               # Session & Cookie Cleanup (Prac 10)
│   ├── export_students.php      # Student Roster CSV Exporter (Prac 11)
│   └── event_crud.php           # Poster Upload & Event CRUD (Prac 12)
│
└── uploads/                     # Uploads Directory Protected via .htaccess (Prac 12)
    └── events/                  # Securely Stored Uploaded Event Posters
```

---

## 🚀 Quick Setup & Installation Guide

### Option 1: Static Browser Mode (Zero Configuration)
Simply double-click or open `pages/index.html` in any modern web browser (Google Chrome, Mozilla Firefox, Microsoft Edge).  
All client-side features, DOM animations, canvas CAPTCHA, Fetch API integrations (with automatic offline fallback caching), and theme toggling work out of the box!

### Option 2: Full-Stack XAMPP Deployment (PHP 8 + MySQL)
1. **Move Project Folder:**  
   Copy the `StudentHub` folder to your XAMPP `htdocs` directory (e.g. `C:\xampp\htdocs\StudentHub` or run directly in place).
2. **Start Services:**  
   Open the **XAMPP Control Panel** and start both **Apache** and **MySQL**.
3. **Import Database:**  
   - Open your browser and navigate to: `http://localhost/phpmyadmin/`  
   - Click on the **Import** tab.  
   - Choose file: `database/studenthub.sql` and click **Go**.  
   - This automatically creates `studenthub_db` with all 6 normalized tables and 20+ seed records.
4. **Launch Portal:**  
   Visit `http://localhost/StudentHub/` or `http://localhost:8000/`.

---

## 🧪 Demo Credentials for Examination & Viva

| Role | Email ID | Password | Access Privileges |
|---|---|---|---|
| **Administrator** | `admin@charusat.ac.in` | `Admin@123` | Full CRUD on students/events, system statistics, audit logs |
| **Student** | `student@charusat.ac.in` | `Student@123` | Personal academic dashboard, event registration, profile view |

*(Note: The login page includes quick-fill buttons for one-click testing during viva demonstrations).*

---

## 🏛️ Campus Highlight & Dedicated Thought

As featured on the Home page beneath the panoramic campus visual:

> *"Education is not the learning of facts, but the training of the mind to think."*  
> — **Albert Einstein**  
>  
> *At CHARUSAT, every challenge is an opportunity to invent, every laboratory is a launchpad for discovery, and every student is an architect of tomorrow's technological landscape.*

---

## 👨‍🏫 Course Leadership & Faculty Mentors (FTE CHARUSAT)
- **Dr. Nikita Bhatt** (Head-CE, CSPIT)
- **Dr. Martin Parmar** (CSPIT-CE)
- **Dr. Mrugendra Rahevar** (CSPIT-CE)
- **Ms. Srushti Gajjar** (CSPIT-CSE)
- **Ms. Vaibhavi Patel** (CSPIT-CSE)
- **Mr. Ravi Patel** (CSPIT-IT)
- **Mr. Dhaval Patel** (CSPIT-IT)
- **Dr. Jalpesh Vasa** (CSPIT-AIML)
- **Ms. Priyanka Padhiyar** (DEPSTAR-CE)
- **Mr. Jay Patel** (DEPSTAR-CE)
- **Mr. Vaibhav Matalia** (DEPSTAR-CSE)
- **Ms. Pooja Singh Chaudhary** (DEPSTAR-IT)

---
© 2026 Charotar University of Science and Technology (CHARUSAT). All Rights Reserved.
