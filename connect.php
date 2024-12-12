<?php
$servername = "srv1514.hstgr.io";
$username = "u220407022_chatService";
$password = "*bq1c6Lqm24P";
$dbname = "u220407022_chatService";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}/*  else {
 echo "Connected succesfully";
} */
?>