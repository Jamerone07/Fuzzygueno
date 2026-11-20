# Fuzzygueño Website Setup Guide

## PHP Email Setup Instructions

### Step 1: Install Composer (if not already installed)

1. Download Composer from https://getcomposer.org/download/
2. Follow the installation instructions for your operating system
3. Verify installation by running: `composer --version`

### Step 2: Install PHPMailer

Open your terminal/command prompt in the project directory and run:

```bash
composer install
```

This will install PHPMailer and its dependencies into the `vendor/` folder.

### Step 3: Configure Email Settings

1. Create a `.env` file in the project root (copy from `.env.example`):
   ```bash
   cp .env.example .env
   ```

2. Edit the `.env` file and add your Gmail credentials:
   ```
   MAIL_HOST=smtp.gmail.com
   MAIL_USERNAME=alexandercajurao552@gmail.com
   MAIL_PASSWORD=your_app_password_here
   ```

### Step 4: Get Gmail App Password

1. Go to your Google Account: https://myaccount.google.com/
2. Enable **2-Step Verification** (if not already enabled)
3. Go to **Security** → **2-Step Verification** → **App passwords**
4. Select **Mail** as the app and **Other** as the device
5. Name it "Fuzzygueño Website" and click **Generate**
6. Copy the 16-character password (no spaces)
7. Paste it into your `.env` file as `MAIL_PASSWORD`

### Step 5: Test the Setup

1. Make sure your web server (Apache/Nginx) is running
2. Open the website in your browser
3. Fill out the order form and submit
4. Check the recipient email addresses for the order notification

## Server Requirements

- PHP 7.4 or higher
- Composer
- Web server (Apache/Nginx) with PHP support
- Gmail account with App Password enabled

## Troubleshooting

### Email not sending?

1. **Check .env file**: Make sure it exists and has correct values
2. **Check App Password**: Verify the password is correct (16 characters, no spaces)
3. **Check PHP error logs**: Look for error messages
4. **Test SMTP connection**: You can test with a simple PHP script
5. **Check firewall**: Make sure port 587 (SMTP) is not blocked

### Common Errors

- **"Email configuration is missing"**: Check your `.env` file exists and has values
- **"SMTP connect() failed"**: Check your internet connection and Gmail credentials
- **"Authentication failed"**: Your App Password might be incorrect

## File Structure

```
Fuzzygueño/
├── index.html          # Main website
├── styles.css          # Styles
├── script.js           # JavaScript
├── sendOrder.php       # PHP email handler
├── composer.json       # Composer dependencies
├── .env                # Email configuration (create this)
├── .env.example        # Example configuration
├── vendor/             # Composer packages (after install)
└── images/             # Product images
```

## Security Notes

- **Never commit `.env` file** to version control (it's in `.gitignore`)
- Keep your App Password secure
- Use HTTPS in production
- Consider adding rate limiting to prevent spam

## Support

If you encounter issues, check:
1. PHP error logs
2. Browser console for JavaScript errors
3. Network tab for failed requests

