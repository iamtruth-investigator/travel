# 🌍 GLOBEXA – AI Powered Travel Planning & Management System

> A responsive travel booking and management system developed using **HTML, CSS, JavaScript, PHP, and MySQL**

---

# 📌 Project Description

**GLOBEXA** is a complete travel platform where users can discover destinations, book hotels, reserve taxis, manage trips, and make secure payments — all in one place.

The system is designed with a modern UI/UX and responsive layout for desktop, tablet, and mobile devices.

---

# 🚀 Core Features

## 👤 User Panel

- User Registration & Login
- Search Destinations
- Explore Travel Packages
- Hotel Booking
- Taxi Booking
- Secure Payment
- Booking History
- Profile Management
- Contact Support

---

## 🛠️ Admin Panel

- Admin Login
- Dashboard Overview
- Manage Users
- Manage Destinations
- Manage Hotels
- Manage Taxi Services
- Manage Bookings
- Payment Reports

---

# 🎨 UI / UX Highlights

- Attractive Hero Section
- Responsive Navbar
- Destination Cards
- Booking Forms
- Dashboard Layout
- Smooth Hover Effects
- Mobile Friendly Design
- Premium Color Theme
- Clean Footer Section

---

# 🧰 Technology Used

| Technology | Purpose |
|-----------|---------|
| HTML5 | Page Structure |
| CSS3 | Styling |
| JavaScript | Dynamic Interaction |
| PHP | Backend Development |
| MySQL | Database |
| Bootstrap | Responsive Design |

---

# 📂 Project File Structure

```bash id="t8jv31"
GLOBEXA/
│── index.php
│── login.php
│── register.php
│── dashboard.php
│── destinations.php
│── hotels.php
│── taxi.php
│── bookings.php
│── payment.php
│── profile.php
│── contact.php
│
├── admin/
│   │── login.php
│   │── dashboard.php
│   │── users.php
│   │── destinations.php
│   │── hotels.php
│   │── bookings.php
│   │── payments.php
│
├── includes/
│   │── db.php
│   │── header.php
│   │── footer.php
│   │── auth.php
│
├── assets/
│   ├── css/
│   │   │── style.css
│   │   │── responsive.css
│   │
│   ├── js/
│   │   │── script.js
│   │
│   ├── images/
│
├── database/
│   │── globexa.sql
│
└── README.md


⸻

🗄️ Database Setup

Create Database

CREATE DATABASE globexa;
USE globexa;


⸻

Users Table

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


⸻

Destinations Table

CREATE TABLE destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    country VARCHAR(100),
    image VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2)
);


⸻

Hotels Table

CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    location VARCHAR(100),
    image VARCHAR(255),
    price DECIMAL(10,2),
    rating FLOAT
);


⸻

Taxi Table

CREATE TABLE taxis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_name VARCHAR(100),
    car_name VARCHAR(100),
    location VARCHAR(100),
    price DECIMAL(10,2)
);


⸻

Bookings Table

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50),
    item_id INT,
    total_price DECIMAL(10,2),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


⸻

Payments Table

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


⸻

⚙️ Installation Guide (VS Code + XAMPP)

Step 1: Move Project Folder

Copy the GLOBEXA folder into:

C:/xampp/htdocs/


⸻

Step 2: Start Server

Open XAMPP Control Panel and start:
	•	Apache
	•	MySQL

⸻

Step 3: Import Database

Open browser:

http://localhost/phpmyadmin

	•	Create database: globexa
	•	Import: database/globexa.sql

⸻

Step 4: Run Project

http://localhost/GLOBEXA


⸻

📱 Main Pages
	•	Home Page
	•	Login Page
	•	Register Page
	•	Destinations Page
	•	Hotels Page
	•	Taxi Booking Page
	•	Payment Page
	•	Dashboard
	•	Profile Page
	•	Contact Page
	•	Admin Panel

⸻

🤖 Future AI Integration
	•	AI Travel Assistant
	•	Smart Package Recommendation
	•	Personalized Destination Search
	•	Travel Budget Planner
	•	Chatbot Support

⸻

🎯 Prompt for Codex

Create a fully responsive travel website named GLOBEXA using HTML, CSS, JavaScript, PHP, and MySQL.
Follow the provided file structure.
Use premium UI/UX, responsive navbar, hero section, destination cards, booking forms,
dashboard pages, admin panel, mobile responsive layout, animations, modern footer,
clean code, reusable header/footer includes, and professional design.


⸻

👨‍💻 Developed By

Alakesh Gogoi
Software Developer | MCA Student | Assam

⸻


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


