<?php
// Include database connection
include 'connect.php';
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$reset_error = "";
$success_message = "";

// Check if the reset token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if the token exists in the database
    $sql = "SELECT * FROM `User` WHERE reset_token='$token'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Token found, user can reset their password
        if (isset($_POST['reset_password'])) {
            // Get the new password from the form
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Check if passwords match
            if ($new_password === $confirm_password) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update the password in the database
                $sql = "UPDATE `User` SET password='$hashed_password', reset_token=NULL WHERE reset_token='$token'";
                if ($conn->query($sql) === TRUE) {
                    $success_message = "Your password has been reset successfully. You can now log in.";
                } else {
                    $reset_error = "There was an error updating your password.";
                }
            } else {
                $reset_error = "Passwords do not match!";
            }
        }
    } else {
        $reset_error = "Invalid or expired token.";
    }
} else {
    $reset_error = "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="login_style.css">
</head>

<body>

<section>
    <div class="login-box">
        <form action="reset-password.php?token=<?= $_GET['token'] ?>" method="POST">
            <h2>Reset Password</h2>

            <!-- New Password Input -->
            <div class="input-box">
                <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                <input type="password" name="new_password" required>
                <label>New Password</label>
            </div>

            <!-- Confirm Password Input -->
            <div class="input-box">
                <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                <input type="password" name="confirm_password" required>
                <label>Confirm Password</label>
            </div>

            <!-- Error or Success Messages -->
            <?php if (!empty($reset_error)): ?>
                <p style="color: red;"><?= $reset_error ?></p>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <p style="color: green;"><?= $success_message ?></p>
            <?php endif; ?>

            <!-- Reset Button -->
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
    </div>
</section>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
