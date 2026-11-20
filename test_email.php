<?php
/**
 * Test Email Configuration
 * Run this file to test if email is working
 * Access: http://localhost/TECNO/test_email.php
 */

// Load PHPMailer if available
$phpmailer_available = false;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $phpmailer_available = class_exists('PHPMailer\PHPMailer\PHPMailer');
} elseif (file_exists(__DIR__ . '/PHPMailer-master/src/PHPMailer.php')) {
    // Fallback: Use PHPMailer-master folder directly
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
    $phpmailer_available = class_exists('PHPMailer\PHPMailer\PHPMailer');
}

// Load .env
function loadEnv($path) {
    if (!file_exists($path)) {
        error_log(".env file not found at: " . $path);
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("Failed to read .env file");
        return false;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $name = trim($parts[0]);
        $value = trim($parts[1]);
        // Remove quotes if present
        $value = trim($value, '"\'');
        if (!empty($name)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}
loadEnv(__DIR__ . '/.env');

$mailHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$mailUsername = getenv('MAIL_USERNAME') ?: '';
$mailPassword = getenv('MAIL_PASSWORD') ?: '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Test - Fuzzygueño</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>📧 Email Configuration Test</h1>
    
    <div class="info">
        <h3>Configuration Status:</h3>
        <p><strong>PHPMailer Available:</strong> <?php echo $phpmailer_available ? '✅ Yes' : '❌ No (vendor/autoload.php not found)'; ?></p>
        
        <?php
        $env_path = __DIR__ . '/.env';
        $env_exists = file_exists($env_path);
        ?>
        <p><strong>.env File Path:</strong> <code><?php echo htmlspecialchars($env_path); ?></code></p>
        <p><strong>.env File:</strong> <?php echo $env_exists ? '✅ Found' : '❌ Not Found'; ?></p>
        
        <?php if (!$env_exists): ?>
            <div class="error" style="margin-top: 10px;">
                <strong>⚠️ .env file not found!</strong><br>
                Please create a file named <code>.env</code> (with the dot) in: <code><?php echo htmlspecialchars(__DIR__); ?></code><br>
                <strong>Content should be:</strong>
                <pre style="margin-top: 10px;">MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=madijamerone@gmail.com
MAIL_PASSWORD=vwinwvucqxfxzmwd</pre>
            </div>
        <?php else: ?>
            <div class="success" style="margin-top: 10px;">
                ✅ .env file found! Reading contents...
            </div>
        <?php endif; ?>
        
        <p><strong>MAIL_HOST:</strong> <?php echo $mailHost; ?></p>
        <p><strong>MAIL_USERNAME:</strong> <?php echo !empty($mailUsername) ? '✅ Set (' . htmlspecialchars($mailUsername) . ')' : '❌ Not Set'; ?></p>
        <p><strong>MAIL_PASSWORD:</strong> <?php echo !empty($mailPassword) ? '✅ Set (' . str_repeat('*', strlen($mailPassword)) . ')' : '❌ Not Set'; ?></p>
        
        <?php
        // Debug: Show what we're reading from .env
        if ($env_exists) {
            echo '<div class="info" style="margin-top: 15px;">';
            echo '<strong>Debug - .env file contents:</strong><pre>';
            $env_content = file_get_contents($env_path);
            echo htmlspecialchars($env_content);
            echo '</pre></div>';
            
            // Show parsed values
            echo '<div class="info" style="margin-top: 10px;">';
            echo '<strong>Parsed Environment Variables:</strong><pre>';
            echo 'MAIL_HOST: ' . (getenv('MAIL_HOST') ?: 'not set') . "\n";
            echo 'MAIL_USERNAME: ' . (getenv('MAIL_USERNAME') ?: 'not set') . "\n";
            echo 'MAIL_PASSWORD: ' . (getenv('MAIL_PASSWORD') ? str_repeat('*', strlen(getenv('MAIL_PASSWORD'))) : 'not set') . "\n";
            echo '</pre></div>';
        } else {
            echo '<div class="error" style="margin-top: 15px;">';
            echo '<strong>⚠️ Quick Fix:</strong><br>';
            echo 'Visit <a href="create_env.php" style="color: #721c24; font-weight: bold;">create_env.php</a> to automatically create the .env file!';
            echo '</div>';
        }
        ?>
    </div>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_email'])) {
        if (!$phpmailer_available) {
            echo '<div class="error"><strong>Error:</strong> PHPMailer is not available. Please run: composer install</div>';
        } elseif (empty($mailUsername) || empty($mailPassword)) {
            echo '<div class="error"><strong>Error:</strong> Email credentials not configured. Please check your .env file.</div>';
        } else {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $mailHost;
                $mail->SMTPAuth = true;
                $mail->Username = $mailUsername;
                $mail->Password = $mailPassword;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                
                $mail->setFrom($mailUsername, 'Fuzzygueño Test');
                $mail->addAddress('madijamerone@gmail.com', 'Test Recipient');
                
                $mail->isHTML(true);
                $mail->Subject = 'Test Email from Fuzzygueño';
                $mail->Body = '<h2>Test Email</h2><p>This is a test email from Fuzzygueño e-commerce system.</p><p>If you received this, email is working correctly!</p>';
                $mail->AltBody = 'Test Email from Fuzzygueño. If you received this, email is working correctly!';
                
                $mail->send();
                echo '<div class="success"><strong>Success!</strong> Test email sent to madijamerone@gmail.com. Please check your inbox.</div>';
            } catch (Exception $e) {
                echo '<div class="error"><strong>Error sending email:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<div class="error"><strong>PHPMailer Error Info:</strong> ' . htmlspecialchars($mail->ErrorInfo) . '</div>';
            }
        }
    }
    ?>
    
    <form method="POST">
        <button type="submit" name="test_email" style="padding: 15px 30px; background: #87CEEB; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
            Send Test Email to madijamerone@gmail.com
        </button>
    </form>
    
    <div class="info" style="margin-top: 30px;">
        <h3>Setup Instructions:</h3>
        <ol>
            <li>Make sure PHPMailer is installed: <code>composer install</code></li>
            <li>Create a <code>.env</code> file in the project root with:
                <pre>MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_app_password</pre>
            </li>
            <li>Get Gmail App Password from Google Account settings</li>
            <li>Click the button above to test</li>
        </ol>
    </div>
</body>
</html>

