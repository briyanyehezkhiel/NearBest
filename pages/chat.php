<?php
session_start();
include "../db.php";

if(!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

$current_user = $_SESSION['username'];
$receiver = isset($_GET['to']) ? $_GET['to'] : 'admin';

// Ambil semua pesan antara current user dan receiver
$sql = "SELECT * FROM chat_messages 
        WHERE (sender_username = '$current_user' AND receiver_username = '$receiver')
        OR (sender_username = '$receiver' AND receiver_username = '$current_user')
        ORDER BY created_at ASC";
$messages_result = mysqli_query($conn, $sql);

// Update pesan yang diterima menjadi sudah dibaca
mysqli_query($conn, "UPDATE chat_messages SET is_read = 1 
                     WHERE receiver_username = '$current_user' 
                     AND sender_username = '$receiver'");

// Ambil daftar user untuk chat list (untuk admin bisa lihat semua user)
$users_sql = "SELECT DISTINCT 
              CASE 
                  WHEN sender_username = '$current_user' THEN receiver_username
                  ELSE sender_username
              END as chat_partner
              FROM chat_messages
              WHERE sender_username = '$current_user' OR receiver_username = '$current_user'
              UNION
              SELECT 'admin' as chat_partner
              ORDER BY chat_partner";
$users_result = mysqli_query($conn, $users_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat - NearBest</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
    body { background:#f6f6f6; }
    
    .chat-container {
        max-width:1200px;
        margin:40px auto;
        background:#fff;
        border-radius:12px;
        box-shadow:0 3px 15px rgba(0,0,0,0.1);
        display:flex;
        height:calc(100vh - 200px);
        min-height:600px;
    }
    
    .chat-sidebar {
        width:300px;
        border-right:1px solid #eee;
        background:#f9f9f9;
        overflow-y:auto;
    }
    
    .chat-sidebar-header {
        padding:20px;
        background:#3b2db2;
        color:white;
        font-weight:bold;
        font-size:18px;
    }
    
    .chat-user-item {
        padding:15px 20px;
        border-bottom:1px solid #eee;
        cursor:pointer;
        transition:.2s;
        display:flex;
        align-items:center;
        gap:10px;
    }
    
    .chat-user-item:hover {
        background:#fff;
    }
    
    .chat-user-item.active {
        background:#3b2db2;
        color:white;
    }
    
    .user-avatar {
        width:40px;
        height:40px;
        border-radius:50%;
        background:#3b2db2;
        display:flex;
        align-items:center;
        justify-content:center;
        color:white;
        font-weight:bold;
    }
    
    .chat-user-item.active .user-avatar {
        background:white;
        color:#3b2db2;
    }
    
    .chat-main {
        flex:1;
        display:flex;
        flex-direction:column;
    }
    
    .chat-header {
        padding:20px;
        border-bottom:1px solid #eee;
        background:#f9f9f9;
    }
    
    .chat-header h3 {
        color:#333;
        font-size:20px;
    }
    
    .chat-messages {
        flex:1;
        padding:20px;
        overflow-y:auto;
        background:#fafafa;
    }
    
    .message {
        margin-bottom:15px;
        display:flex;
        flex-direction:column;
    }
    
    .message.sent {
        align-items:flex-end;
    }
    
    .message.received {
        align-items:flex-start;
    }
    
    .message-bubble {
        max-width:70%;
        padding:12px 16px;
        border-radius:18px;
        word-wrap:break-word;
    }
    
    .message.sent .message-bubble {
        background:#3b2db2;
        color:white;
        border-bottom-right-radius:4px;
    }
    
    .message.received .message-bubble {
        background:#e9ecef;
        color:#333;
        border-bottom-left-radius:4px;
    }
    
    .message-time {
        font-size:11px;
        color:#999;
        margin-top:5px;
        padding:0 5px;
    }
    
    .chat-input-area {
        padding:20px;
        border-top:1px solid #eee;
        background:#fff;
    }
    
    .chat-input-form {
        display:flex;
        gap:10px;
    }
    
    .chat-input {
        flex:1;
        padding:12px;
        border:1px solid #ddd;
        border-radius:25px;
        font-size:15px;
        outline:none;
    }
    
    .chat-input:focus {
        border-color:#3b2db2;
    }
    
    .send-btn {
        padding:12px 30px;
        background:#3b2db2;
        color:white;
        border:none;
        border-radius:25px;
        cursor:pointer;
        font-weight:500;
        transition:.2s;
    }
    
    .send-btn:hover {
        background:#2c2297;
    }
    
    .empty-chat {
        text-align:center;
        color:#999;
        padding:40px;
    }
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">💬 Chat</div>
        <div class="chat-user-item <?php echo $receiver == 'admin' ? 'active' : ''; ?>" onclick="location.href='chat.php?to=admin'">
            <div class="user-avatar">A</div>
            <div>
                <div style="font-weight:500;">Admin</div>
                <div style="font-size:12px;color:#999;">Support</div>
            </div>
        </div>
        <?php while($user = mysqli_fetch_assoc($users_result)): 
            if($user['chat_partner'] == 'admin' || $user['chat_partner'] == $current_user) continue;
        ?>
        <div class="chat-user-item <?php echo $receiver == $user['chat_partner'] ? 'active' : ''; ?>" onclick="location.href='chat.php?to=<?php echo $user['chat_partner']; ?>'">
            <div class="user-avatar"><?php echo strtoupper(substr($user['chat_partner'], 0, 1)); ?></div>
            <div>
                <div style="font-weight:500;"><?php echo htmlspecialchars($user['chat_partner']); ?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="chat-main">
        <div class="chat-header">
            <h3>💬 Chat dengan <?php echo htmlspecialchars($receiver); ?></h3>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <?php if(mysqli_num_rows($messages_result) > 0): ?>
                <?php while($msg = mysqli_fetch_assoc($messages_result)): 
                    $is_sent = $msg['sender_username'] == $current_user;
                ?>
                <div class="message <?php echo $is_sent ? 'sent' : 'received'; ?>">
                    <div class="message-bubble">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    <div class="message-time">
                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-chat">
                    <p>Belum ada pesan. Mulai percakapan!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-input-area">
            <form method="POST" action="process_chat.php" class="chat-input-form" id="chatForm">
                <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($receiver); ?>">
                <input type="text" name="message" class="chat-input" placeholder="Ketik pesan..." required autocomplete="off">
                <button type="submit" class="send-btn">Kirim</button>
            </form>
        </div>
    </div>
</div>

<script>
// Auto scroll ke bawah
const chatMessages = document.getElementById('chatMessages');
chatMessages.scrollTop = chatMessages.scrollHeight;

// Auto refresh setiap 3 detik
setInterval(function() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_messages.php?to=<?php echo $receiver; ?>', true);
    xhr.onload = function() {
        if(xhr.status === 200) {
            const newMessages = JSON.parse(xhr.responseText);
            if(newMessages.length > 0) {
                location.reload();
            }
        }
    };
    xhr.send();
}, 3000);

// Submit form dengan Enter
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'process_chat.php', true);
    xhr.onload = function() {
        if(xhr.status === 200) {
            location.reload();
        }
    };
    xhr.send(formData);
    this.querySelector('input[name="message"]').value = '';
});
</script>

<?php include "../includes/footer.php"; ?>
</body>
</html>

