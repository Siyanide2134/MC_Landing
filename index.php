<?php
// Copyright (c) 2026 Siyanide2134. Licensed under the GNU GPL v3.
// This program is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// In a more casual sense, this is a hobbyist utility which has not been tested on larger servers, please don't deploy to some huge public SMP, it will collapse.
if (!file_exists('config.php')) {
    die("Error: Production configuration target missing.");
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="icon" type="image/x-icon" href="favicon.ico">
<title><?php echo htmlspecialchars($site_name); ?></title>
<style>
:root { --bg: #121212; --panel: #1a1a1a; --accent: <?php echo $accent_color; ?>; --text: #eee; }
* { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

html, body {
    margin: 0; padding: 0; width: 100%; height: 100%;
    background: var(--bg); color: var(--text); font-family: sans-serif;
    overflow: hidden; position: fixed;
}

.wrapper { display: flex; width: 100%; height: 100%; }

.side-panel {
    width: 30%; background: var(--panel); border-right: 1px solid #333;
    display: flex; flex-direction: column; height: 100%;
}

.shout-container { flex: 1; min-height: 0; border-bottom: 1px solid #333; display: flex; }
.shout-container iframe { width: 100%; height: 100%; border: none; }

.guides-container {
    padding: 20px; background: transparent; overflow-y: auto;
    display: flex; flex-direction: column; gap: 10px;
}
.guides-container::-webkit-scrollbar { width: 4px; }
.guides-container::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }

.main-panel { flex: 1; background: #000; height: 100%; position: relative; }
.main-panel iframe { width: 100%; height: 100%; border: none; display: block; }

/* --- ACCORDION SYSTEM --- */
details { width: 100%; }
summary {
    list-style: none; outline: none; cursor: pointer;
    background: #222; color: #aaa; padding: 14px;
    border-radius: 6px; font-size: 15px; border: 1px solid #333;
    text-align: center; font-weight: bold; transition: background 0.2s;
}
summary::-webkit-details-marker { display: none; }
details[open] summary { border-radius: 6px 6px 0 0; background: var(--accent); color: white; }

.dropdown-content {
    background: rgba(0,0,0,0.2); padding: 15px; border: 1px solid #333; border-top: none;
    border-radius: 0 0 6px 6px;
}

.guide-item { margin-bottom: 15px; }
.guide-item:last-child { margin-bottom: 0; }
.guide-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: 4px; margin: 10px 0; border: 1px solid #333; }

table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; color: var(--accent); padding-bottom: 5px; border-bottom: 1px solid #333; }
td { padding: 8px 0; border-bottom: 1px solid #222; }
code { background: #000; padding: 2px 4px; border-radius: 3px; color: #ffca28; font-family: monospace; }
ul { margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.6; }

/* --- PORTAL NAVIGATION BUTTONS --- */
.svc-link {
    display: block; text-align: center; background: #222; color: #eee;
    padding: 12px; border-radius: 4px; text-decoration: none;
    font-weight: bold; font-size: 13px; border: 1px solid #333;
    transition: background 0.2s, color 0.2s; margin-bottom: 8px;
}
.svc-link:last-child { margin-bottom: 0; }
.svc-link:hover { background: var(--accent); color: #fff; border-color: transparent; }

/* --- RESPONSIVE HANDOFF PANEL --- */
.mobile-nav { display: none; }
@media (max-width: 768px) {
    .wrapper { height: calc(100% - 60px); }
    .side-panel { width: 100%; display: none; }
    .main-panel { width: 100%; display: none; }
    .side-panel.active, .main-panel.active { display: flex; flex: 1; }
    .mobile-nav {
        display: flex; position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
        background: #1a1a1a; border-top: 1px solid #333;
        justify-content: space-around; align-items: center;
    }
    .nav-item { color: #666; font-size: 12px; flex: 1; text-align: center; cursor: pointer; }
    .nav-item.active { color: var(--accent); font-weight: bold; }
}
</style>
</head>
<body>
<div class="wrapper">
<div id="side" class="side-panel">
<div id="shout-view" class="shout-container">
<iframe src="shoutbox.php"></iframe>
</div>

<div id="guide-view" class="guides-container">
<?php foreach ($config_dropdowns as $id => $dropdown): ?>
<details id="details-<?php echo $id; ?>">
<summary><?php echo htmlspecialchars($dropdown['title']); ?></summary>
<div class="dropdown-content">

<?php if ($dropdown['type'] === 'table'): ?>
<table>
<tr>
<?php foreach ($dropdown['headers'] as $header): ?>
<th><?php echo htmlspecialchars($header); ?></th>
<?php endforeach; ?>
</tr>
<?php foreach ($dropdown['rows'] as $row): ?>
<tr>
<td><code><?php echo htmlspecialchars($row[0]); ?></code></td>
<td><?php echo htmlspecialchars($row[1]); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php elseif ($dropdown['type'] === 'guide'): ?>
<?php foreach ($dropdown['content'] as $index => $item): ?>
<div class="guide-item">
<p><?php echo $item['label']; ?> <code><?php echo htmlspecialchars($item['code']); ?></code></p>
<?php if (!empty($item['img'])): ?>
<img src="<?php echo htmlspecialchars($item['img']); ?>" class="guide-img">
<?php endif; ?>
<?php if ($index < count($dropdown['content']) - 1): ?>
<hr style="border:0; border-top:1px solid #333; margin: 15px 0;">
<?php endif; ?>
</div>
<?php endforeach; ?>

<?php elseif ($dropdown['type'] === 'list'): ?>
<ul>
<?php foreach ($dropdown['items'] as $item): ?>
<li><?php echo $item; ?></li>
<?php endforeach; ?>
</ul>

<?php elseif ($dropdown['type'] === 'links'): ?>
<div style="display: flex; flex-direction: column;">
<?php foreach ($dropdown['items'] as $link): ?>
<a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="svc-link">
<?php echo htmlspecialchars($link['label']); ?>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</details>
<?php endforeach; ?>
</div>
</div>

<div id="main" class="main-panel active">
<iframe src="<?php echo htmlspecialchars($map_url); ?>"></iframe>
</div>
</div>

<nav class="mobile-nav">
<div class="nav-item active" onclick="switchTab('map', this)">Map</div>
<div class="nav-item" onclick="switchTab('shout', this)">Shoutbox</div>
<div class="nav-item" onclick="switchTab('guides', this)">Guides</div>
</nav>

<script>
function switchTab(tab, el) {
    const side = document.getElementById('side');
    const main = document.getElementById('main');
    const shout = document.getElementById('shout-view');
    const guide = document.getElementById('guide-view');
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    if (tab === 'map') {
        side.classList.remove('active');
        main.classList.add('active');
    } else {
        main.classList.remove('active');
        side.classList.add('active');
        shout.style.display = (tab === 'shout') ? 'flex' : 'none';
        guide.style.display = (tab === 'guides') ? 'flex' : 'none';
    }
}
</script>
</body>
</html>
