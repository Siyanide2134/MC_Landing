#!/bin/bash
set -e

TARGET_DIR="/var/www/MC_Landing"
NGINX_CONF="/etc/nginx/sites-available/default"

echo "=== MC_Landing Automated Installer ==="

# 1. Verify script is run as root
if [ "$EUID" -ne 0 ]; then
    echo "Error: Please run this script using sudo."
    exit 1
fi

# 2. Dynamic Package Management Detection
echo "Detecting operating system package manager..."
if command -v apt-get &> /dev/null; then
    # Debian, Ubuntu, Mint, Tailscale standard base
    apt-get update
    apt-get install -y nginx php-fpm php-curl
elif command -v dnf &> /dev/null; then
    # Rocky Linux, AlmaLinux, RHEL, Fedora
    dnf check-update || true
    dnf install -y nginx php-fpm php-curl
elif command -v apk &> /dev/null; then
    # Alpine Linux (Popular for ultra-light containers)
    apk update
    apk add nginx php-fpm php-curl
else
    echo "Error: Supported package manager (apt, dnf, apk) not found."
    echo "Please install nginx, php-fpm, and php-curl manually."
    exit 1
fi

# 3. Establish deployment directory structure
echo "Setting up webroot directory at $TARGET_DIR..."
mkdir -p "$TARGET_DIR"

# 4. Pull latest workspace files into position
if [ -d "src" ]; then
    cp -r src/* "$TARGET_DIR/"
else
    cp -r ./* "$TARGET_DIR/" 2>/dev/null || true
fi

# 5. Handle configuration initialization
if [ -f "$TARGET_DIR/config.example.php" ] && [ ! -f "$TARGET_DIR/config.php" ]; then
    echo "Initializing default config.php profile..."
    cp "$TARGET_DIR/config.example.php" "$TARGET_DIR/config.php"
fi

# 6. Apply optimal Nginx routing blocks
echo "Configuring Nginx server block routing..."
# Ensure sites-available parent layout structure exists for non-Debian formats
mkdir -p "$(dirname "$NGINX_CONF")"
cat << 'EOF' > "$NGINX_CONF"
server {
    listen 80;
    server_name localhost;

    root /var/www/MC_Landing;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }
}
EOF

# 7. Restructure ownership permissions
echo "Adjusting file ownership masks for web service process..."
# Detect if web user is www-data (Debian) or nginx (RHEL/Alpine)
WEB_USER="www-data"
if id "nginx" &>/dev/null; then WEB_USER="nginx"; fi

chown -R "$WEB_USER":"$WEB_USER" "$TARGET_DIR"
chmod -R 775 "$TARGET_DIR"

# 8. Test and kick background daemons
echo "Verifying server rules and restarting infrastructure services..."
nginx -t
systemctl restart nginx php-fpm 2>/dev/null || rc-service nginx restart 2>/dev/null || true

echo "====================================================="
echo " Installation Complete!"
echo " Portal Webroot: $TARGET_DIR"
echo " Next Step: Manually edit $TARGET_DIR/config.php"
echo "====================================================="
