# PHP MVC CRUD

## Database Setup

```sql
CREATE DATABASE mvc_crud;
USE 
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL
);
```

## How to Run

1. Place the project in your web server directory (e.g., `htdocs` for XAMPP).
2. Import the SQL above to your MySQL server.
3. Update the database credentials in `app/config/database.php` if needed.
4. Access `http://localhost/php-mvc-crud/public/` in your browser.
