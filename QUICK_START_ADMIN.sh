#!/bin/bash

# Quick Admin User Setup Guide
# This file provides quick instructions to create admin user

cat << 'EOF'

╔══════════════════════════════════════════════════════════════════╗
║     🔑 CBI Admin System - Quick Start (Create Admin User)       ║
╚══════════════════════════════════════════════════════════════════╝

✅ REQUIREMENTS CHECKLIST:
   □ Database imported (db/schema.sql)
   □ config.php configured with DB credentials
   □ security.php exists in root
   □ create-admin.php exists in root

════════════════════════════════════════════════════════════════════

🚀 QUICK START - Choose Your Method:

════════════════════════════════════════════════════════════════════

METHOD 1: CLI (Recommended) - Fast & Secure
────────────────────────────────────────────

1. Open terminal and navigate to project:
   $ cd /path/to/cbi

2. Run the script:
   $ php create-admin.php

3. Follow the prompts:
   ✓ Enter username (e.g., admin)
   ✓ Enter email (e.g., admin@example.com)
   ✓ Enter password (minimum 8 chars)
   ✓ Confirm password
   ✓ Review details
   ✓ Confirm creation

4. Done! Login at: https://yoursite.com/login

════════════════════════════════════════════════════════════════════

METHOD 2: Web Browser - GUI Interface
─────────────────────────────────────

1. Open your browser and go to:
   https://yoursite.com/create-admin.php

2. Fill in the form:
   • Username: Enter your admin username
   • Email: Enter admin email
   • Password: Enter strong password
   • Confirm Password: Re-enter password

3. Watch password strength meter

4. Click "Create Admin User" button

5. Done! Go to login page

════════════════════════════════════════════════════════════════════

⚠️  SECURITY CHECKLIST:
────────────────────────

After creating admin:

[ ] 1. Delete create-admin.php from server:
       $ rm create-admin.php

[ ] 2. Login to admin panel at /login

[ ] 3. Change password if needed (in admin settings)

[ ] 4. Review security logs (SECURITY.md)

[ ] 5. Set up HTTPS (production only)

[ ] 6. Configure email settings (in config.php)

[ ] 7. Set up regular backups

[ ] 8. Monitor security logs weekly

════════════════════════════════════════════════════════════════════

🔐 PASSWORD TIPS:
─────────────────

Strong Password Example:
❌ password123
❌ admin
✅ MyAdm!n@2024Secure

Requirements:
• Minimum 8 characters
• Mix uppercase and lowercase
• Include numbers
• Include special characters
• No personal information
• No dictionary words

════════════════════════════════════════════════════════════════════

🐛 TROUBLESHOOTING:
────────────────────

Database Error?
→ Check config.php DB credentials
→ Verify database is created
→ Run: mysql -u root -p < db/schema.sql

Username Already Exists?
→ Use different username
→ Or delete old user from database

Script Not Running?
→ Check: php --version
→ Check file permissions: chmod 755 create-admin.php
→ Try: /usr/bin/php create-admin.php

═══════════════════════════════════════════════════════════════════

📚 MORE INFORMATION:
────────────────────

Detailed Guide:     CREATE_ADMIN_GUIDE.md
Security Guide:     SECURITY.md
Project Setup:      SETUP.md

════════════════════════════════════════════════════════════════════

Ready? Let's go! 🚀

EOF

EOF
