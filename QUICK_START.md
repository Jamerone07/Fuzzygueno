# 🚀 Quick Start - Fix "Failed to fetch" Error

## The Problem
You're seeing this error because you opened `index.html` directly in the browser (file:// protocol). The website needs to run on a web server.

## ✅ Quick Solution (Choose One)

### Option 1: XAMPP (Easiest - Recommended)

1. **Download XAMPP**: https://www.apachefriends.org/download.html
2. **Install** XAMPP
3. **Copy your project folder** (`TECNO`) to:
   ```
   C:\xampp\htdocs\fuzzygueno\
   ```
4. **Open XAMPP Control Panel** and click **Start** next to **Apache**
5. **Open your browser** and go to:
   ```
   http://localhost/fuzzygueno/
   ```

### Option 2: PHP Built-in Server

1. **Download PHP**: https://windows.php.net/download/
   - Choose "Thread Safe" version
   - Extract to `C:\php\`
2. **Add PHP to PATH**:
   - Search "Environment Variables" in Windows
   - Edit "Path" variable
   - Add `C:\php\`
3. **Open Command Prompt** in your project folder:
   ```bash
   cd C:\TECNO
   php -S localhost:8000
   ```
4. **Open browser**: http://localhost:8000

### Option 3: Test Your Setup

1. Open `test-server.html` in your browser
2. It will tell you if everything is set up correctly

## ⚠️ Important

- **Always use** `http://localhost` NOT `file:///C:/TECNO/index.html`
- **PHP must be installed** for the email form to work
- **Composer must be installed** for PHPMailer (see SETUP.md)

## 📧 After Server is Running

1. Install Composer: https://getcomposer.org/download/
2. Run: `composer install`
3. Create `.env` file (copy from `.env.example`)
4. Add your Gmail App Password to `.env`
5. Test the order form!

## Need Help?

- See `SERVER_SETUP.md` for detailed instructions
- See `SETUP.md` for email configuration
- Open `test-server.html` to diagnose issues

