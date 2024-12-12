<?php
include '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';
session_start();
$username = $_SESSION['username'];
$query_sql = "SELECT 2FA_enabled FROM User WHERE username = '$username'";
$result = $conn->query($query_sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $is_2fa_enabled = $row['2FA_enabled'];
    $newstatus = 1;
    if ($is_2fa_enabled == 1){
        $newstatus = 0;
    };
        $update_sql = "UPDATE User SET 2FA_enabled = $newstatus WHERE username = '$username'";
    if ($conn->query($update_sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => '2FA setting updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
header("Location: logout.php");
?>