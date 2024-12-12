<?php
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';
require '/home/u220407022/domains/feralstorm.com/public_html/chatService/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$reset_error = "";

if (isset($_POST['reset_password'])) {
    $email = $conn->real_escape_string($_POST['reset_email']);
    
    $sql = "SELECT * FROM `User` WHERE email='$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $sql = "UPDATE `User` SET reset_token='$token' WHERE email='$email'";
        $conn->query($sql);

        $reset_link = "https://chat.feralstorm.com/reset-password.php?token=$token";
        
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'password@feralstorm.com';
        $mail->Password = 'PasswordReset3#';
        $mail->setFrom('password@feralstorm.com', 'Password Reset');
        $mail->addReplyTo('password@feralstorm.com', 'Password Reset');
        $mail->addAddress($email, $email);
        $mail->Subject = 'Password Reset Request @ Your Business Name';

        $html_body = file_get_contents('/home/u220407022/domains/feralstorm.com/public_html/chatService/templates/email_templates/resetpassword.html');
        if ($html_body === false) {
            echo 'Could not open email template.';
            exit;
        }

        $html_body = str_replace('{{reset_link}}', $reset_link, $html_body);
        $mail->msgHTML($html_body);

        if (!$mail->send()) {
            $reset_error = 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'A reset link has been sent to your email.';
            header('Location: login.php');
        }
    } else {
        $reset_error = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="login_style.css">
</head>

<body>

<section>
    <div class="login-box">
        <form action="forgot-password.php" method="POST">
            <h2>Forgot Password</h2>

            <div class="input-box">
                <span class="icon"><ion-icon name="mail"></ion-icon></span>
                <input type="email" name="reset_email" required>
                <label>Email</label>
            </div>

            <?php if (!empty($reset_error)): ?>
                <p style="color: red;"><?= $reset_error ?></p>
            <?php endif; ?>

            <button type="submit" name="reset_password">Reset Password</button>

            <div class="register-link">
                <p>Remembered your password? <a href="login.php">Login</a></p>
            </div>
        </form>
    </div>
</section>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
