<?php
// --- USER PROFILE SCRIPT ---
// This page is the main hub for a *logged-in* user. It displays their profile,
// allows them to update information, and upload a profile picture.

// Initialize the session
session_start();

// **SECURITY CHECK**: Check if the user is logged in.
// If our 'loggedin' session variable is not set or is not true,
// the user is not authenticated. Redirect them to the login page.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database connection file.
require_once "db.php";

// Define variables to hold user data and messages.
$name = $surname = $email = $contact_num = $module_code = $profile_picture = "";
$update_success = "";
$errors = [];

// We get the user's ID from the session variable. This is secure because the session is server-side.
$user_id = $_SESSION["id"];

// --- HANDLING OUR FORM SUBMISSIONS FOR PROFILE UPDATE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if the 'update_profile' button was clicked.
    if (isset($_POST['update_profile'])) {
        // --- VALIDATE AND SANITIZE INPUTS ---
        $name = trim($_POST['name']);
        $surname = trim($_POST['surname']);
        $contact_num = trim($_POST['contact_num']);
        $module_code = trim($_POST['module_code']);

        // Basic validation
        if (empty($name)) $errors[] = "Name is required.";
        if (empty($surname)) $errors[] = "Surname is required.";
        if (empty($module_code)) $errors[] = "Module code is required.";

        // If there are no errors, update the database.
        if (empty($errors)) {
            $sql = "UPDATE users SET name = ?, surname = ?, contact_num = ?, module_code = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssi", $name, $surname, $contact_num, $module_code, $user_id);
                if ($stmt->execute()) {
                    $update_success = "Profile updated successfully!";
                } else {
                    $errors[] = "Failed to update profile.";
                }
                $stmt->close();
            }
        }
    }

    // --- HANDLING THE PROFILE PICTURE UPLOAD ---
    // 1st we Check if the 'upload_picture' button was clicked and a file was actually uploaded.
   
        if (isset($_POST['upload_picture']) && isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] == 0) {
        
        $target_dir = "uploads/"; // The directory where the pics will be stored.
        // Always Create a unique filename to prevent overwriting files. 

        $target_file = $target_dir . $user_id . "_" . basename($_FILES["profile_pic"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // --- SECURITY CHECKS FOR FILE UPLOAD ---
        // 1. Check if it's a real image.
        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check === false) {
            $errors[] = "File is not an image.";
        }
        
        // 2. Check file size (e.g., max 2MB).
        if ($_FILES["profile_pic"]["size"] > 2000000) {
            $errors[] = "Sorry, your file is too large.";
        }

        // 3. Allow only specific file formats.
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        // If all checks pass, move the file and update the database.
        if (empty($errors)) {
            // move_uploaded_file() moves the temporary file to our permanent 'uploads' directory.
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                // Now, update the 'profile_picture' field in the database with the new file path.
                $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("si", $target_file, $user_id);
                    if ($stmt->execute()) {
                        $update_success = "Profile picture updated successfully!";
                    } else {
                        $errors[] = "Failed to update profile picture path in database.";
                    }
                    $stmt->close();
                }
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        }
    }
}

// --- FETCH CURRENT USER DATA TO DISPLAY ON THE PAGE ---
// This runs every time the page loads to ensure the displayed data is up-to-date.
$sql = "SELECT name, surname, email, contact_num, module_code, profile_picture FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        $stmt->bind_result($name, $surname, $email, $contact_num, $module_code, $profile_picture);
        $stmt->fetch();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <img src="<?php echo !empty($profile_picture) && file_exists($profile_picture) ? htmlspecialchars($profile_picture) : 'uploads/default.png'; ?>" alt="Profile Picture" class="profile-picture">
            <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
        </div>

        <?php
        // Display any success or error messages.
        if (!empty($update_success)) echo '<div class="message success">' . $update_success . '</div>';
        if (!empty($errors)) {
            echo '<div class="message error">';
            foreach ($errors as $error) echo '<p>' . htmlspecialchars($error) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Surname:</strong> <?php echo htmlspecialchars($surname); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($contact_num); ?></p>
            <p><strong>Module:</strong> <?php echo htmlspecialchars($module_code); ?></p>
        </div>

        <hr style="margin: 2rem 0;">

        <h3>Edit Your Profile</h3>
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
                <label>Contact</label>
                <input type="text" name="contact_num" value="<?php echo htmlspecialchars($contact_num); ?>">
            </div>
             <div class="form-group">
                <label>Module Code</label>
                <input type="text" name="module_code" value="<?php echo htmlspecialchars($module_code); ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" name="update_profile" class="btn" value="Update Profile">
            </div>
        </form>

        <hr style="margin: 2rem 0;">

        <h3>Update Profile Picture</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Select image to upload:</label>
                <input type="file" name="profile_pic" required>
            </div>
            <div class="form-group">
                <input type="submit" name="upload_picture" class="btn" value="Upload Picture">
            </div>
        </form>

        <p class="link"><a href="logout.php">Logout of Your Account</a></p>
    </div>
</body>
</html>
