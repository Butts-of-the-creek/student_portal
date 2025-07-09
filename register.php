<?php
// REGISTRATION SCRIPT
// This script handles new user registration. It includes form validation,
// database interaction, and security measures like password hashing.

// I Start a session to store user data across pages (like user info or error messages).
session_start();

// we 'Include' our database connection file :)
include('db.php');

// Initialize variables to hold user input and error messages.
$name = $surname = $student_num = $contact_num = $module_code = $email = "";
$errors = [];

// Checking if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // FORM VALIDATION 
    // We're checking each field to make sure it's not empty and meets our requirements.
    // trim() removes whitespace from the beginning and end of a string just incase.

    // Validate Name
    if (empty(trim($_POST["name"]))) {
        $errors[] = "Name is required!!";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validating The Surname
    if (empty(trim($_POST["surname"]))) {
        $errors[] = "Surname is required!!";
    } else {
        $surname = trim($_POST["surname"]);
    }
    
    // Validating The Student Number
    if (empty(trim($_POST["student_num"]))) {
        $errors[] = "Student Number is required!!";
    } else {
        $student_num = trim($_POST["student_num"]);
    }

    // Contact Number (optional, so... no validation if empty)
    $contact_num = trim($_POST["contact_num"]);

    // Validating The Module Code
    if (empty(trim($_POST["module_code"]))) {
        $errors[] = "Module Code is required!!";
    } else {
        $module_code = trim($_POST["module_code"]);
    }

    // Validate Email
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Email is required!!";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        // filter_var is a great PHP function for validating emails.
        $errors[] = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate Password
    if (empty(trim($_POST["password"]))) {
        $errors[] = "Password is required!!";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        // A simple security check: ensure the password has a minimum length.
        $errors[] = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate Confirm Password
    if (empty(trim($_POST["confirm_password"]))) {
        $errors[] = "Please confirm your password.";
    } elseif ($password !== trim($_POST["confirm_password"])) {
        // Make sure the two password fields match.
        $errors[] = "Passwords do not match.";
    }

    // DATABASE CHECKS
    if (empty($errors)) {
        // We need to check if the email or student number already exists in the database.

        // SQL query to check for existing email or student numbers.
        // The '?' are placeholders for our variables. This is the first step of using prepared statements.
        $sql = "SELECT id FROM users WHERE email = ? OR student_num = ?";

        // We Prepare the statement. This protects against SQL Injection.
        // The database compiles the query template separately from the data.
        if ($stmt = $conn->prepare($sql)) {

            // Bind the variables to the placeholders as strings ("ss").
            $stmt->bind_param("ss", $param_email, $param_student_num);

            // Set the parameter values.
            $param_email = $email;
            $param_student_num = $student_num;

            // Execute the prepared statement.
            if ($stmt->execute()) {
                // Store the result so we can check if any rows were returned.
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $errors[] = "An account with this email or student number already exists.";
                }
            } else {
                $errors[] = "Oops! Something went wrong. Please try again later.";
            }
            // We then close the statement to free up resources.
            $stmt->close();
        }
    }

    // INSERT DATA INTO DATABASE (if no errors at all) 
    if (empty($errors)) {

        // SQL query to insert a new user into the 'users' table.
        $sql = "INSERT INTO users (name, surname, student_num, contact_num, module_code, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters.
            // "sssssss" means all 7 parameters are strings.
            $stmt->bind_param("sssssss", $param_name, $param_surname, $param_student_num, $param_contact, $param_module, $param_email, $param_password);
            
            // Set parameters
            $param_name = $name;
            $param_surname = $surname;
            $param_student_num = $student_num;
            $param_contact = $contact_num;
            $param_module = $module_code;
            $param_email = $email;

            // **CRITICAL SECURITY STEP**: Hash the password.
            // password_hash() creates a secure, one-way hash of the password.
            // We store this hash, never the plain text password.
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // If registration is successful, redirect to the login page.
                header("location: login.php");
                exit(); // Always call exit() after a header redirect.
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    // Close the database connection.
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Create Your Account</h2>
        <p>Please fill this form to create an account.</p>

        <?php
        // If there are any errors from our PHP validation, display them here.
        if (!empty($errors)) {
            echo '<div class="message error">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label>Surname</label>
                <input type="text" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>
            </div>
            <div class="form-group">
                <label>Student No</label>
                <input type="text" name="student_num" value="<?php echo htmlspecialchars($student_num); ?>" required>
            </div>
             <div class="form-group">
                <label>Contact</label>
                <input type="text" name="contact_num" value="<?php echo htmlspecialchars($contact_num); ?>">
            </div>
             <div class="form-group">
                <label>Module Code</label>
                <input type="text" name="module_code" value="<?php echo htmlspecialchars($module_code); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Register">
            </div>
            <p class="link">Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>
