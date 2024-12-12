<?php
include '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$url_path = $_SERVER['REQUEST_URI'];
$parts = explode('/', $url_path);
$company_name = $parts[1];

$sql = "SELECT company_id FROM Companies WHERE company_name = '$company_name'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $company = $result->fetch_assoc();
    $company_id = $company['company_id'];
} else {
    $company_id = null;
    echo "No company found for the specified company name.";
    exit;
}

if (isset($_POST['register'])) {
    if ($_POST['password'] !== $_POST['password_confirm']) {
        echo "Passwords do not match.";
        exit;
    }

    $voornaam = $conn->real_escape_string($_POST['voornaam']);
    $naam = $conn->real_escape_string($_POST['naam']);
    $username = $voornaam . " " . $naam;
    $email = $conn->real_escape_string($_POST['email']);
    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if ($company_id !== null) {
        $stmt = $conn->prepare("INSERT INTO User (company_id, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $company_id, $username, $email, $password_hash);
        
        if ($stmt->execute()) {
            header("login.php");
        } else {
            echo "Error: " . $stmt->error;
            error_log("SQL Error: " . $stmt->error);  // Log the SQL error
        }
    } else {
        echo "Error: No company found for the specified company name.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="login_style.css">
</head>

<body>

<section>
    <div class="login-box">
        <form action="register.php" method="POST">
            <h2>Register</h2>

            <div class="input-box">
                <span class="icon"><ion-icon name="person"></ion-icon></span>
                <input type="text" name="voornaam" required>
                <label>First Name</label>
            </div>

            <div class="input-box">
                <span class="icon"><ion-icon name="person"></ion-icon></span>
                <input type="text" name="naam" required>
                <label>Last Name</label>
            </div>

            <div class="input-box">
                <span class="icon"><ion-icon name="mail"></ion-icon></span>
                <input type="email" name="email" required>
                <label>Email</label>
            </div>

            <div class="input-box">
                <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                <input type="password" name="password" required>
                <label>Password</label>
            </div>

            <div class="input-box">
                <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                <input type="password" name="password_confirm" required>
                <label>Confirm Password</label>
            </div>

            <button type="submit" name="register">Register</button>

            <?php if (isset($error)): ?>
                <p style="color: red;"><?= $error ?></p>
            <?php endif; ?>

            <div class="register-link">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </form>
    </div>
</section>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
