---
description: Build the Hostel/PG Finder Application
---

# Phase 1: Setup and Database
1. Create directories: `config`, `auth`, `admin`, `owner`, `user`, `uploads`, `assets/css`, `assets/js`.
2. Create `config/database.php` for PDO connection.
3. Create `database.sql` with tables: users, pg_owners, pg_listings, rooms, bookings, payments, notifications.
4. Move existing `css/home.css` to `assets/css/style.css` and update `index.php`.

# Phase 2: Authentication
1. Create `auth/register.php` (User & Owner).
2. Create `auth/login.php` (All roles).
3. Create `auth/logout.php`.

# Phase 3: Functionality
1. Create `owner/add_listing.php` (PG details).
2. Create `owner/add_room.php` (Room details + Image Uploads).
3. Create `user/search.php` (Search PGs).
4. Create `user/book.php` (Booking + Fake Payment).
5. Create `admin/dashboard.php` (Stats & approvals).

# Phase 4: UI Refinement
1. Style `index.php` with modern CSS.
2. Ensure mobile responsiveness.
