<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt');
error_reporting(E_ALL);
session_start();

require '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step'])) {
    if ($_POST['step'] == 'verify_code') {
        // Step 1: Verify Code
        $code = implode('', $_POST['code']);
        $stmt = $conn->prepare("SELECT * FROM BusinessRegistrations WHERE verification_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $business = $result->fetch_assoc();

        if ($business) {
            // Code matches, proceed to account creation
            $_SESSION['business'] = $business;
            header('Location: ?step=create_account');
            exit();
        } else {
            $error = "Invalid code. Please try again.";
        }
    } elseif ($_POST['step'] == 'create_account') {
        try {
            $business = $_SESSION['business'];
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];

            $domain = explode('@', $email)[1];
            $expectedDomain = preg_replace('/^www\./', '', parse_url($business['websiteLink'], PHP_URL_HOST));

            if (!$expectedDomain) {
                throw new Exception("Invalid website link in business registration.");
            }

            if ($domain !== $expectedDomain) {
                throw new Exception("Email must match the business domain ($expectedDomain).");
            }

            if ($password !== $passwordConfirm) {
                throw new Exception("Passwords do not match.");
            }

            if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                throw new Exception("Password must meet security requirements.");
            }

            // Insert into Companies table
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO Companies (company_name, plan, business_email) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("SQL Prepare Error: " . $conn->error);
            }
            $stmt->bind_param("sss", $business['businessName'], $business['plan'], $email);
            if (!$stmt->execute()) {
                throw new Exception("SQL Execute Error: " . $stmt->error);
            }
            $companyId = $stmt->insert_id;

            // Insert into User table
            $username = $firstName . " " . $lastName;
            $adminLevel = 4;
            $stmt = $conn->prepare("INSERT INTO User (company_id, username, email, password, admin_level) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $companyId, $username, $email, $hashedPassword, $adminLevel);
            if (!$stmt->execute()) {
                throw new Exception("SQL Execute Error: " . $stmt->error);
            }

            $currentFolderPath = __DIR__;

            $folderName = str_replace(' ', '-', $business['businessName']);
            $newFolderPath = dirname($currentFolderPath) . "/$folderName";

            if (file_exists($newFolderPath)) {
                throw new Exception("A folder with this name already exists: " . $newFolderPath);
            }

            if (!rename($currentFolderPath, $newFolderPath)) {
                throw new Exception("Failed to rename the folder from $currentFolderPath to $newFolderPath");
            }

            $indexPath = $newFolderPath . "/index.php";
            $loginScript = "<?php\nheader('Location: home.php');\nexit();\n?>";
            if (!file_put_contents($indexPath, $loginScript)) {
                throw new Exception("Failed to create index.php in " . $newFolderPath);
            }

            $templatesDir = '/home/u220407022/domains/feralstorm.com/public_html/chatService/templates';
            $templateFiles = scandir($templatesDir);

            foreach ($templateFiles as $file) {
                $sourceFile = $templatesDir . "/" . $file;
                $destinationFile = $newFolderPath . "/" . $file;
                
                if (is_file($sourceFile)) {
                    if (!copy($sourceFile, $destinationFile)) {
                        throw new Exception("Failed to copy template file: " . $file);
                    }
                }
            }

            header("Location: /$folderName/home.php");
            exit();

        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $error = "An unexpected error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Code Verification</title>
    <style>
        .code-input { width: 3em; height: 3em; text-align: center; font-size: 1.5em; margin: 0.5em; }
        .code-container { display: flex; justify-content: center; }
    </style>
</head>
<body>
<?php if (!isset($_GET['step']) || $_GET['step'] == 'verify_code'): ?>
    <h1>Enter Security Code</h1>
    <form method="POST">
        <div class="code-container">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <input type="text" name="code[]" maxlength="1" class="code-input" required>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="step" value="verify_code">
        <button type="submit">Verify</button>
    </form>
    <?php if (isset($error)): ?><p style="color: red;"><?= $error ?></p><?php endif; ?>
<?php elseif ($_GET['step'] == 'create_account'): ?>
    <h1>Create Account</h1>
    <form method="POST">
        <label>First Name: <input type="text" name="first_name" required></label><br>
        <label>Last Name: <input type="text" name="last_name" required></label><br>
        <label>Business Email: <input type="email" name="email" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>Confirm Password: <input type="password" name="password_confirm" required></label><br>
        <small>Password must be at least 8 characters, include an uppercase letter, a lowercase letter, a number, and a special character.</small><br>
        <input type="hidden" name="step" value="create_account">
        <button type="submit">Create Account</button>
    </form>
    <?php if (isset($error)): ?><p style="color: red;"><?= $error ?></p><?php endif; ?>
<?php endif; ?>
</body>
</html>