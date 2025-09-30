# FlashCard Login System

This is a simple PHP-based user authentication system with registration, login, and logout functionality.

## Setup Instructions  teste

1. **Create the Database**
   - In your MySQL server (e.g., via phpMyAdmin or MySQL CLI), create a new database named `flashcard`:
     ```sql
     CREATE DATABASE flashcard CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
     ```

2. **Import Database Schema**
   - Import the `database/schema.sql` file into the `flashcard` database. This will create the `users` table.

3. **Configure Database Connection**
   - Open `config.php` and update the database credentials if necessary:
     ```php
     $host = 'localhost';
     $db   = 'flashcard';
     $user = 'root';
     $pass = '';
     ```

4. **Deploy Files**
   - Place the project folder (`FlashCard`) inside your web server's root (e.g., `C:\xampp\htdocs\FlashCard`).

5. **Access the Application**
   - Open your browser and navigate to `http://localhost/FlashCard/index.php`.

## Usage

- **Register a New User**: Click the "Register" link and fill in your details.
- **Login**: Use your username or email and password to log in.
- **Logout**: Click the "Logout" link to end your session.

## Flashcards Feature

- **Database Schema**: Import `database/flashcards_schema.sql` to create the `flashcards` table.
- **Pages**:
  - `flashcards.php`: List and flip flashcards.
  - `flashcard_add.php`: Create a new flashcard.
  - `flashcard_edit.php`: Edit existing flashcard.
  - `flashcard_delete.php`: Delete a flashcard.
- **Assets**:
  - `classes/Flashcard.php`: OOP model with CRUD methods.
  - `css/flashcards.css`, `js/flashcards.js`: Styles and scripts for interface.

## File Structure

```
FlashCard/
├── database/
│   └── schema.sql      # SQL script to create users table
├── config.php          # Database connection setup
├── index.php           # Home page showing login status
├── login.php           # User login page
├── logout.php          # User logout script
## Subjects and Topics Feature

- **Database Schema**: Import `database/flashcards_schema.sql` to create the `subjects`, `topics`, and `flashcards` tables.
- **Pages**:
   - `flashcards.php`: List and flip flashcards.
   - `flashcard_add.php`: Create a new flashcard.
   - `flashcard_edit.php`: Edit existing flashcard.
   - `flashcard_delete.php`: Delete a flashcard.
- **Assets**:
   - `classes/Subject.php`: OOP model for matérias (subjects).
   - `classes/Topic.php`: OOP model for assuntos (topics).
   - `classes/Flashcard.php`: OOP model with CRUD methods for flashcards.
   - `css/flashcards.css`, `js/flashcards.js`: Styles and scripts for flashcards interface.
└── README.md           # Project documentation
```

## License

This project is provided under the MIT License.
