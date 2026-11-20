# Local Server Setup for Fuzzygueño Website

## The Problem
You're seeing CORS errors because the website needs to run on a web server (http://) instead of opening the file directly (file://).

## Solution Options

### Option 1: Install XAMPP (Recommended - Easiest)

1. **Download XAMPP**: https://www.apachefriends.org/download.html
2. **Install XAMPP** (includes PHP, Apache, MySQL)
3. **Copy your project** to `C:\xampp\htdocs\fuzzygueno\`
4. **Start Apache** from XAMPP Control Panel
5. **Access your site**: http://localhost/fuzzygueno/

### Option 2: Install PHP Standalone

1. **Download PHP**: https://windows.php.net/download/
2. **Extract** to `C:\php\`
3. **Add to PATH**: Add `C:\php\` to your system PATH
4. **Run server**:
   ```bash
   cd C:\TECNO
   php -S localhost:8000
   ```
5. **Access**: http://localhost:8000

### Option 3: Use Live Server Extension (VS Code)

1. **Install "Live Server"** extension in VS Code
2. **Right-click** on `index.html`
3. **Select "Open with Live Server"**
4. **Note**: This works for HTML/CSS/JS but PHP won't work without a PHP server

### Option 4: Use WAMP (Windows Alternative)

1. **Download WAMP**: https://www.wampserver.com/
2. **Install WAMP**
3. **Copy project** to `C:\wamp64\www\fuzzygueno\`
4. **Start WAMP** services
5. **Access**: http://localhost/fuzzygueno/

## Quick Test

Once you have a server running, test the form:
1. Open http://localhost:8000 (or your server URL)
2. Fill out the order form
3. Submit and check for errors

## Important Notes

- **PHP is required** for the email functionality to work
- **Composer must be installed** to use PHPMailer
- **.env file must be configured** with your Gmail credentials
- Always access via `http://localhost` NOT `file:///`

## Troubleshooting

**"Failed to fetch" error:**
- Make sure you're accessing via http:// not file://
- Make sure your server is running
- Check browser console for errors

**"PHP not found" error:**
- Install PHP or use XAMPP/WAMP
- Make sure PHP is in your system PATH

**Email not sending:**
- Check .env file exists and has correct values
- Verify Gmail App Password is correct
- Check PHP error logs

