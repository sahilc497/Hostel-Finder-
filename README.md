# StudentNest - Hostel & PG Finder

StudentNest is a high-impact, industrial-themed web application designed for students of **DYPCET** to find and book hostels (PGs) near campus. The application features a unique **Brutalism UI** design, providing a sharp and powerful user experience.

![Home Page Screenshot](assets/images/screenshot_home.png) *(Note: Add actual screenshot if available)*

## 🚀 Features

### For Students
- **Mission Briefing (Search):** Advanced filtering by city, gender type, and budget.
- **Visual Intel:** Detailed property views with high-quality images and amenity lists.
- **Sector Security:** Visual bed booking system to select your exact spot.
- **Command Status:** Personal dashboard to track bookings, roommates, and rent due dates.

### For Property Owners
- **Asset Deployment:** Easy listing of properties with image uploads and map integration.
- **Tenant Manifest:** Track all active tenants, bed assignments, and payment statuses.
- **Operational Control:** Approve or reject bookings and manage property details.

### For Admin (Central Command)
- **Analytics Feed:** Visual charts (Chart.js) showing user growth and platform revenue.
- **Demographics:** Overview of students vs. owners on the platform.
- **Authorization:** Centralized control over property approvals and platform users.

## 🛠️ Tech Stack

- **Backend:** PHP (Pure)
- **Database:** PostgreSQL (Core migration complete)
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Frameworks:** Bootstrap 5 (for structure), Chart.js (for analytics), Leaflet.js (for maps)
- **Design System:** Custom **Brutalism UI** (Yellow, Black, White palette)

## 📦 Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/sahilc497/Hostel-Finder.git
   ```

2. **Database Setup:**
   - Ensure you have **PostgreSQL** installed and running.
   - Create a database named `hostel_finder`.
   - Run the schema script located at `/postgres_schema.sql` to initialize tables.

3. **Configure Environment:**
   - Copy `config/database.php.example` to `config/database.php`.
   - Update `config/database.php` with your PostgreSQL credentials.

4. **Web Server:**
   - Move the project to your web server root (e.g., `C:/xampp/htdocs/hf`).
   - Access via `http://localhost/hf`.

## 🎨 Design Philosophy

StudentNest uses a **Brutalist UI** aesthetic:
- **Bold Typography:** Uses 'Anton' for headings and 'Space Mono' for data.
- **High Contrast:** Strong yellow and black color scheme.
- **Industrial Layout:** Thick borders, hard shadows, and a raw, functional feel.

---
**Made by Sahil**
