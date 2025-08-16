# OrderTrack

 <!-- Screenshot -->

**OrderTrack** is a lightweight, self-hosted order tracking and management system built with vanilla PHP and MySQL. It's designed for small businesses, e-commerce stores, and developers who need a straightforward, easy-to-deploy solution without the overhead of large frameworks.

Manage your products, create orders for customers, update shipment statuses, and provide a clean, public-facing page for customers to track their order's progress from processing to delivery.

## ✨ Features

*   **Public Tracking Page:** Customers can track their order status using their Order ID or Phone Number without needing to log in.
*   **Real-time Timeline:** Displays a clear, chronological history of order status updates (e.g., Pending, Processing, Shipped, Delivered).
*   **Secure Admin Panel:** A password-protected dashboard to manage the entire system.
*   **Order Management:** View, search, and filter all orders. Update order statuses with optional notes.
*   **Create Orders Manually:** Easily create new orders for sales made over the phone, social media, or in-person.
*   **Product Management:** A simple interface to add, edit, and delete products and manage stock levels.
*   **Print Invoices & Labels:** Generate printer-friendly invoices and shipping labels directly from the order details page.
*   **Zero Dependencies:** Built with pure, vanilla PHP and MySQL. No need to manage complex dependencies with Composer.
*   **Easy to Deploy:** Simply upload to any standard PHP/MySQL web server (like LAMP/LEMP), configure your database, and you're ready to go.

## 🚀 Getting Started

Follow these instructions to get OrderTrack PHP up and running on your server.

### Prerequisites

*   A web server with PHP 7.4 or higher.
*   A MySQL or MariaDB database server.
*   A web browser.

### Installation

1.  **Clone or Download:**
    Clone the repository to your local machine or web server:
    ```bash
    git clone https://github.com/your-username/ordertrack-php.git
    ```
    Alternatively, download the ZIP file and extract it to your desired directory (e.g., `/var/www/html/`).

2.  **Create the Database:**
    - Using a tool like phpMyAdmin or the MySQL command line, create a new database. For example, `order_system`.
    - Import the `database.sql` file into your newly created database. This will set up all the necessary tables and create a default admin user.

3.  **Configure the Application:**
    - Rename `config.example.php` to `config.php`. *(Note: If my previous answer gave you `config.php` directly, you can skip this rename step)*.
    - Open `config.php` in a text editor and update the following constants with your information:
      ```php
      // --- DATABASE CONFIGURATION ---
      define('DB_HOST', 'localhost');
      define('DB_USER', 'your_db_user');
      define('DB_PASS', 'your_db_password');
      define('DB_NAME', 'your_db_name');

      // --- SITE CONFIGURATION ---
      define('SITE_URL', 'http://your-website.com'); // IMPORTANT: No trailing slash
      ```

4.  **Set Up `.htaccess` (for Apache users):**
    The included `.htaccess` file enables clean URLs. Ensure that `mod_rewrite` is enabled on your Apache server. If you are using Nginx, you will need to configure the URL rewrites in your server block.

5.  **You're All Set!**
    - **Public Tracking Page:** Navigate to `http://your-website.com`
    - **Admin Panel:** Navigate to `http://your-website.com/admin`

    **Default Admin Login:**
    - **Email:** `admin@example.com`
    - **Password:** `password123`

    > **Security Warning:** It is highly recommended that you change the default admin email and password immediately after your first login.

## 🏛️ System Structure

*   `/` - Root directory containing the main router (`index.php`) and configuration files.
*   `/public/` - Contains the customer-facing tracking page.
*   `/admin/` - Contains all files for the secure admin panel.
*   `/includes/` - Core files for database connection and helper functions.
*   `/assets/` - CSS stylesheets and any future JS or image files.
*   `database.sql` - The database schema for a fresh installation.

## 🤝 Contributing

Contributions are welcome! If you'd like to improve OrderTrack PHP, please feel free to fork the repository and submit a pull request.

Some areas for potential improvement:
*   Adding user management for different staff roles.
*   Implementing email notifications for status updates.
*   Adding basic sales reporting and charts.
*   Improving the UI/UX.
*   Integrating with third-party shipping APIs.

## 📄 License

This project is open-sourced under the [GNU GPL v3.0](LICENSE.md). See the `LICENSE.md` file for details.

---
Made with ❤️ for small businesses.
