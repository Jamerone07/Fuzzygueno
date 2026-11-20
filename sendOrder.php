<?php
/**
 * Fuzzygueño Order Submission Handler
 * Sends order requests via email using PHPMailer
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// Load PHPMailer if available (with fallback)
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

if (!$phpmailer_available) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email system is not available. Please contact us directly.'
    ]);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
$productType = isset($_POST['product-type']) ? trim($_POST['product-type']) : '';
$colorsDesign = isset($_POST['colors-design']) ? trim($_POST['colors-design']) : '';
$quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate required fields
if (empty($name) || empty($contact) || empty($productType) || empty($colorsDesign) || empty($quantity)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all required fields.'
    ]);
    exit;
}

// Recipient emails
$recipientEmails = [
    'madijamerone@gmail.com', // Primary recipient
    'madi_jamerone@plpasig.edu.ph',
    'alay_darryljohn@plpasig.edu.ph',
    'modancia_johnchester@plpasig.edu.ph',
    'cajurao_alexanderjr@plpasig.edu.ph'
];

// Get email credentials from environment variables (with fallback)
$mailHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$mailUsername = getenv('MAIL_USERNAME') ?: 'madijamerone@gmail.com';
$mailPassword = getenv('MAIL_PASSWORD') ?: 'vwinwvucqxfxzmwd';

// Validate email credentials
if (empty($mailUsername) || empty($mailPassword)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email configuration is missing. Please check your .env file.'
    ]);
    exit;
}

try {
    // Create PHPMailer instance
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $mailHost;
    $mail->SMTPAuth = true;
    $mail->Username = $mailUsername;
    $mail->Password = $mailPassword;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 0; // Set to 2 for debugging
    
    // Sender
    $mail->setFrom($mailUsername, 'Fuzzygueño Website');
    $mail->addReplyTo($mailUsername, 'Fuzzygueño');
    
    // Recipients
    foreach ($recipientEmails as $email) {
        $mail->addAddress($email);
    }
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Order Request from Fuzzygueño Website';
    
    // Build email body
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #87CEEB; padding: 20px; border-radius: 10px; color: white; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-top: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #5BA3D0; }
            .value { margin-top: 5px; padding: 10px; background: white; border-radius: 5px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🌸 New Order Request 🌸</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Customer Name:</div>
                    <div class='value'>{$name}</div>
                </div>
                <div class='field'>
                    <div class='label'>Contact Information:</div>
                    <div class='value'>{$contact}</div>
                </div>
                <div class='field'>
                    <div class='label'>Product Type:</div>
                    <div class='value'>{$productType}</div>
                </div>
                <div class='field'>
                    <div class='label'>Colors/Design:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($colorsDesign)) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Quantity:</div>
                    <div class='value'>{$quantity}</div>
                </div>";
    
    if (!empty($message)) {
        $emailBody .= "
                <div class='field'>
                    <div class='label'>Additional Message:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>";
    }
    
    $emailBody .= "
            </div>
            <div class='footer'>
                <p>This order was submitted from the Fuzzygueño website.</p>
                <p>Please contact the customer to confirm the order details.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $mail->Body = $emailBody;
    
    // Plain text version
    $mail->AltBody = "
New Order Request from Fuzzygueño Website

Customer Name: {$name}
Contact: {$contact}
Product Type: {$productType}
Colors/Design: {$colorsDesign}
Quantity: {$quantity}" . (!empty($message) ? "\nAdditional Message: {$message}" : "") . "

---
This order was submitted from the Fuzzygueño website.
Please contact the customer to confirm the order details.
";
    
    // Send email
    $mail->send();
    
    // Success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Your order has been submitted successfully! We will contact you soon.'
    ]);
    
} catch (\PHPMailer\PHPMailer\Exception $e) {
    // Error response
    $error_message = 'Failed to send order. Please try again later or contact us directly.';
    
    // Log error for debugging
    error_log('PHPMailer Error: ' . $e->getMessage());
    if (isset($mail)) {
        error_log('PHPMailer Error Info: ' . $mail->ErrorInfo);
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => $error_message
    ]);
} catch (Exception $e) {
    // Generic error response
    error_log('SendOrder Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again later or contact us directly.'
    ]);
}

