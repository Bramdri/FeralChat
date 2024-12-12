<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
require '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';
require '/home/u220407022/domains/feralstorm.com/public_html/chatService/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;



if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

function getApplications($conn) {
    $sql = "SELECT * FROM BusinessRegistrations";
    $result = $conn->query($sql);
    $applications = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
    }
    return $applications;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && isset($_POST['applicant_id'])) {
    $action = $_POST['action'];
    $applicantId = intval($_POST['applicant_id']);
    
    if ($action === "approve") {
        $stmt = $conn->prepare("SELECT * FROM BusinessRegistrations WHERE id = ?");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $verificationCode = sprintf("%06d", mt_rand(1, 999999));
        
        if ($result->num_rows > 0) {
            $applicant = $result->fetch_assoc();
            $genCode = $conn->prepare("UPDATE BusinessRegistrations SET verification_code = ? WHERE id = ?");
            $genCode->bind_param("si", $verificationCode, $applicantId);
            $genCode->execute();
            
            if($applicant['plan'] == "Standard"){
                $payment_link = 'https://buy.stripe.com/test_8wMcQh82j9Hw2OIbII';
            } elseif ($applicant['plan'] == "Pro"){
                $payment_link = 'https://buy.stripe.com/test_aEU03v6Yf3j860U289';
            }else{
                $payment_link = 'https://buy.stripe.com/test_8wM8A1cizaLAcpi6oq';
            }
            
            
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'smtp.hostinger.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = 'password@feralstorm.com';
            $mail->Password = 'PasswordReset3#';
            $mail->setFrom('password@feralstorm.com', 'FeralChat Approval');
            $mail->addReplyTo('password@feralstorm.com', 'FeralChat Approval');
            $mail->addAddress($applicant['email'], $applicant['email']);
            $mail->Subject = "You've been approved for FeralChat";
            
            $html_body = file_get_contents('email_templates/approval.html');
            if ($html_body === false) {
                echo 'Could not open email template.';
                exit;
            }

            $html_body = str_replace('{{payment_link}}', $payment_link, $html_body);
            $html_body = str_replace('{{verification_code}}', $verificationCode, $html_body);
            $mail->msgHTML($html_body);

            if (!$mail->send()) {
                $reset_error = 'Mailer Error: ' . $mail->ErrorInfo;
            }
            
        }
    } elseif ($action === "deny") {
        $stmt = $conn->prepare("DELETE FROM BusinessRegistrations WHERE id = ?");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'overview';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard [Approvals only]</title>
</head>
<body>
                <table>
                    <thead>
                        <tr><td>Name</td><td>Email</td><td>Business Name</td><td>Plan</td><td>Reason</td><td>Actions</td></tr>
                    </thead>
                    <tbody>
                        <?php
                        $applications = getApplications($conn);
                        foreach ($applications as $applicant): ?>
                            <tr>
                                <td><?= htmlspecialchars($applicant['name']) ?></td>
                                <td><?= htmlspecialchars($applicant['email']) ?></td>
                                <td><?= htmlspecialchars($applicant['businessName']) ?></td>
                                <td><?= htmlspecialchars($applicant['plan']) ?></td>
                                <td><?= htmlspecialchars($applicant['reason']) ?></td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="applicant_id" value="<?= $applicant['id'] ?>">
                                        <button type="submit" name="action" value="approve">Approve</button>
                                        <button type="submit" name="action" value="deny">Deny</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <a href="logout.php">logout</a>

    <!-- Scripts -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>