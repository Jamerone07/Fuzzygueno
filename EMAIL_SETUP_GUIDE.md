# Email Setup Guide - Quick Steps

## Step 1: Create .env File

1. In your `TECNO` folder, create a new file named `.env` (with the dot at the beginning)
2. Copy and paste this EXACT content:

```
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=madijamerone@gmail.com
MAIL_PASSWORD=vwinwvucqxfxzmwd
```

3. Save the file

**Note:** If you can't see the file after creating it, it might be hidden. In Windows Explorer:
- Go to View tab
- Check "Hidden items" to show hidden files

## Step 2: Install PHPMailer (if not already installed)

Open Command Prompt in your TECNO folder and run:

```bash
cd D:\TECNP\TECNO
composer install
```

This will create a `vendor` folder with PHPMailer.

**OR** if you don't have Composer, the code will use the PHPMailer-master folder you already have.

## Step 3: Test Email

1. Open: `http://localhost/TECNO/test_email.php`
2. Click "Send Test Email"
3. Check if you receive an email at madijamerone@gmail.com

## Step 4: Test Order Email

1. Place a test order on your website
2. Check madijamerone@gmail.com inbox
3. You should receive an email with order details

## Troubleshooting

**Email not sending?**
- Check PHP error logs in XAMPP
- Make sure .env file exists and has correct credentials
- Verify Gmail App Password is correct (16 characters, no spaces)
- Check if PHPMailer is installed (vendor folder exists OR PHPMailer-master folder exists)

**Still not working?**
- Check spam folder
- Verify Gmail account has "Less secure app access" enabled OR use App Password
- Check XAMPP error logs: `C:\xampp\apache\logs\error.log`

