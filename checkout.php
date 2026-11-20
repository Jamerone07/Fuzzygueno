<?php
session_start();
require_once 'db.php';

// Load PHPMailer if available (for email functionality)
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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check for order success first (before checking cart, since cart is cleared after order)
$order_success = isset($_GET['success']) && isset($_SESSION['order_success']);
$order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : null;
$delivery_method_success = isset($_SESSION['delivery_method']) ? $_SESSION['delivery_method'] : 'pickup';

// Get cart items (only if not showing success message)
$cart_items = [];
$total = 0;
if (!$order_success) {
    $stmt = $conn->prepare("
        SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.id DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Check if cart is empty (only if not showing success message)
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }
    
    // Calculate total
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order - check which columns exist and build query accordingly
        // Based on DESCRIBE, we have: id, user_id, total_amount, total, created_at
        // Missing: order_status
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        // Get delivery method
        $delivery_method = $_POST['delivery_method'] ?? 'pickup';
        $delivery_address = '';
        
        // Get user's address if delivery
        if ($delivery_method === 'delivery') {
            $addr_stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
            $addr_stmt->bind_param("i", $user_id);
            $addr_stmt->execute();
            $addr_result = $addr_stmt->get_result();
            if ($addr_row = $addr_result->fetch_assoc()) {
                $delivery_address = $addr_row['address'] ?? '';
            }
            $addr_stmt->close();
        }
        
        // Check if delivery columns exist
        $check_delivery = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_method'");
        $has_delivery = ($check_delivery && $check_delivery->num_rows > 0);
        
        // Re-prepare with delivery info if available
        if ($has_delivery) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_method, delivery_address) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("idss", $user_id, $total, $delivery_method, $delivery_address);
        } else {
            $stmt->bind_param("id", $user_id, $total);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $order_id = $conn->insert_id;
        $stmt->close();
        
        // Fix duplicate: Group items by product_id to avoid duplicates
        $unique_items = [];
        foreach ($cart_items as $item) {
            $key = $item['product_id'];
            if (!isset($unique_items[$key])) {
                $unique_items[$key] = $item;
            } else {
                // Combine quantities if same product
                $unique_items[$key]['quantity'] += $item['quantity'];
            }
        }
        
        // Create order items - only unique products
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        foreach ($unique_items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
        $stmt->close();
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Send order email
        $email_sent = false;
        try {
            // Get user info
            $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $user = $user_result->fetch_assoc();
            $stmt->close();
            
            // Get order items for email
            $stmt = $conn->prepare("
                SELECT oi.quantity, oi.price, p.name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order_items_result = $stmt->get_result();
            $order_items_email = $order_items_result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Send email using PHPMailer
            if ($phpmailer_available) {
                // Load .env if exists
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
                
                // Get email credentials - with fallback to hardcoded values if .env not found
                $mailHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
                $mailUsername = getenv('MAIL_USERNAME') ?: 'madijamerone@gmail.com';
                $mailPassword = getenv('MAIL_PASSWORD') ?: 'vwinwvucqxfxzmwd';
                
                if (!empty($mailUsername) && !empty($mailPassword)) {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = $mailHost;
                    $mail->SMTPAuth = true;
                    $mail->Username = $mailUsername;
                    $mail->Password = $mailPassword;
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    
                    $mail->setFrom($mailUsername, 'Fuzzygueño E-commerce');
                    $mail->addAddress('madijamerone@gmail.com', 'Fuzzygueño Orders');
                    
                    $mail->isHTML(true);
                    $mail->Subject = "New Order #{$order_id} - Fuzzygueño";
                    
                    // Build email body
                    $items_html = '';
                    foreach ($order_items_email as $item) {
                        $items_html .= "<tr>
                            <td>{$item['name']}</td>
                            <td>{$item['quantity']}</td>
                            <td>₱" . number_format($item['price'], 2) . "</td>
                            <td>₱" . number_format($item['price'] * $item['quantity'], 2) . "</td>
                        </tr>";
                    }
                    
                    $emailBody = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #87CEEB; padding: 20px; border-radius: 10px; color: white; text-align: center; }
                            .content { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-top: 20px; }
                            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                            th { background: #87CEEB; color: white; }
                            .total { font-size: 18px; font-weight: bold; color: #87CEEB; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>🌸 New Order Received 🌸</h2>
                            </div>
                            <div class='content'>
                                <p><strong>Order #{$order_id}</strong></p>
                                <p><strong>Customer:</strong> {$user['username']} ({$user['email']})</p>
                                <p><strong>Order Date:</strong> " . date('F j, Y g:i A') . "</p>
                                <h3>Order Items:</h3>
                                <table>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                    {$items_html}
                                </table>
                                <p class='total'>Total Amount: ₱" . number_format($total, 2) . "</p>";
                    
                    // Add delivery info
                    $check_delivery_order = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_method'");
                    $has_delivery_col = ($check_delivery_order && $check_delivery_order->num_rows > 0);
                    if ($has_delivery_col) {
                        $delivery_stmt = $conn->prepare("SELECT delivery_method, delivery_address FROM orders WHERE id = ?");
                        $delivery_stmt->bind_param("i", $order_id);
                        $delivery_stmt->execute();
                        $delivery_result = $delivery_stmt->get_result();
                        if ($delivery_row = $delivery_result->fetch_assoc()) {
                            $emailBody .= "<p><strong>Delivery Method:</strong> " . ucfirst($delivery_row['delivery_method']) . "</p>";
                            if ($delivery_row['delivery_method'] === 'delivery' && !empty($delivery_row['delivery_address'])) {
                                $emailBody .= "<p><strong>Delivery Address:</strong> " . htmlspecialchars($delivery_row['delivery_address']) . "</p>";
                            } elseif ($delivery_row['delivery_method'] === 'pickup') {
                                $emailBody .= "<p><strong>Pickup Location:</strong> Pamantasan ng Lungsod ng Pasig</p>";
                            }
                        }
                        $delivery_stmt->close();
                    }
                    
                    $emailBody .= "
                                <p>Order Status: <strong>Pending</strong></p>
                            </div>
                        </div>
                    </body>
                    </html>";
                    
                    $mail->Body = $emailBody;
                    $mail->AltBody = "New Order #{$order_id}\n\nCustomer: {$user['username']} ({$user['email']})\n\nOrder Items:\n" . 
                        implode("\n", array_map(function($item) {
                            return "- {$item['name']} x{$item['quantity']} = ₱" . number_format($item['price'] * $item['quantity'], 2);
                        }, $order_items_email)) . "\n\nTotal: ₱" . number_format($total, 2);
                    
                    // Enable error reporting for email debugging (remove in production)
                    $mail->SMTPDebug = 0; // Set to 2 for detailed debugging
                    $mail->send();
                    $email_sent = true;
                    error_log('✅ Order email sent successfully to madijamerone@gmail.com for order #' . $order_id);
                } else {
                    error_log('Email not sent: Missing MAIL_USERNAME or MAIL_PASSWORD in .env file');
                    // Try fallback: simple PHP mail
                    $fallback_subject = "New Order #{$order_id} - Fuzzygueño";
                    $fallback_body = "New Order #{$order_id}\n\n";
                    $fallback_body .= "Customer: {$user['username']} ({$user['email']})\n";
                    $fallback_body .= "Order Date: " . date('F j, Y g:i A') . "\n\n";
                    $fallback_body .= "Order Items:\n";
                    foreach ($order_items_email as $item) {
                        $fallback_body .= "- {$item['name']} x{$item['quantity']} = ₱" . number_format($item['price'] * $item['quantity'], 2) . "\n";
                    }
                    $fallback_body .= "\nTotal: ₱" . number_format($total, 2) . "\n";
                    $fallback_body .= "Delivery Method: " . ucfirst($delivery_method) . "\n";
                    if ($delivery_method === 'delivery' && !empty($delivery_address)) {
                        $fallback_body .= "Delivery Address: {$delivery_address}\n";
                    } else {
                        $fallback_body .= "Pickup Location: Pamantasan ng Lungsod ng Pasig\n";
                    }
                    @mail('madijamerone@gmail.com', $fallback_subject, $fallback_body, 'From: Fuzzygueño E-commerce <noreply@fuzzygueno.com>');
                }
            } else {
                error_log('Email not sent: PHPMailer not available (vendor/autoload.php not found)');
                // Try fallback: simple PHP mail
                $fallback_subject = "New Order #{$order_id} - Fuzzygueño";
                $fallback_body = "New Order #{$order_id}\n\n";
                $fallback_body .= "Customer: {$user['username']} ({$user['email']})\n";
                $fallback_body .= "Order Date: " . date('F j, Y g:i A') . "\n\n";
                $fallback_body .= "Order Items:\n";
                foreach ($order_items_email as $item) {
                    $fallback_body .= "- {$item['name']} x{$item['quantity']} = ₱" . number_format($item['price'] * $item['quantity'], 2) . "\n";
                }
                $fallback_body .= "\nTotal: ₱" . number_format($total, 2) . "\n";
                $fallback_body .= "Delivery Method: " . ucfirst($delivery_method) . "\n";
                if ($delivery_method === 'delivery' && !empty($delivery_address)) {
                    $fallback_body .= "Delivery Address: {$delivery_address}\n";
                } else {
                    $fallback_body .= "Pickup Location: Pamantasan ng Lungsod ng Pasig\n";
                }
                @mail('madijamerone@gmail.com', $fallback_subject, $fallback_body, 'From: Fuzzygueño E-commerce <noreply@fuzzygueno.com>');
            }
        } catch (Exception $email_error) {
            // Email failed but order was successful, so continue
            error_log('Email error: ' . $email_error->getMessage());
            if (isset($mail)) {
                error_log('PHPMailer Error Info: ' . $mail->ErrorInfo);
            }
            // Try fallback email
            try {
                $fallback_subject = "New Order #{$order_id} - Fuzzygueño";
                $fallback_body = "New Order #{$order_id}\n\n";
                $fallback_body .= "Customer: {$user['username']} ({$user['email']})\n";
                $fallback_body .= "Order Date: " . date('F j, Y g:i A') . "\n\n";
                $fallback_body .= "Order Items:\n";
                foreach ($order_items_email as $item) {
                    $fallback_body .= "- {$item['name']} x{$item['quantity']} = ₱" . number_format($item['price'] * $item['quantity'], 2) . "\n";
                }
                $fallback_body .= "\nTotal: ₱" . number_format($total, 2) . "\n";
                $fallback_body .= "Delivery Method: " . ucfirst($delivery_method) . "\n";
                if ($delivery_method === 'delivery' && !empty($delivery_address)) {
                    $fallback_body .= "Delivery Address: {$delivery_address}\n";
                } else {
                    $fallback_body .= "Pickup Location: Pamantasan ng Lungsod ng Pasig\n";
                }
                @mail('madijamerone@gmail.com', $fallback_subject, $fallback_body, 'From: Fuzzygueño E-commerce <noreply@fuzzygueno.com>');
                error_log('Fallback email sent to madijamerone@gmail.com');
            } catch (Exception $e) {
                error_log('Fallback email also failed: ' . $e->getMessage());
            }
        }
        
        // Store delivery method in session for success message
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $order_id;
        $_SESSION['delivery_method'] = $delivery_method;
        header('Location: checkout.php?success=1');
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error = 'Order placement failed: ' . $e->getMessage();
        error_log('Order error: ' . $e->getMessage());
    } catch (mysqli_sql_exception $e) {
        // Rollback on error
        $conn->rollback();
        $error = 'Order placement failed: ' . $e->getMessage();
        error_log('Order SQL error: ' . $e->getMessage());
    }
}

// Clean up session variables after displaying success message
if ($order_success) {
    unset($_SESSION['order_success']);
    unset($_SESSION['order_id']);
    unset($_SESSION['delivery_method']);
}
?>
<?php
$page_title = 'Checkout - Fuzzygueño';
include 'head.php';
?>
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .checkout-title {
            text-align: center;
            color: var(--text-dark);
            margin-bottom: 30px;
            font-size: 32px;
        }
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        .checkout-items {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(255, 182, 217, 0.2);
            padding: 30px;
        }
        .checkout-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .checkout-item:last-child {
            border-bottom: none;
        }
        .checkout-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .checkout-item-info {
            flex: 1;
        }
        .checkout-item-info h3 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        .checkout-item-info p {
            color: var(--text-light);
            margin: 5px 0;
        }
        .checkout-summary {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(255, 182, 217, 0.2);
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        .checkout-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .checkout-summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-blue);
        }
        .place-order-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .place-order-btn:hover {
            transform: translateY(-2px);
        }
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #2e7d32;
            text-align: center;
        }
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #c62828;
        }
        @media (max-width: 968px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            .checkout-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="checkout-container">
        <h2 class="checkout-title">💳 Checkout</h2>
        
        <?php if ($order_success): 
            // Get user address for display
            $user_addr_stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
            $user_addr_stmt->bind_param("i", $user_id);
            $user_addr_stmt->execute();
            $user_addr_result = $user_addr_stmt->get_result();
            $user_address = '';
            if ($user_addr_row = $user_addr_result->fetch_assoc()) {
                $user_address = $user_addr_row['address'] ?? '';
            }
            $user_addr_stmt->close();
        ?>
            <div class="success-message">
                <h3>🎉 Order Received Successfully!</h3>
                <p style="font-size: 18px; margin: 20px 0;"><strong>Your order has been received and we're working on it! 💖</strong></p>
                <p>Our team is now making your order with love and attention to detail. We'll notify you once it's ready!</p>
                
                <?php if ($delivery_method_success === 'pickup'): ?>
                    <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196F3;">
                        <p style="margin: 0 0 10px 0;"><strong>📍 Pickup Information:</strong></p>
                        <p style="margin: 5px 0;"><strong>Location:</strong> Pamantasan ng Lungsod ng Pasig</p>
                        <p style="margin: 10px 0 0 0;">You can pick up your order at the school once it's ready. We will contact you when your order is complete!</p>
                    </div>
                <?php else: ?>
                    <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #2196F3;">
                        <p style="margin: 0 0 10px 0;"><strong>🚚 Delivery Information:</strong></p>
                        <p style="margin: 5px 0;"><strong>Delivery Address:</strong> <?php echo htmlspecialchars($user_address); ?></p>
                        <p style="margin: 10px 0 0 0;">Your order will be delivered to your address. We will contact you to confirm delivery details and schedule!</p>
                    </div>
                <?php endif; ?>
                
                <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107;">
                    <p style="margin: 0 0 10px 0;"><strong>📧 Have Questions or Concerns?</strong></p>
                    <p style="margin: 5px 0;">Please email us at <a href="mailto:madijamerone@gmail.com" style="color: #1976d2; font-weight: 600; text-decoration: none;">madijamerone@gmail.com</a> for any questions about your order.</p>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #856404;">We're here to help! Feel free to reach out if you need to make any changes or have special requests.</p>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.php" class="btn btn-primary" style="display: inline-block; padding: 15px 40px; text-decoration: none;">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="checkout-content">
                <div class="checkout-items">
                    <h3 style="margin-bottom: 20px; color: var(--text-dark);">Order Summary</h3>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="checkout-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="checkout-item-image">
                            <div class="checkout-item-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                                <p>Price: ₱<?php echo number_format($item['price'], 2); ?> each</p>
                            </div>
                            <div style="font-weight: 600; color: var(--accent-blue);">
                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="checkout-summary">
                    <h3 style="margin-bottom: 20px; color: var(--text-dark);">Total</h3>
                    <div class="checkout-summary-row">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="checkout-summary-total">
                        <span>Total:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <?php
                    // Get user address
                    $user_addr_check = $conn->prepare("SELECT address FROM users WHERE id = ?");
                    $user_addr_check->bind_param("i", $user_id);
                    $user_addr_check->execute();
                    $user_addr_check_result = $user_addr_check->get_result();
                    $user_address_display = '';
                    if ($user_addr_check_row = $user_addr_check_result->fetch_assoc()) {
                        $user_address_display = $user_addr_check_row['address'] ?? '';
                    }
                    $user_addr_check->close();
                    ?>
                    
                    <form method="POST">
                        <div style="margin: 20px 0; padding: 20px; background: #f0f8ff; border-radius: 10px;">
                            <h4 style="margin-bottom: 15px; color: var(--text-dark);">Delivery Option</h4>
                            <div style="margin-bottom: 10px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="delivery_method" value="pickup" checked style="margin-right: 10px; width: 18px; height: 18px;">
                                    <span><strong>Pickup at School</strong> - Pamantasan ng Lungsod ng Pasig</span>
                                </label>
                            </div>
                            <div style="margin-bottom: 10px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="delivery_method" value="delivery" style="margin-right: 10px; width: 18px; height: 18px;">
                                    <span><strong>Delivery to Address</strong></span>
                                </label>
                            </div>
                            <?php if (!empty($user_address_display)): ?>
                                <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 5px; font-size: 14px;">
                                    <strong>Your Address:</strong><br>
                                    <?php echo htmlspecialchars($user_address_display); ?>
                                </div>
                            <?php else: ?>
                                <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; font-size: 14px; color: #856404;">
                                    ⚠️ No address on file. Please update your profile with your address for delivery.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" name="place_order" class="place-order-btn">
                            Place Order
                        </button>
                    </form>
                    
                    <a href="cart.php" style="display: block; text-align: center; margin-top: 15px; color: var(--text-light); text-decoration: none;">
                        ← Back to Cart
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

