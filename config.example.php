<?php
// Copyright (c) 2026 Siyanide2134. Licensed under the GNU GPL v3.
// This program is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// In a more casual sense, this is a hobbyist utility which has not been tested on larger servers, please don't deploy to some huge public SMP, it will collapse.
/**
 * Server Portal - Core Configuration Template
 * Rename this file to config.php and populate with your production environmental details.
 */

// --- 1. CORE BRANDING ---
$site_name    = "Server Portal";
$accent_color = "#4caf50"; // Global theme color for highlights, panels, and [GAME] tags

// --- 2. ENDPOINTS & INTEGRATIONS ---
$map_url   = "192.168.1.xx:8080";   // Absolute address to squaremap frame

// --- 3. INFRASTRUCTURE PRIVACY KEYS & CONFIG FLAGS ---
$webhook_secret      = "generate_a_secure_token_here"; // URL secret token verification key
$disable_fingerprint = false;                          // KEEP ON BY DEFAULT. Set to true to bypass canvas tracking and fallback to network IP seeds.
$rcon_host           = 'localhost';
$rcon_port           = 25575;
$rcon_pass           = 'craftmine';

// --- 4. DYNAMIC HOOKS (GUIDES, CARDS & CORNER ACCORDIONS) ---
$config_dropdowns = [
    'commands' => [
        'title'   => 'Commands List',
'type'    => 'table',
'headers' => ['Command', 'Effect'],
'rows'    => [
    ['/mvtp [world]', 'Switch Worlds'],
['/h | /home',    'Teleport Home'],
['/sh | /sethome', 'Set Home Location'],
['/b | /back',     'Return to Last Position'],
['/tpa [name]',    'Request Teleport'],
['/msg [name]',    'Private Whisper'],
['/ec | /enderchest',    'Open Portable Ender Chest']
]
    ],
'joining' => [
    'title'   => 'Joining Guide',
'type'    => 'guide',
'content' => [
    ['label' => 'Java Connection IP:', 'code' => '192.168.1.xx', 'img' => 'java_shot.png'],
['label' => 'Bedrock Connection Info:', 'code' => '192.168.1.xx:19132', 'img' => 'bedrock_shot.png']
]
],
'linking' => [
    'title' => 'Link Accounts',
'type'  => 'list',
'items' => [
    '<b>Step 1 (Main):</b> <code>/linkaccount [Alt]</code>',
    '<b>Step 2 (Alt):</b> <code>/linkaccount [Main] [Code]</code>'
]
],
'services' => [
    'title' => 'External Portals',
'type'  => 'links',
'items' => [
    ['label' => 'Crafty Admin Panel', 'url' => 'https://192.168.1.xx:8443'],
['label' => 'Fullscreen Map',    'url' => 'http://192.168.1.xx:8080']
]
]
];
