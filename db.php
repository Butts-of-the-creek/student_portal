<?php
// --- DATABASE CONNECTION SCRIPT ---
// This single file handles the connection to our MySQL database.
// By including this file in other scripts, we avoid repeating code and can easily manage our database credentials.

// Define database connection constants.
// Using constants is a good practice for values that won't change.
define('DB_SERVER', 'localhost'); // Your server name, usually 'localhost' for XAMPP
define('DB_USERNAME', 'root');      // Your database username, 'root' is the default for XAMPP
define('DB_PASSWORD', '');          // Your database password, empty by default for XAMPP
define('DB_NAME', 'student_portal'); // The name of the database we created

// Attempt to create a new connection to the MySQL database.
// We use the mysqli class, which is an improved version for interacting with MySQL.
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check if the connection was successful.
// The connect_error property will contain an error message if something went wrong.
if ($conn->connect_error) {
    // If the connection fails, we stop the script immediately (die) and show an error message.
    // This is crucial because the rest of our application depends on this connection.
    die("Connection failed: " . $conn->connect_error);
}

// If we reach this point, the connection was successful!
// The $conn object can now be used in other PHP files to run SQL queries.
?>
