#!/bin/bash
# ============================================================
# EC2 UserData Script — Ubuntu 22.04
# Deploys the Konser Ticketing PHP application with AWS RDS
# ============================================================

set -euo pipefail

LOGFILE="/var/log/userdata.log"
exec > >(tee -a "$LOGFILE") 2>&1

echo "========================================"
echo "Starting userdata script at $(date)"
echo "========================================"

# --------------------------------------------------
# 1. Update apt package index
# --------------------------------------------------
echo "[1/8] Updating package lists..."
apt-get update -y

# --------------------------------------------------
# 2. Install Apache2, PHP, extensions, Git, MySQL client
# --------------------------------------------------
echo "[2/8] Installing Apache2, PHP, MySQL client, Git..."
DEBIAN_FRONTEND=noninteractive apt-get install -y \
    apache2 \
    php \
    php-mysql \
    php-curl \
    php-gd \
    php-mbstring \
    php-xml \
    git \
    mysql-client

# --------------------------------------------------
# 3. Remove the default Apache index.html
# --------------------------------------------------
echo "[3/8] Removing default Apache index page..."
rm -f /var/www/html/index.html

# --------------------------------------------------
# 4. Clone the repository into /var/www/html/
# --------------------------------------------------
echo "[4/8] Cloning konser-ticketing repository..."
if [ -d "/var/www/html/konser-ticketing" ]; then
    echo "  Directory already exists — removing before clone..."
    rm -rf /var/www/html/konser-ticketing
fi
git clone https://github.com/reino-r/konser-ticketing.git /var/www/html/konser-ticketing

# --------------------------------------------------
# 5. Create the uploads directory if it doesn't exist
# --------------------------------------------------
echo "[5/8] Ensuring uploads directory exists..."
mkdir -p /var/www/html/konser-ticketing/uploads

# --------------------------------------------------
# 6. Replace database config with AWS RDS credentials
# --------------------------------------------------
echo "[6/8] Updating config.php with RDS connection settings..."
CONFIG_FILE="/var/www/html/konser-ticketing/config.php"

sed -i "s/define('DB_HOST', 'localhost');/define('DB_HOST', 'YOUR_RDS_ENDPOINT_HERE');/" "$CONFIG_FILE"
sed -i "s/define('DB_USER', 'root');/define('DB_USER', 'admin');/" "$CONFIG_FILE"
sed -i "s/define('DB_PASS', '');/define('DB_PASS', 'YOUR_RDS_PASSWORD_HERE');/" "$CONFIG_FILE"
# DB_NAME is already set to 'konser_ticketing' in the cloned config.php

# --------------------------------------------------
# 7. Set ownership (www-data) and permissions (755)
# --------------------------------------------------
echo "[7/8] Setting ownership and permissions..."
chown -R www-data:www-data /var/www/html/konser-ticketing
find /var/www/html/konser-ticketing -type d -exec chmod 755 {} \;
find /var/www/html/konser-ticketing -type f -exec chmod 644 {} \;
chmod 775 /var/www/html/konser-ticketing/uploads

# --------------------------------------------------
# 8. Enable Apache mod_rewrite and restart Apache
# --------------------------------------------------
echo "[8/8] Enabling mod_rewrite and restarting Apache..."
a2enmod rewrite
systemctl restart apache2

echo "========================================"
echo "Userdata script completed at $(date)"
echo "========================================"
