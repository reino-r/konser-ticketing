# KonserTicketing — Concert Ticketing CRUD

Minimalist concert management system built with pure PHP, Bootstrap 5, and MySQL.

## Local Setup (XAMPP)

1. **Start XAMPP** — Apache & MySQL services.

2. **Clone / copy** this folder into `C:\xampp\htdocs\konser-ticketing`.

3. **Create the database**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`), or
   - Run the included schema from the command line:
     ```bash
     mysql -u root < schema.sql
     ```

4. **Verify config** — `config.php` already points to XAMPP defaults:
   ```
   Host: localhost | User: root | Pass: (empty) | DB: konser_ticketing
   ```

5. **Open the app** at:
   ```
   http://localhost/konser-ticketing
   ```

6. **Upload folder** — The `uploads/` directory must be writable by the web server.

## File Structure

```
konser-ticketing/
├── config.php        # Database constants (XAMPP active, RDS commented)
├── db.php            # Singleton MySQLi connection
├── schema.sql        # Database & table DDL
├── index.php         # List concerts (card grid)
├── create.php        # Add concert with image upload
├── edit.php          # Edit concert
├── delete.php        # Delete concert + poster file
├── uploads/          # Uploaded poster images
└── README.md
```

## AWS EC2 Deployment

This script can be used as EC2 **UserData** to bootstrap a fresh Amazon Linux 2023 instance:

```bash
#!/bin/bash
set -e

# Update system
dnf update -y

# Install Apache, PHP 8.x, MySQL client
dnf install -y httpd php php-mysqli php-gd php-mbstring mysql

# Start & enable Apache
systemctl enable --now httpd

# Allow Apache through firewall
if systemctl is-active firewalld &>/dev/null; then
  firewall-cmd --permanent --add-service=http
  firewall-cmd --reload
fi

# Set document root
DOC_ROOT="/var/www/html/konser-ticketing"
mkdir -p "$DOC_ROOT"

# Download project (replace with your actual source)
# Option A: From GitHub
# yum install -y git
# git clone https://github.com/your-org/konser-ticketing.git "$DOC_ROOT"

# Option B: Upload manually via SCP/SFTP to /var/www/html/konser-ticketing

# Set permissions
chown -R apache:apache "$DOC_ROOT"
chmod -R 755 "$DOC_ROOT"
chmod 775 "$DOC_ROOT/uploads"

# Install RDS CA certificate (for SSL)
curl -sS https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem \
  -o /etc/pki/tls/certs/rds-ca.pem

echo "Deployment complete. Edit config.php to use RDS credentials."
```

After the instance boots, edit `config.php` to uncomment the AWS RDS section and fill in your RDS endpoint and password.

## Notes

- No authentication — all pages are publicly accessible.
- Price is stored as `DECIMAL(12,0)` in IDR (Rupiah).
- Poster images get a timestamp + random hex prefix to avoid name collisions.
- Uses MySQLi prepared statements to prevent SQL injection.
