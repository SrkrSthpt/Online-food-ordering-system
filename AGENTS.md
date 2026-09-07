# Bitezy — Online Food Ordering System

A complete food ordering web application built with **HTML, CSS, JavaScript, PHP, and MySQL**. Designed to run on **XAMPP** (Apache + MySQL).

## Features

- **User Authentication** — Sign up, login, logout with session management
- **Restaurant Browsing** — Browse restaurants and view their menus
- **Menu Items** — View food items with prices and ratings
- **Shopping Cart** — Add items, update quantities, remove items (session-based)
- **Checkout & Payment** — Place orders with multiple payment methods (PayPal, Stripe, Cash on Delivery)
- **Order Tracking** — Track order status (Pending → Preparing → Delivered)
- **Admin Dashboard** — Manage restaurants, menu items, and orders with analytics charts
- **Responsive Design** — Mobile-friendly UI with smooth animations

## Tech Stack

| Layer     | Technology              |
|-----------|-------------------------|
| Frontend  | HTML5, CSS3, JavaScript |
| Backend   | PHP 8.x                 |
| Database  | MySQL (XAMPP)           |
| Server    | Apache (XAMPP)          |
| Fonts     | Google Fonts (Poppins)  |
| Icons     | Font Awesome 6.4        |

## Project Structure

```
bitezy/
├── admin/              # Admin panel pages
│   ├── index.php       # Dashboard with stats & charts
│   ├── restaurants.php # Manage restaurants
│   ├── menu-items.php  # Manage menu items
│   └── orders.php      # Manage orders
├── api/                # AJAX API endpoints
│   ├── add-to-cart.php
│   ├── update-cart.php
│   ├── remove-from-cart.php
│   ├── get-cart-count.php
│   ├── place-order.php
│   ├── process-payment.php
│   └── delete-item.php
├── assets/
│   ├── css/style.css   # All styles
│   └── js/script.js    # All JavaScript
├── database/
│   └── schema.sql      # Database schema + seed data
├── includes/           # PHP includes
│   ├── db.php          # Database connection & helpers
│   ├── header.php      # Common header with navbar
│   └── footer.php      # Common footer
├── pages/              # Public pages
│   ├── menu.php        # Restaurant & menu listing
│   ├── cart.php        # Shopping cart
│   ├── login.php       # User login
│   ├── signup.php      # User registration
│   ├── payment.php     # Payment page
│   ├── payment-success.php
│   ├── order-tracking.php
│   └── logout.php
├── config.php          # App configuration & .env loading
├── index.php           # Homepage
├── .env.example        # Environment template
└── .htaccess           # Apache URL rewriting
```

## Setup Instructions (XAMPP)

1. **Install XAMPP** — Download from [apachefriends.org](https://www.apachefriends.org)

2. **Clone the project** into your XAMPP htdocs directory:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/SrkrSthpt/Online-food-ordering-system.git bitezy
   ```

3. **Start Apache & MySQL** from XAMPP Control Panel

4. **Create the database:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import `database/schema.sql` (or run the SQL directly)

5. **Configure environment:**
   - Copy `.env.example` to `.env`
   - Update database credentials if needed (default: root with no password)

6. **Access the application:**
   - Homepage: `http://localhost/bitezy`
   - Admin Panel: `http://localhost/bitezy/admin/`

## Default Logins

| Role    | Email                | Password |
|---------|----------------------|----------|
| Admin   | admin@bitezy.com     | password |
| Manager | manager@bitezy.com   | password |

## License

MIT
