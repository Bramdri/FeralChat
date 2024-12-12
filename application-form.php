<?php
require 'connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
    $businessName = filter_input(INPUT_POST, 'businessName', FILTER_SANITIZE_STRING);
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $websiteLink = filter_input(INPUT_POST, 'websiteLink', FILTER_VALIDATE_URL);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $selectedPlan = filter_input(INPUT_POST, 'plan', FILTER_SANITIZE_STRING) ?? 'STANDARD';
    echo $selectedPlan;
    $googleProfile = filter_input(INPUT_POST, 'googleProfile', FILTER_VALIDATE_URL) ?: null;

    if ($name && $firstName && $businessName && $reason && $websiteLink && $email) {
        $stmt = $conn->prepare("INSERT INTO BusinessRegistrations (name, firstName, businessName, reason, websiteLink, googleProfile, email, plan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $firstName, $businessName, $reason, $websiteLink, $googleProfile, $email, $selectedPlan);

        if ($stmt->execute()) {
            $emailTemplate = file_get_contents("application-mail.html");
            $emailTemplate = str_replace("{{name}}", $name, $emailTemplate);
            $emailTemplate = str_replace("{{firstName}}", $firstName, $emailTemplate);
            $emailTemplate = str_replace("{{businessName}}", $businessName, $emailTemplate);
            $emailTemplate = str_replace("{{reason}}", $reason, $emailTemplate);
            $emailTemplate = str_replace("{{websiteLink}}", $websiteLink, $emailTemplate);
            $emailTemplate = str_replace("{{plan}}", $selectedPlan, $emailTemplate);

            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0;
                
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'chat@feralstorm.com';
                $mail->Password = 'Nouredine3#';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('chat@feralstorm.com', 'Feralstorm Entertainment');
                $mail->addAddress($email, "$firstName $name");

                $mail->isHTML(true);
                $mail->Subject = 'Application Confirmation';
                $mail->Body    = $emailTemplate;

                $mail->send();
                $message = "Form submitted successfully! A confirmation email has been sent.";
                header("Location: index.html");
            } catch (Exception $e) {
                $message = "Form submitted, but email could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Error submitting form: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill out all required fields correctly.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Registration Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2 class="plan-title">Selected Plan: <?= $_GET['plan'] ?></h2>

    <div class="form-container">
        <?php
        if (!empty($message)) {
            echo "<p>" . htmlspecialchars($message) . "</p>";
        }
        ?>

        <div class="progress-bar" id="progressBar"></div>
        <form id="businessForm" method="POST" action="application-form.php">
            <input type="hidden" name="plan" value="$_GET['plan']?>">
            <div class="form-step">
                <h2>Enter your personal information</h2>
                <input type="text" id="name" name="name" placeholder="Name" required>
                <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
                <button type="button" class="next-btn" disabled>Next</button>
            </div>

            <div class="form-step">
                <h2>Enter your email</h2>
                <input type="email" id="email" name="email" placeholder="Your email address" required>
                <button type="button" class="prev-btn">Previous</button>
                <button type="button" class="next-btn" disabled>Next</button>
            </div>

            <div class="form-step">
                <h2>What is the name of your business?</h2>
                <input type="text" id="businessName" name="businessName" placeholder="Enter business name" required>
                <button type="button" class="prev-btn">Previous</button>
                <button type="button" class="next-btn" disabled>Next</button>
            </div>

            <div class="form-step">
                <h2>Why do you want to use our chat service?</h2>
                <textarea id="reason" name="reason" required placeholder="Your answer"></textarea>
                <button type="button" class="prev-btn">Previous</button>
                <button type="button" class="next-btn" disabled>Next</button>
            </div>

            <div class="form-step">
                <h2>Link to your website</h2>
                <input type="url" id="websiteLink" name="websiteLink" required placeholder="https://example.com">
                <button type="button" class="prev-btn">Previous</button>
                <button type="button" class="next-btn" disabled>Next</button>
            </div>

            <div class="form-step">
                <h2>Google Business Profile (Optional)</h2>
                <input type="url" id="googleProfile" name="googleProfile" placeholder="https://business.google.com">
                <button type="button" class="prev-btn">Previous</button>
                <button type="submit" class="next-btn">Submit</button>
            </div>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>