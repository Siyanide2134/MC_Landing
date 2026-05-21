# MC_Landing
A frontend for small Minecraft servers. Includes configurable two-way shoutbox, space for a map plugins, and guides, easily configurable from a single file. Dedicated desktop and mobile views.
# Prerequisites
A discord-compliant webhooks plugin, such as [Discord Chat Hook](https://modrinth.com/plugin/discordchathook) or [LittleHooks](https://modrinth.com/plugin/littlehooks) 
Literally any map plugin
RCON Configured for secure use in ```server.properties```
# Hosting
On most distros, use of ```install.sh``` should work.
```curl -sSL https://raw.githubusercontent.com/Siyanide2134/MC_Landing/main/install.sh | sudo bash```
Afterwards,
```
sudo nano /path/to/config.php
sudo systemctl restart nginx
```
