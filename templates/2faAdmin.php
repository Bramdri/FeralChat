<?php
include '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';
session_start();

$company_id = $_SESSION['company_id'];

$query_sql = "SELECT 2FA_enabled FROM Companies WHERE company_id = '$company_id'";
$result = $conn->query($query_sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_status = $row['2FA_enabled'];
    
    if($current_status == 0){
        $updateUser_sql = "UPDATE User SET 2FA_enabled = 1 WHERE company_id = '$company_id'";
    if ($conn->query($updateUser_sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => '2FA set to true for everyone.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    }

    $new_status = ($current_status == 1) ? 0 : 1;

    $update_sql = "UPDATE Companies SET 2FA_enabled = $new_status WHERE company_id = '$company_id'";
    if ($conn->query($update_sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => '2FA setting updated successfully for the company.']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
header("Location: logout.php");
?>