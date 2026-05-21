<?php
// Copyright (c) 2026 Siyanide2134. Licensed under the GNU GPL v3.
// This program is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// In a more casual sense, this is a hobbyist utility which has not been tested on larger servers, please don't deploy to some huge public SMP, it will collapse.
session_start();

if (!file_exists('config.php')) {
    die("Error: Production configuration target missing.");
}
require_once 'config.php';

$file = 'messages.txt';
$expiry = 86400; // 24 Hours

/**
 * 1. SERVER-SIDE DEVICE IDENTIFICATION (CANVAS SEED INTERACTION OR LAZY BANNED SWITCH)
 */
if (isset($disable_fingerprint) && $disable_fingerprint === true) {
    $device_fp = $_SERVER['REMOTE_ADDR'] ?? 'anonymous_node';
} else {
    $device_fp = $_POST['fp'] ?? $_GET['fp'] ?? 'anonymous_node';
}

$safe_palette = ['#ff5555', '#ffaa00', '#55ff55', '#55ffff', '#ff55ff', '#09d6b2', '#ffca28', '#42a5f5'];
$device_hash = abs(crc32($device_fp));
$assigned_color = $safe_palette[$device_hash % count($safe_palette)];

/**
 * 2. MESSAGE DISTRIBUTION FRAMEWORK
 */
function process_message($username, $message, $is_game = false, $user_color = '#ccc') {
    global $file, $rcon_host, $rcon_port, $rcon_pass, $accent_color;

    $ts = time();
    $username = substr(htmlspecialchars($username), 0, 15);
    $message = substr(htmlspecialchars($message), 0, 200);

    if ($is_game) {
        $display_msg = preg_replace('/\\*\\*(.*?)\\*\\*/', '<b>$1</b>', $message);
        $entry = "<div class='msg' ts='$ts'><b style='color:$accent_color'>[GAME] $username:</b> $display_msg</div>";
    } else {
        $entry = "<div class='msg' ts='$ts'><b style='color:$user_color'>$username:</b> $message</div>";

        $json_msg = json_encode($message);
        $cmd = "tellraw @a [{\"text\":\"[WEB] \",\"color\":\"aqua\"},{\"text\":\"$username: \",\"color\":\"white\",\"bold\":true},{\"text\":$json_msg,\"color\":\"white\",\"bold\":false}]";
        send_rcon($rcon_host, $rcon_port, $rcon_pass, $cmd);
    }

    file_put_contents($file, $entry . "\n", FILE_APPEND | LOCK_EX);
}

function send_rcon($host, $port, $password, $cmd) {
    $socket = @fsockopen($host, $port, $errno, $errstr, 3);
    if (!$socket) return false;
    $req = pack('VV', 1, 3) . $password . "\x00\x00";
    fwrite($socket, pack('V', strlen($req)) . $req);
    fread($socket, 4096);
    $req = pack('VV', 2, 2) . $cmd . "\x00\x00";
    fwrite($socket, pack('V', strlen($req)) . $req);
    fclose($socket);
    return true;
}

// --- 3. INPUT HANDLING ---
$json_input = file_get_contents('php://input');
$webhook_data = json_decode($json_input, true);

if ($webhook_data && isset($webhook_data['content'])) {
    process_message($webhook_data['username'] ?? 'Player', $webhook_data['content'], true);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['msg'])) {
    $_SESSION['n'] = $_POST['n'] ?: 'Guest';
    process_message($_SESSION['n'], $_POST['msg'], false, $assigned_color);
    header("Location: shoutbox.php");
    exit;
}

if (isset($_GET['refresh'])) {
    if (file_exists($file)) {
        $all_lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($all_lines as $line) { echo $line; }
    }
    exit;
}

// --- 4. MAINTENANCE LOG ROTATION ---
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $now = time(); $keep = [];
    foreach ($lines as $line) {
        if (preg_match("/ts='(\d+)'/", $line, $m) && ($now - (int)$m[1]) < $expiry) $keep[] = $line;
    }
    file_put_contents($file, implode("\n", $keep) . (empty($keep) ? "" : "\n"), LOCK_EX);
}

$saved_name = $_SESSION['n'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<title>Live Shoutbox</title>
<style>
/* * FLUID VIEWPORT SYSTEM
 * Forces the document window context to match the true size of the parent <iframe> element container exactly
 */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}
body {
    background: #1a1a1a;
    color: #ccc;
    font-family: sans-serif;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Scrollable log panel wraps perfectly to fill available remaining space */
#l {
flex: 1;
overflow-y: auto;
background: #111;
padding: 15px;
line-height: 1.5;
}
#l::-webkit-scrollbar { width: 4px; }
#l::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }

.msg { margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #333; word-wrap: break-word; }

/* The form tray is locked perfectly to the absolute bottom bounds of the parent element space */
form { background: #1a1a1a; padding: 15px; display: flex; flex-direction: column; gap: 10px; border-top: 1px solid #444; }
input { background: #222; color: #fff; border: 1px solid #444; padding: 12px; border-radius: 4px; outline: none; }
button { background: <?php echo $accent_color; ?>; color: #fff; border: none; padding: 12px; cursor: pointer; font-weight: bold; border-radius: 4px; }
</style>
</head>
<body>
<div id="l">
<?php
if (file_exists($file)) {
    $all_lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($all_lines as $line) { echo $line; }
}
?>
</div>
<form method="POST" id="shoutForm">
<input type="hidden" name="fp" id="fpField" value="anonymous_node">
<input type="text" name="n" placeholder="Nickname" value="<?php echo htmlspecialchars($saved_name); ?>">
<input type="text" name="msg" placeholder="Shout something..." required autocomplete="off">
<button type="submit">SHOUT</button>
</form>

<script>
const log = document.getElementById('l');
function scrollToBottom() { log.scrollTop = log.scrollHeight; }
window.onload = function() {
    generateFingerprint();
    scrollToBottom();
};

function generateFingerprint() {
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 150; canvas.height = 30;

        ctx.textBaseline = "top";
        ctx.font = "13px 'Arial', 'Helvetica Neue', sans-serif";
        ctx.fillStyle = "#f60"; ctx.fillRect(10, 5, 50, 20);
        ctx.fillStyle = "#069"; ctx.fillText("MC_Core_v1", 4, 2);
        ctx.fillStyle = "rgba(50, 150, 50, 0.6)"; ctx.fillText("SeedInit!", 6, 12);

        const pixelData = ctx.getImageData(0, 0, 150, 30).data;
        let hashValue = 0;
        for (let i = 0; i < pixelData.length; i++) {
            hashValue = (hashValue << 5) - hashValue + pixelData[i];
            hashValue |= 0;
        }
        const machineToken = Math.abs(hashValue).toString(16);
        document.getElementById('fpField').value = machineToken;
        return machineToken;
    } catch(e) {
        return 'fallback_node';
    }
}

setInterval(function() {
    const token = document.getElementById('fpField').value;
    fetch('shoutbox.php?refresh=1&fp=' + token + '&t=' + Date.now())
    .then(response => response.text())
    .then(data => {
        if (log.innerHTML !== data) {
            log.innerHTML = data;
            scrollToBottom();
        }
    });
}, 3000);
</script>
</body>
</html>
