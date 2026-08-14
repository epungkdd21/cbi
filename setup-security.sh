#!/bin/bash

# ============================================
# CBI Admin System - Security Setup Script
# Run this after deployment
# ============================================

set -e

echo "🔐 CBI Admin System - Security Setup"
echo "===================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as web user
echo "📋 Checking environment..."

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p logs
mkdir -p config
mkdir -p uploads/{articles,events,gallery,general,thumbnails}
mkdir -p db/backups

# Set proper permissions
echo "🔐 Setting proper file permissions..."
chmod 755 logs
chmod 755 config
chmod 755 uploads
chmod 755 uploads/{articles,events,gallery,general,thumbnails}
chmod 755 db/backups

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found${NC}"
    echo "Creating .env from .env.example..."
    cp .env.example .env
    chmod 600 .env
    echo -e "${RED}✓ .env created. Please update with your values!${NC}"
else
    echo "✓ .env file exists"
fi

# Check if config.php is protected
if [ -f config.php ]; then
    echo "🔐 Securing config.php..."
    chmod 600 config.php
    echo "✓ config.php permissions set to 600"
fi

# Generate encryption key if needed
if ! grep -q "ENCRYPTION_KEY=" .env || grep -q "ENCRYPTION_KEY=your_encryption_key" .env; then
    echo -e "${YELLOW}🔑 Generating encryption key...${NC}"
    ENCRYPTION_KEY=$(openssl rand -hex 32)
    sed -i "s/ENCRYPTION_KEY=.*/ENCRYPTION_KEY=$ENCRYPTION_KEY/" .env
    echo "✓ Encryption key generated and saved to .env"
fi

# Create PHP-FPM pool configuration if needed
if [ ! -f "config/pool.conf" ]; then
    echo "⚙️  Creating PHP-FPM pool configuration..."
    cat > config/pool.conf << 'EOF'
; CBI Admin Pool Configuration
[cbi]
user = www-data
group = www-data
listen = 127.0.0.1:9000
listen.owner = www-data
listen.group = www-data

; Process management
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

; Security settings
catch_workers_output = yes
clear_env = no
EOF
fi

# Create logrotate configuration
echo "📝 Creating logrotate configuration..."
cat > config/cbi-logrotate << 'EOF'
/workspaces/cbi/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0600 www-data www-data
    sharedscripts
    postrotate
        # Restart PHP-FPM if needed
        systemctl reload php-fpm > /dev/null 2>&1 || true
    endscript
}
EOF

# Create .htpasswd for directory protection (optional)
if [ ! -f "config/.htpasswd" ]; then
    echo ""
    read -p "Do you want to set up HTTP basic auth for admin panel? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Creating .htpasswd file..."
        htpasswd -c config/.htpasswd admin
        chmod 600 config/.htpasswd
    fi
fi

# Security checklist
echo ""
echo "🔒 Security Checklist"
echo "===================="
echo ""

checks=(
    "✓ Directories created"
    "✓ File permissions set"
)

# Check permissions
if [ -f config.php ] && [ $(stat -c "%a" config.php) = "600" ]; then
    checks+=("✓ config.php permissions (600)")
else
    checks+=("✗ config.php permissions need review")
fi

if [ -f .env ] && [ $(stat -c "%a" .env) = "600" ]; then
    checks+=("✓ .env permissions (600)")
else
    checks+=("✗ .env permissions need review")
fi

if [ -f .htaccess ]; then
    checks+=("✓ .htaccess configured")
else
    checks+=("✗ .htaccess missing")
fi

if [ -f security.php ]; then
    checks+=("✓ security.php module present")
else
    checks+=("✗ security.php missing")
fi

if [ -f api/middleware.php ]; then
    checks+=("✓ API middleware configured")
else
    checks+=("✗ API middleware missing")
fi

if [ -f api/waf.php ]; then
    checks+=("✓ WAF module configured")
else
    checks+=("✗ WAF module missing")
fi

for check in "${checks[@]}"; do
    echo "$check"
done

echo ""
echo "🚀 Next Steps"
echo "============="
echo ""
echo "1. Update .env file with your values:"
echo "   - Database credentials"
echo "   - SMTP settings"
echo "   - Encryption key (already generated)"
echo ""
echo "2. Create database and import schema:"
echo "   mysql -u root -p < db/schema.sql"
echo ""
echo "3. Create first admin user:"
echo "   php -r 'include \"config.php\"; ...'"
echo ""
echo "4. Review security settings in SECURITY.md"
echo ""
echo "5. Set up regular backups"
echo ""
echo "6. Configure monitoring and alerts"
echo ""
echo "7. Test the system:"
echo "   - Visit https://your-domain.com/login"
echo "   - Review security logs"
echo ""
echo -e "${GREEN}✅ Setup complete!${NC}"
echo ""
echo "For detailed security guide, see: SECURITY.md"
echo ""
