# ShareSpace 

> **Rent Anything from Your Community** — a peer-to-peer rental marketplace built for township and urban communities in South Africa.

[![Live Site](https://img.shields.io/badge/Live%20Site-sharespace.infinityfreeapp.com-D4530A?style=flat-square)](https://sharespace.infinityfreeapp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)](https://mysql.com)

---

## What is ShareSpace?

ShareSpace connects neighbours who have equipment, furniture, tools, and gear with people who need them — at fair daily prices, with no middlemen.

Community members can list items they own, browse listings by category or location, book rentals, message owners, leave reviews, and manage everything from a personal dashboard.

---

## Features

| Area | What's included |
|---|---|
| **Auth** | Register, login, bcrypt passwords, role-based access (admin / user) |
| **Listings** | Create, edit, delete with photo upload; category & availability filters |
| **Rentals** | Book by date range, status tracking (pending → active → completed) |
| **Messaging** | In-app inbox between renters and owners |
| **Reviews** | Star ratings + comments after completed rentals |
| **Wishlist** | Save / unsave listings with heart toggle |
| **Dashboard** | Personal rental history, listings, and messages |
| **Admin Panel** | Manage users, listings, and rentals; send removal notifications |

---

## Tech Stack

- **Frontend** — HTML5, CSS3 (custom design system), vanilla JS, Google Fonts (Syne + DM Sans)
- **Backend** — PHP 8.3, PDO with prepared statements, file upload handling
- **Database** — MySQL 8 with full relational schema (8 tables, FK constraints)
- **Security** — bcrypt hashing, `htmlspecialchars()` escaping, session auth, SQL-injection-safe queries
- **Dev** — MAMP (local), InfinityFree (production), FTP deployment

---

## Database Schema

```
users ─┬─< listings ─┬─< rentals ─┬─< reviews
       │             │             └─< payments
       │             └─< wishlists
       └─< messages
categories ──< listings
```

---

## Local Setup

### 1. Clone the repo
```bash
git clone https://github.com/hpotgieter0310/sharespace.git
cd sharespace
```

### 2. Set up the database (MAMP / any MySQL server)
- Create a database called `sharespace_db`
- Import `sharespace_import.sql` via phpMyAdmin or:
  ```bash
  mysql -u root -p sharespace_db < sharespace_import.sql
  ```

### 3. Configure database credentials
```bash
cp includes/db.example.php includes/db.php
# Edit includes/db.php with your local DB details
```

### 4. Create the uploads folder
```bash
mkdir -p uploads/listings
chmod 755 uploads/listings
```

### 5. Run locally
Point your web server (MAMP / XAMPP / Laravel Herd) at the project folder and open:
```
http://localhost:8888/sharespace/
```

**Default admin login:**
- Email: `hpotgieter0310@gmail.com`
- Password: *(set during registration)*

**Demo users** (password: `password`):
- `sipho@demo.com`, `nomsa@demo.com`, `thabo@demo.com`, `zanele@demo.com`, `bongani@demo.com`

---

## Project Structure

```
sharespace/
├── includes/
│   ├── db.example.php     # DB config template (copy → db.php)
│   └── db.php             # ← gitignored, contains real credentials
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── users.php          # User management
│   ├── listings.php       # Listing management
│   └── rentals.php        # Rental management
├── uploads/listings/      # User-uploaded photos (gitignored)
├── sharespace_import.sql  # Full DB schema + seed data
├── index.php              # Homepage / listing browse
├── listing.php            # Single listing detail
├── list_item.php          # Create new listing
├── edit_listing.php       # Edit existing listing
├── dashboard.php          # User dashboard
├── messages.php           # Inbox
├── login.php / register.php / logout.php
└── README.md
```

---

## Live Site

🌐 **[sharespace.infinityfreeapp.com](https://sharespace.infinityfreeapp.com)**

---

## Author

**Heinrich Potgieter** — Web Development Final Year Project  
📧 hpotgieter0310@gmail.com
