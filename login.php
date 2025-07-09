<?php
// --- LOGIN SCRIPT ---
// This script handles user authentication. It verifies credentials against the database
// and uses session management to keep the user logged in.

// Always start the session at the beginning of any script that needs session access.
session_start();

// If the user is already logged in, redirect them to the profile page.
// This prevents a logged-in user from seeing the login page again.
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: profile.php");
    exit;
}

// Include our database connection file.
require_once 'db.php';

// Initialize variables for email, password, and error messages.
$email = $password = "";
$login_err = "";

// Check if the form was submitted via POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- VALIDATION ---
    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $login_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $login_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // --- DATABASE VERIFICATION ---
    // If there are no validation errors, proceed to check the database.
    if (empty($login_err)) {
        // Prepare a select statement to get user info based on their email.
        $sql = "SELECT id, email, password FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind the email variable to the prepared statement.
            $stmt->bind_param("s", $param_email);
            
            // Set the parameter value.
            $param_email = $email;
            
            // Execute the statement.
            if ($stmt->execute()) {
                // Store the result.
                $stmt->store_result();
                
                // Check if a user with that email exists.
                if ($stmt->num_rows == 1) {
                    // If the user exists, bind the result variables.
                    $stmt->bind_result($id, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        // **CRITICAL SECURITY STEP**: Verify the password.
                        // password_verify() securely compares the submitted password
                        // with the stored hash. It's designed to be safe against timing attacks.
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session.
                            session_start();
                            
                            // Store data in session variables.
                            // These variables will be available on other pages as long as the session is active.
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id; // Store user ID for fetching their data later
                            $_SESSION["email"] = $email;
                            
                            // Redirect user to their profile page.
                            header("location: profile.php");
                            exit();
                        } else {
                            // Password is not valid.
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // No account found with that email.
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement.
            $stmt->close();
        }
    }
    
    // Close connection.
    $conn->close();
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Student Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php 
        // Display the login error if it's not empty.
        if(!empty($login_err)){
            echo '<div class="message error">' . htmlspecialchars($login_err) . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Login">
            </div>
            <p class="link">Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>
</body>
</html>
