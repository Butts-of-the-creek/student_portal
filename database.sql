-- This script sets up the entire database structure.
-- We are running this in PHPMyAdmin.
-- It's the foundation of my entire application!

-- I Create a database named 'student_portal'.
CREATE DATABASE student_portal;

-- Now Use the database.
USE student_portal;

-- Create the 'users' table. This table will hold all our student information..
CREATE TABLE users (
    -- 'id' is our primary key. It's an integer that auto-increments,
    -- ensuring every user has a unique, non-null ID. It's the most reliable way to identify a user.
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- 'student_num' stores the student's number..
    -- It is marked as 'UNIQUE' because no two students should have the same number.
    student_num VARCHAR(50) UNIQUE NOT NULL,

    -- 'name' and 'surname' fields for the user's personal details.
    -- NOT NULL means these fields must be filled out during registration.
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,

    -- 'contact_num' for the phone number.
    contact_num VARCHAR(20) UNIQUE,

    -- 'module_code' to store the module the student is registered for.
    module_code VARCHAR(20) NOT NULL,

    -- 'email' is crucial for login and communication.
    -- It must be UNIQUE and NOT NULL. We use VARCHAR(255) as it's a standard safe length for emails.
    email VARCHAR(255) UNIQUE NOT NULL,

    -- 'password' will store the *hashed* password, not the actual one.
    -- Storing plain text passwords is a major security risk. Hashing is a one-way process.
    -- VARCHAR(255) is used because hashed passwords are pretty long strings.
    password VARCHAR(255) NOT NULL,

    -- 'profile_picture' will store the path to the user's uploaded image.
    -- We don't store the image itself in the database, just a reference to where it's saved on the server.
    -- It has a DEFAULT value for users who haven't uploaded a picture yet.
    profile_picture VARCHAR(255) DEFAULT 'default.png',

    -- 'created_at' automatically timestamps when a user registers.
    -- This is great for auditing and tracking.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
