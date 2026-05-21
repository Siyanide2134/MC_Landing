# MC_Landing
A frontend for small Minecraft servers. Includes configurable two-way shoutbox, space for a map plugins, and guides, easily configurable from a single file. Dedicated desktop and mobile views.
## Prerequisites
1. A discord-compliant webhooks plugin, such as [Discord Chat Hook](https://modrinth.com/plugin/discordchathook) or [LittleHooks](https://modrinth.com/plugin/littlehooks) 
2. Literally any map plugin
3. RCON Configured for secure use in ```server.properties```
## Hosting

### Native
On most distros, use of ```install.sh``` should work.
```
curl -sSL https://raw.githubusercontent.com/Siyanide2134/MC_Landing/main/install.sh | sudo bash
```
Afterwards,
```
sudo nano /path/to/config.php
sudo systemctl restart nginx
```

### Containers
If you prefer containerized environments, a pre-configured multi-container compose block is available.

Configuration
Copy the template profile into a live configuration file:
```
cp config.example.php config.php
```
Edit ```config.php``` to fit your server.

Launch
```docker compose up -d``` or ```podman-compose up -d```

Set Runtime Permissions (If Chat Fails to Log)

Because containers map file boundaries differently, ensure the container's internal web process can append data to your local directory storage:

Docker:
```
sudo chown -R 82:82 .
```
Podman (Rootless):
```
podman unshare chown -R 82:82 .
```
