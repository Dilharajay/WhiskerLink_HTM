<?php
/**
 * Email Configuration File
 * Store your SMTP settings here
 */

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');           // SMTP server (Gmail, Outlook, etc.)
define('SMTP_PORT', 587);                        // Port (587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email address
define('SMTP_PASSWORD', 'your-app-password');    // Your email password or App Password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // From email address
define('SMTP_FROM_NAME', 'WhiskerLink Platform'); // From name

// Email encryption type
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

/**
 * HOW TO GET APP PASSWORD FOR GMAIL:
 * 
 * 1. Go to your Google Account: https://myaccount.google.com/
 * 2. Click on "Security" in the left menu
 * 3. Enable "2-Step Verification" if not already enabled
 * 4. After enabling 2-Step Verification, search for "App passwords"
 * 5. Click "App passwords"
 * 6. Select "Mail" and "Other (Custom name)"
 * 7. Enter "WhiskerLink" as the custom name
 * 8. Click "Generate"
 * 9. Copy the 16-character password (without spaces)
 * 10. Use this password as SMTP_PASSWORD above
 * 
 * IMPORTANT: Never share or commit this password to version control!
 */

/**
 * OTHER SMTP PROVIDERS:
 * 
 * OUTLOOK/HOTMAIL:
 * Host: smtp.office365.com or smtp-mail.outlook.com
 * Port: 587
 * Encryption: TLS
 * 
 * YAHOO MAIL:
 * Host: smtp.mail.yahoo.com
 * Port: 587 or 465
 * Encryption: TLS or SSL
 * Note: You need to generate an App Password from Yahoo Account Security
 * 
 * SENDGRID:
 * Host: smtp.sendgrid.net
 * Port: 587
 * Username: apikey
 * Password: Your SendGrid API Key
 * 
 * MAILGUN:
 * Host: smtp.mailgun.org
 * Port: 587
 * Username: Your Mailgun SMTP username
 * Password: Your Mailgun SMTP password
 * 
 * AWS SES:
 * Host: email-smtp.us-east-1.amazonaws.com (or your region)
 * Port: 587
 * Username: Your AWS SES SMTP username
 * Password: Your AWS SES SMTP password
 */

// Email templates
define('EMAIL_HEADER_COLOR', '#667eea');
define('EMAIL_BUTTON_COLOR', '#ff6b6b');

?>