<?php
include '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';

$company_id = $_GET['company_id'];

$sql = "SELECT * FROM Messages WHERE company_id = $company_id ORDER BY timestamp ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<div class='message'>";
    echo "<strong>" . htmlspecialchars($row['username']) . ":</strong>";
    echo "<p>" . htmlspecialchars($row['message_content']) . "</p>";
    echo "<small>" . $row['timestamp'] . "</small>";
    echo "</div>";
}
?>
