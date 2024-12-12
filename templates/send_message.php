<?php
include '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';  // Include your database connection

$company_id = $_POST['company_id'];
$username = $_POST['username'];
$message_content = mysqli_real_escape_string($conn, $_POST['message_content']);

$insert_sql = "INSERT INTO Messages (company_id, username, message_content) VALUES ($company_id, '$username', '$message_content')";

if ($conn->query($insert_sql) === TRUE) {
    echo "Message sent successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>