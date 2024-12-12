<?php
include '/home/u220407022/domains/feralstorm.com/public_html/chatService/connect.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$url_path = $_SERVER['REQUEST_URI'];  
$parts = explode('/', $url_path);  
$company_name = $parts[1];  

$sql = "SELECT company_id FROM Companies WHERE company_name = '$company_name'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $company = $result->fetch_assoc();
    $company_id = $company['company_id'];
    $_SESSION['company_id'] = $company_id;
} else {
    $company_id = null;
    echo "No company found with the name: $company_name.";
    exit;
}

$sql = "SELECT * FROM Messages WHERE company_id = $company_id ORDER BY timestamp ASC";
$messages_result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_content'])) {
    $username = $_SESSION['username'];
    $message_content = mysqli_real_escape_string($conn, $_POST['message_content']);
    
    $insert_sql = "INSERT INTO Messages (company_id, username, message_content) VALUES ($company_id, '$username', '$message_content')";
    
    if ($conn->query($insert_sql) === TRUE) {
        echo "Message sent successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo $company_name; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo $company_name; ?></h2>
            </div>
            <div class="channels">
                <?php
                $sql = "SELECT 2FA_enabled FROM Companies WHERE company_id = '$company_id'";
                $result = $conn->query($sql);
                $show_2fa_link = false;

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $is_2fa_enabled = $row['2FA_enabled'];
                    $link_text_admin = "Disable 2FA for everyone";
                    if ($is_2fa_enabled == 0) {
                        $show_2fa_link = true;
                        $link_text_admin = "Enable 2FA for everyone";
                    }
                }
                if ($show_2fa_link): ?>
                    <div class="channel"><a href="2fa.php"><ion-icon name="settings-sharp"></ion-icon>Switch 2FA settings</a></div>
                <?php endif; ?>

                <?php if ($_SESSION['admin_level'] == 2 || $_SESSION['admin_level'] == 3): ?>
                    <div class="channel"><a href="2faAdmin.php"><ion-icon name="settings-sharp"></ion-icon><?php echo $link_text_admin ?></a></div>
                <?php endif; ?>
                <div class="channel"><a href="logout.php">Logout</a></div>
            </div>
        
        </div>
        <div class="chat-area">
            <!-- Header -->
            <div class="chat-header">
                <input type="text" id="searchBar" placeholder="Search messages...">
            </div>

            <div id="messages" class="messages">
                <!-- Messages will be loaded dynamically here -->
            </div>

            <div class="chat-input">
                <form id="chatForm" method="POST">
                    <textarea id="messageInput" name="message_content" placeholder="Type a message..." required></textarea>
                    <button type="submit" id="sendBtn">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function isAtBottom() {
            const messagesContainer = document.getElementById("messages");
            return messagesContainer.scrollHeight - messagesContainer.scrollTop === messagesContainer.clientHeight;
        }

        function scrollToBottom() {
            const messagesContainer = document.getElementById("messages");
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Load new messages via AJAX
        function loadMessages() {
            const company_id = "<?php echo $company_id; ?>";
            const url = 'fetch_messages.php?company_id=' + company_id;

            fetch(url)
                .then(response => response.text())
                .then(data => {
                    const messagesContainer = document.getElementById("messages");
                    const wasAtBottom = isAtBottom();

                    messagesContainer.innerHTML = data;

                    if (wasAtBottom) {
                        scrollToBottom();
                    }
                });
        }

        // Send message via AJAX
        document.getElementById('chatForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const messageContent = document.getElementById('messageInput').value;
            const company_id = "<?php echo $company_id; ?>";
            const username = "<?php echo $_SESSION['username']; ?>";

            fetch('send_message.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'company_id': company_id,
                    'username': username,
                    'message_content': messageContent
                })
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('messageInput').value = '';
                loadMessages();
            });
        });

        setInterval(loadMessages, 800);

        loadMessages();
    </script>
        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
